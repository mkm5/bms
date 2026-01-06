<?php declare(strict_types=1);

namespace App\Controller\User\Security;

use App\Entity\User;
use App\Form\UserRegistrationFinalizeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController extends AbstractController
{
    #[Route('/register/finalize/{id}', name: 'app_user_register_finalize', methods: ['GET', 'POST'])]
    public function finalize(
        Request $request,
        User $user,
        UriSigner $signer,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): Response
    {
        if (!$signer->check($request->getUri())
        || $user->isActive()) {
            throw $this->createAccessDeniedException('This link is invalid or modified.');
        }

        $form = $this->createForm(UserRegistrationFinalizeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $form->getData()['password']));
            $user->setIsActive(true);
            $em->flush();

            return $this->redirectToRoute('app_user_login');
        }

        return $this->render('security/register_finalize.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }
}
