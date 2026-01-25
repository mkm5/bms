<?php declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\Company;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CompanyController extends AbstractController
{
    #[Route('/companies', name: 'app_user_companies')]
    public function index(): Response
    {
        return $this->render('listings/listing.html.twig', [
            'title' => 'Company',
            'headerTitle' => 'Company',
            'listing' => '_companies.html.twig',
            'entityClassName' => Company::class,
        ]);
    }

    #[Route('/companies/{id}', name: 'app_user_company_view')]
    public function view(Company $company): Response
    {
        return $this->render('user/company_view.html.twig', [
            'company' => $company,
        ]);
    }
}
