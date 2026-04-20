<?php declare(strict_types=1);

namespace App\Controller\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

#[AsController()]
class LoginAction
{
    public function __construct(
        private Environment $templating,
        private AuthenticationUtils $authUtils,
        private UrlGeneratorInterface $urlGenerator,
        private TokenStorageInterface $userTokenStorage,
    ) {}

    #[Route(path: '/login', name: 'login', methods: ['GET', 'POST'])]
    public function __invoke(): Response
    {
        $token = $this->userTokenStorage->getToken();
        if ($token !== null && $token->getUser() instanceof User) {
            return new RedirectResponse($this->urlGenerator->generate('tasklist_list'));
        }

        return new Response($this->templating->render('security/login.html.twig', [
            'error' => $this->authUtils->getLastAuthenticationError(),
            'last_username' => $this->authUtils->getLastUsername(),
        ]));
    }
}