<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Config\CommunicationType;
use App\Entity\Company;
use App\Entity\Contact;
use App\Entity\Project;
use App\Service\User\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserFactory $userFactory,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Users
        $admin = $this->userFactory->create('admin@localhost', 'admin', isAdmin: true, isActive: true);
        $manager->persist($admin);

        $user1 = $this->userFactory->create('user@localhost');
        $manager->persist($user1);

        // Managers
        $pmKatarzyna = $this->userFactory->create('katarzyna.nowak@cloudforge.dev', 'katarzyna123', 'Katarzyna', 'Nowak', isActive: true);
        $manager->persist($pmKatarzyna);
        $pmDavid = $this->userFactory->create('david.thompson@byteworks.io', 'david123', 'David', 'Thompson', isActive: true);
        $manager->persist($pmDavid);
        $pmAisha = $this->userFactory->create('aisha.patel@codestream.net', 'aisha123', 'Aisha', 'Patel', isActive: true);
        $manager->persist($pmAisha);
        $pmLars = $this->userFactory->create('lars.eriksson@devhorizon.se', 'lars123', 'Lars', 'Eriksson', isActive: true);
        $manager->persist($pmLars);

        // Regular users (non-managers)
        $manager->persist($this->userFactory->create('maya.johnson@stackpulse.io', 'maya123', 'Maya', 'Johnson', isActive: true));
        $manager->persist($this->userFactory->create('oliver.kim@codeharbor.dev', 'oliver123', 'Oliver', 'Kim', isActive: true));
        $manager->persist($this->userFactory->create('nina.petrov@bitshift.net', 'nina123', 'Nina', 'Petrov', isActive: true));
        $manager->persist($this->userFactory->create('jakub.wisniewski@appforge.pl', 'jakub123', 'Jakub', 'Wiśniewski', isActive: true));

        // Companies
        $companyAcme = Company::create('Acme Corporation');
        $manager->persist($companyAcme);

        $companyNexus = Company::create('Nexus Technologies');
        $manager->persist($companyNexus);

        $companyPinnacle = Company::create('Pinnacle Solutions');
        $manager->persist($companyPinnacle);

        $companyVortex = Company::create('Vortex Industries');
        $manager->persist($companyVortex);

        // Contacts with Communication Channels
        $contactJohn = Contact::create('John', 'Mitchell', $companyAcme)
            ->withEmail('j.mitchell@acme-corp.com')
            ->withWorkPhone('+1 555 123 4567');
        $manager->persist($contactJohn);

        $contactSarah = Contact::create('Sarah', 'Chen', $companyNexus)
            ->withEmail('sarah.chen@nexustech.io')
            ->withPersonalPhone('+1 555 987 6543');
        $manager->persist($contactSarah);

        $contactMarcus = Contact::create('Marcus', 'Weber', $companyPinnacle)
            ->withEmail('m.weber@pinnacle-solutions.de');
        $manager->persist($contactMarcus);

        $contactElena = Contact::create('Elena', 'Rodriguez', $companyVortex)
            ->withEmail('elena.r@vortex-ind.com')
            ->withWorkPhone('+34 612 345 678')
            ->withCommuncationChannel(CommunicationType::OTHER, 'Slack: @elena.rodriguez');
        $manager->persist($contactElena);

        $contactTomasz = Contact::create('Tomasz', 'Kowalski', $companyAcme)
            ->withEmail('t.kowalski@acme-corp.com');
        $manager->persist($contactTomasz);

        // Projects
        $projectERP = Project::create(
            'Enterprise Resource Planning Migration',
            'Complete migration of legacy ERP system to cloud-based solution with data integrity verification and staff training.',
        );
        $projectERP->addManager($pmKatarzyna);
        $projectERP->addManager($pmDavid);
        $projectERP->addCompany($companyAcme);
        $projectERP->addCompany($companyNexus);
        $manager->persist($projectERP);

        $projectWebsite = Project::create(
            'Corporate Website Redesign',
            'Modern responsive redesign with improved UX, accessibility compliance, and SEO optimization.',
            isFinished: true,
        );
        $projectWebsite->addManager($pmAisha);
        $projectWebsite->addCompany($companyPinnacle);
        $manager->persist($projectWebsite);

        $projectInventory = Project::create(
            'Warehouse Inventory System',
            'Real-time inventory tracking with barcode scanning, automated reordering, and analytics dashboard.',
        );
        $projectInventory->addManager($pmLars);
        $projectInventory->addManager($pmKatarzyna);
        $projectInventory->addCompany($companyVortex);
        $projectInventory->addCompany($companyAcme);
        $manager->persist($projectInventory);

        $projectCRM = Project::create(
            'Customer Relations Module',
            'Custom CRM integration with existing systems, automated follow-ups, and client communication tracking.',
        );
        $projectCRM->addManager($pmDavid);
        $projectCRM->addCompany($companyNexus);
        $manager->persist($projectCRM);

        $projectAudit = Project::create(
            'Q4 Financial Audit Preparation',
            'Preparation of financial documentation and compliance verification for annual audit.',
            isFinished: true,
        );
        $projectAudit->addManager($pmAisha);
        $projectAudit->addManager($pmLars);
        $projectAudit->addCompany($companyPinnacle);
        $projectAudit->addCompany($companyVortex);
        $manager->persist($projectAudit);

        $manager->flush();
    }
}
