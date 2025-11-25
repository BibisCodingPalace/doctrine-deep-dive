<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[AsController()]
#[Route(name: 'tasklist_')]
class TaskListController
{
    private readonly array $dummyData;

    public function __construct(
        private Environment $templating,
    ) {
        $this->dummyData = [
            [
                'id' => 0,
                'owner' => ['email' => 'drumann@gmail.com'],
                'contributors' => [],
                'title' => 'Empty test list',
                'lastUpdatedOn' => new \DateTimeImmutable(),
                'createdOn' => new \DateTimeImmutable(),
                'items' => [],
                'archived' => false,
            ],
            [
                'id' => 0,
                'owner' => ['email' => 'drumann@gmail.com'],
                'contributors' => [],
                'title' => 'Archived test list',
                'lastUpdatedOn' => new \DateTimeImmutable(),
                'createdOn' => new \DateTimeImmutable(),
                'items' => [],
                'archived' => true,
            ],
            [
                'id' => 1,
                'owner' => ['email' => 'drumann@gmail.com'],
                'contributors' => [],
                'title' => 'Test list with 1 open item',
                'lastUpdatedOn' => new \DateTimeImmutable(),
                'createdOn' => new \DateTimeImmutable(),
                'items' => [
                    [
                        'id' => 1,
                        'summary' => 'Replace with actual data',
                        'done' => false,
                    ]
                ],
                'archived' => false,
            ],
            [
                'id' => 1,
                'owner' => ['email' => 'drumann@gmail.com'],
                'contributors' => [],
                'title' => 'Test list with open and closed item',
                'lastUpdatedOn' => new \DateTimeImmutable(),
                'createdOn' => new \DateTimeImmutable(),
                'items' => [
                    [
                        'id' => 2,
                        'summary' => 'Create dummy data',
                        'done' => true,
                    ],
                    [
                        'id' => 3,
                        'summary' => 'Replace with actual data',
                        'done' => false,
                    ]
                ],
                'archived' => false,
            ]
        ];
    }

    #[Route(path: "/", name: "list", methods: ['GET'])]
    public function index(Request $request): Response
    {
        switch ($request->query->get('filter')) {
            case 'own':
            case 'contributing':
            case 'active':
            case 'archived':
        }

        return new Response($this->templating->render(
            'tasks/index.html.twig',
            [
                'task_lists' => $this->dummyData,
            ]
        ));
    }

    #[Route(path: "/show/{id}", name: "show", methods: ['GET'])]
    public function show(Request $request, int $id): Response
    {
        return new Response($this->templating->render(
            'tasks/show.html.twig',
            [
                'task_list' => $this->dummyData[$id],
            ]
        ));
    }

    #[Route(path: "/recent/{id}", name: "new", methods: ['GET'])]
    public function show(Request $request, int $id): Response
    {
        return new Response($this->templating->render(
            'tasks/recent.html.twig',
            [
                'tasks' => [
                    [
                        'id' => 3,
                        'summary' => 'Replace with actual data',
                        'done' => false,
                    ],
                ],
            ]
        ));
    }

    #[Route(path: "/add/{id}", name: "add", methods: ['POST'])]
    public function add(Request $request, int $id): Response
    {
        // TODO
    }

    #[Route(path: "/update/{id}", name: "item_update", methods: ['POST'])]
    public function update(Request $request, int $id): Response
    {
        // TODO
    }

    #[Route(path: "/edit/{id}", name: "edit", methods: ['POST'])]
    public function edit(Request $request, int $id): Response
    {
        // TODO
    }

    #[Route(path: "/archive/{id}", name: "archive", methods: ['POST'])]
    public function archive(Request $request, int $id): Response
    {
        // TODO
    }

    #[Route(path: "/contributors/{id}", name: "contributors", methods: ['GET', 'POST'])]
    public function contributor(Request $request, int $id): Response
    {
        $taskList = new TaskList();
        $form = $this->createForm(ContributorType::class, null, ['list' => $taskList]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newContributor = $form->get('contributor')->getData();

            $taskList->addContributor($newContributor);

            $entityManager = $managerRegistry->getManagerForClass(Task::class);
            $entityManager->flush();
            $entityManager->clear();

            return $this->redirectToRoute('tasklist_show', ['id' => $taskList->getId()]);
        }

        return new Response($this->templating->render(
            'tasks/contributors.html.twig',
            [
                'task_list' => $this->dummyData[$id],
                'form' => $form->createView(),
            ]
        ));
    }
}