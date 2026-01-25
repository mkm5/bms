<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\User;
use App\Form\UserDetailsType;
use App\Form\UserPasswordChangeType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/user', name: 'app_user_')]
class UserController extends AbstractController
{
    #[Route('/settings', name: 'settings', methods: 'GET')]
    public function settings(#[CurrentUser] User $user): Response
    {
        $detailsForm = $this->createForm(UserDetailsType::class, $user, [
            'action' => $this->generateUrl('app_user_details'),
        ]);
        $passwordForm = $this->createForm(UserPasswordChangeType::class, null, [
            'action' => $this->generateUrl('app_user_password_change'),
        ]);

        return $this->render('user/settings.html.twig', [
            'detailsForm' => $detailsForm,
            'passwordForm' => $passwordForm,
        ]);
    }

    #[Route('/details', name: 'details', methods: 'POST')]
    public function updateDetails(
        Request $request,
        #[CurrentUser] User $user,
        EntityManagerInterface $em,
    ): Response {
        $form = $this->createForm(UserDetailsType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Your details have been updated.');
        }

        return $this->redirectToRoute('app_user_settings');
    }

    #[Route('/password-change', name: 'password_change', methods: 'POST')]
    public function changePassword(
        Request $request,
        #[CurrentUser] User $user,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
    ): Response {
        $form = $this->createForm(UserPasswordChangeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!$passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
                $this->addFlash('error', 'Current password is incorrect.');

                return $this->redirectToRoute('app_user_settings');
            }

            $userRepository->upgradePassword($user, $data['newPassword']);

            return $this->redirectToRoute('app_user_logout');
        }

        return $this->redirectToRoute('app_user_settings');
    }
}
