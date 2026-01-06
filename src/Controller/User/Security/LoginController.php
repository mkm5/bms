<?php declare(strict_types=1);

namespace App\Controller\User\Security;

use App\Form\UserMagicLinkRequestType;
use App\Repository\UserRepository;
use App\Service\User\MagicLinkNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_user_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
            'user_magic_link_request_form' => $this->createForm(UserMagicLinkRequestType::class, null, [
                'action' => $this->generateUrl('app_user_login_magic_request'),
            ]),
        ]);
    }

    #[Route('/login/magic', name: 'app_user_login_magic_request', methods: ['POST'])]
    public function requestMagicLink(
        Request $request,
        UserRepository $userRepository,
        MagicLinkNotifier $magicLinkNotifier,
    ): Response
    {
        $form = $this->createForm(UserMagicLinkRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'];

            if (($user = $userRepository->findOneActiveByEmail($email))) {
                $magicLinkNotifier->notify($user);
            }

            return $this->render(
                'security/magic_link_sent.html.twig',
                ['email' => $user->getEmail()],
                new Response(status: Response::HTTP_SEE_OTHER),
            );
        }

        dd($form->getErrors());

        return $this->redirectToRoute('app_user_login');
    }

    #[Route('/login/check-magic', name: 'app_user_login_magic_check')]
    public function checkMagic(): void
    {
        throw new \LogicException();
    }

    #[Route(path: '/logout', name: 'app_user_logout')]
    public function logout(): void
    {
        throw new \LogicException();
    }
}
