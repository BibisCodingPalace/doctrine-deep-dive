<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Form\ContributorType;
use App\Repository\TaskListRepository;
use App\Repository\TaskRepository;
use App\TaskList\TaskListService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController()]
#[Route(name: 'tasklist_')]
class TaskListController extends AbstractController
{
    public function __construct(
        private readonly TaskListRepository $taskListRepository,
        private readonly TaskRepository $taskRepository,
        private readonly TaskListService $taskListService,
    ) {
    }

    #[Route(path: '/', name: 'list', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            if (!$user instanceof User) {
                return $this->redirectToRoute('login');
            }

            try {
                $taskList = $this->taskListService->createTaskList($user, (string) $request->request->get('name', ''));

                return $this->redirectToRoute('tasklist_show', ['id' => $taskList->getId()]);
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('danger', $e->getMessage());
            }

            $query = [];
            $filter = $request->query->get('filter');
            if ($filter !== null && $filter !== '') {
                $query['filter'] = $filter;
            }

            return $this->redirectToRoute('tasklist_list', $query);
        }

        if (!$user instanceof User) {
            return $this->render('tasks/index.html.twig', [
                'task_lists' => [],
            ]);
        }

        $filter = $request->query->get('filter');
        $taskLists = match ($filter) {
            'own' => $this->taskListRepository->findListsOwnedBy($user),
            'contributing' => $this->taskListRepository->findListsContributedBy($user),
            'active' => $this->taskListRepository->findActive($user),
            'archived' => $this->taskListRepository->findArchived($user),
            default => $this->taskListRepository->findAll()
        };

        return $this->render('tasks/index.html.twig', [
            'task_lists' => $taskLists,
        ]);
    }

    #[Route(path: '/show/{id}', name: 'show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(TaskList $taskList): Response
    {
        return $this->render('tasks/show.html.twig', [
            'task_list' => $taskList,
        ]);
    }

    #[Route(path: '/recent/{id}', name: 'new', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function recent(TaskList $taskList): Response
    {
        $tasks = array_values(array_filter(
            $this->taskRepository->findTasksCreatedToday(),
            static fn (Task $task) => $task->getList()->getId() === $taskList->getId(),
        ));

        return $this->render('tasks/recent.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route(path: '/add/{id}', name: 'add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, TaskList $taskList): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        try {
            $this->taskListService->addTask($user, $taskList, (string) $request->request->get('summary', ''));
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('tasklist_show', ['id' => $taskList->getId()]);
    }

    #[Route(path: '/update/{id}', name: 'item_update', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function update(int $id): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $task = $this->taskRepository->find($id);
        if (!$task instanceof Task) {
            throw $this->createNotFoundException('Task not found.');
        }

        try {
            $this->taskListService->updateTask($user, $task);
        } catch (\RuntimeException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('tasklist_show', ['id' => $task->getList()->getId()]);
    }

    #[Route(path: '/archive/{id}', name: 'archive', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function archive(TaskList $taskList,EntityManagerInterface $entityManager): Response
    {
        $taskList->archive();

        $entityManager->flush();
        $entityManager->clear();

        return $this->redirectToRoute('tasklist_show', ['id' => $taskList->getId()]);
    }

    #[Route(path: '/contributors/{id}', name: 'contributors', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function contributor(Request $request, TaskList $taskList): Response
    {
        $form = $this->createForm(ContributorType::class, null, ['list' => $taskList]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw $this->createAccessDeniedException();
            }

            $contributor = $form->get('contributor')->getData();
            if (!$contributor instanceof User) {
                $this->addFlash('danger', 'Please select a user.');
            } else {
                try {
                    $this->taskListService->addContributor($user, $taskList, $contributor);
                } catch (\InvalidArgumentException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            return $this->redirectToRoute('tasklist_show', ['id' => $taskList->getId()]);
        }

        return $this->render('tasks/contributors.html.twig', [
            'task_list' => $taskList,
            'form' => $form->createView(),
        ]);
    }
}
