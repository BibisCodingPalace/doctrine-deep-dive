<?php declare(strict_types=1);

namespace App\Controller\Security;

use App\Form\RequestSignupFormType;
use App\Security\Signup\RequestSignup;
use App\Security\Signup\UserSignup;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

#[AsController()]
final readonly class SignupAction
{
    public function __construct(
        private Environment $templating,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private UserSignup $signup,
    ) {}

    #[Route(path: '/signup', name: 'signup', methods: ['GET', 'POST'])]
    public function __invoke(
        Request $request,
    ): Response {

        $signupRequest = new RequestSignup();
        $form = $this->formFactory->create(RequestSignupFormType::class, $signupRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->signup->signup($signupRequest);

            return new RedirectResponse($this->urlGenerator->generate('login'));
        }

        $response = new Response($this->templating->render('security/signup.html.twig', [
            'form' => $form->createView(),
        ]));
        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $response;
    }
}