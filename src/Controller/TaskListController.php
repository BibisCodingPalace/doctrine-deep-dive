<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Form\ContributorType;
use App\Repository\TaskListRepository;
use App\Repository\TaskRepository;
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
    ) {
    }

    #[Route(path: '/', name: 'list', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
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
            default => $this->mergeOwnedAndContributedLists($user),
        };

        return $this->render('tasks/index.html.twig', [
            'task_lists' => $taskLists,
        ]);
    }

    #[Route(path: '/show/{id}', name: 'show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(TaskList $taskList): Response
    {
        $this->assertUserMayViewTaskList($taskList);

        return $this->render('tasks/show.html.twig', [
            'task_list' => $taskList,
        ]);
    }

    #[Route(path: '/recent/{id}', name: 'new', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function recent(TaskList $taskList): Response
    {
        $this->assertUserMayViewTaskList($taskList);

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
    public function add(TaskList $taskList): Response
    {
        $this->assertUserMayViewTaskList($taskList);

        return $this->redirectToRoute('tasklist_show', ['id' => $taskList->getId()]);
    }

    #[Route(path: '/update/{id}', name: 'item_update', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function update(int $id): Response
    {
        $task = $this->taskRepository->find($id);
        if (!$task instanceof Task) {
            throw $this->createNotFoundException('Task not found.');
        }

        $this->assertUserMayViewTaskList($task->getList());

        return $this->redirectToRoute('tasklist_show', ['id' => $task->getList()->getId()]);
    }

    #[Route(path: '/archive/{id}', name: 'archive', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function archive(TaskList $taskList): Response
    {
        $this->assertUserMayViewTaskList($taskList);

        return $this->redirectToRoute('tasklist_show', ['id' => $taskList->getId()]);
    }

    #[Route(path: '/contributors/{id}', name: 'contributors', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function contributor(Request $request, TaskList $taskList): Response
    {
        $this->assertUserMayViewTaskList($taskList);

        $form = $this->createForm(ContributorType::class, null, ['list' => $taskList]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO

            return $this->redirectToRoute('tasklist_show', ['id' => $taskList->getId()]);
        }

        return $this->render('tasks/contributors.html.twig', [
            'task_list' => $taskList,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return TaskList[]
     */
    private function mergeOwnedAndContributedLists(User $user): array
    {
        $byId = [];
        foreach ($this->taskListRepository->findListsOwnedBy($user) as $list) {
            $byId[$list->getId()] = $list;
        }
        foreach ($this->taskListRepository->findListsContributedBy($user) as $list) {
            $byId[$list->getId()] = $list;
        }

        return array_values($byId);
    }

    private function userMayViewTaskList(User $user, TaskList $taskList): bool
    {
        if ($user->getId() === $taskList->getOwner()->getId()) {
            return true;
        }

        foreach ($taskList->getContributors() as $contributor) {
            if ($contributor->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    private function assertUserMayViewTaskList(TaskList $taskList): void
    {
        $user = $this->getUser();
        if (!$user instanceof User || !$this->userMayViewTaskList($user, $taskList)) {
            throw $this->createAccessDeniedException();
        }
    }
}
