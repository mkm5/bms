<?php declare(strict_types=1);

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class KanbanBoardController extends AbstractController
{
    #[Route('/board', name: 'app_user_board')]
    public function index(): Response
    {
        return $this->render('user/kanban_board.html.twig');
    }
}
