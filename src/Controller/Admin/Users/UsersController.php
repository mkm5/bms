<?php declare(strict_types=1);

namespace App\Controller\Admin\Users;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

final class UsersController extends AbstractController
{
    #[Route('/admin/users', name: 'app_admin_users')]
    public function index(): Response
    {
        return $this->render('listings/listing.html.twig', [
            'title' => 'User',
            'headerTitle' => 'User',
            'listing' => 'admin/_users.html.twig',
            'entityClassName' => User::class,
        ]);
    }

    #[Route('/admin/users/{id}/delete', name: 'app_admin_user_delete', methods: 'POST')]
    #[IsCsrfTokenValid(new Expression('"delete-user-" ~ args["user"].getId()'))]
    public function delete(User $user, #[CurrentUser] User $currentUser, EntityManagerInterface $em): Response
    {
        if ($user->getId() === $currentUser->getId()) {
            throw $this->createAccessDeniedException('You cannot delete your own account.');
        }

        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('app_admin_users');
    }
}
