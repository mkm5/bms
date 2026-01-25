<?php declare(strict_types=1);

namespace App\Controller\Admin\Users;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;;
use Symfony\Component\Routing\Attribute\Route;

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
}
