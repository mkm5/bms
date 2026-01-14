<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Config\CommunicationType;
use App\Entity\Company;
use App\Entity\Contact;
use App\Entity\Project;
use App\Entity\Tag;
use App\Entity\Ticket;
use App\Entity\TicketStatus;
use App\Entity\TicketTask;
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

        // Ticket Status
        $ticketStatusToDo = TicketStatus::create('To Do', 0);
        $manager->persist($ticketStatusToDo);

        $ticketStatusInProgress = TicketStatus::create('In Progress', 1);
        $manager->persist($ticketStatusInProgress);

        $ticketStatusPaused = TicketStatus::create('Paused', 2);
        $manager->persist($ticketStatusPaused);

        $ticketStatusDone = TicketStatus::create('Done', 3);
        $manager->persist($ticketStatusDone);

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

        // Tags
        $tagBug = Tag::create('Bug');
        $manager->persist($tagBug);

        $tagFeature = Tag::create('Feature');
        $manager->persist($tagFeature);

        $tagUrgent = Tag::create('Urgent');
        $manager->persist($tagUrgent);

        $tagBackend = Tag::create('Backend');
        $manager->persist($tagBackend);

        $tagFrontend = Tag::create('Frontend');
        $manager->persist($tagFrontend);

        $tagDocumentation = Tag::create('Documentation');
        $manager->persist($tagDocumentation);

        $tagRefactor = Tag::create('Refactor');
        $manager->persist($tagRefactor);

        $tagSecurity = Tag::create('Security');
        $manager->persist($tagSecurity);

        $tagQuickWin = Tag::create('Low Hanging Fruit');
        $manager->persist($tagQuickWin);

        $tagTechDebt = Tag::create('Haunted Code');
        $manager->persist($tagTechDebt);

        $tagUx = Tag::create('User Whisperer');
        $manager->persist($tagUx);

        $tagPerformance = Tag::create('Need for Speed');
        $manager->persist($tagPerformance);

        $tagMobile = Tag::create('Pocket Sized');
        $manager->persist($tagMobile);

        $tagApi = Tag::create('Plumbing');
        $manager->persist($tagApi);

        // Tickets for ERP project
        $ticketErp1 = Ticket::create(
            $projectERP,
            'Database schema migration script',
            $ticketStatusInProgress,
            'Create migration scripts for transferring data from legacy Oracle database to PostgreSQL. Include data validation and rollback procedures.',
            [$tagBackend, $tagUrgent],
        );
        $ticketErp1->addTask(TicketTask::create($ticketErp1, 'Analyze current Oracle schema', true));
        $ticketErp1->addTask(TicketTask::create($ticketErp1, 'Design PostgreSQL schema mapping', true));
        $ticketErp1->addTask(TicketTask::create($ticketErp1, 'Write migration scripts'));
        $ticketErp1->addTask(TicketTask::create($ticketErp1, 'Test with sample data'));
        $manager->persist($ticketErp1);

        $ticketErp2 = Ticket::create(
            $projectERP,
            'User authentication integration',
            $ticketStatusToDo,
            null,
            [$tagBackend, $tagSecurity],
        );
        $manager->persist($ticketErp2);

        $ticketErp3 = Ticket::create(
            $projectERP,
            'Fix invoice calculation rounding error',
            $ticketStatusDone,
            'Currency amounts are incorrectly rounded in multi-currency transactions.',
            [$tagBug, $tagUrgent],
        );
        $manager->persist($ticketErp3);

        // Tickets for Inventory project
        $ticketInv1 = Ticket::create(
            $projectInventory,
            'Barcode scanner API integration',
            $ticketStatusInProgress,
            null,
            [$tagFeature, $tagBackend],
        );
        $ticketInv1->addTask(TicketTask::create($ticketInv1, 'Research scanner hardware compatibility', true));
        $ticketInv1->addTask(TicketTask::create($ticketInv1, 'Implement REST API endpoints'));
        $ticketInv1->addTask(TicketTask::create($ticketInv1, 'Create event listeners for scan events'));
        $manager->persist($ticketInv1);

        $ticketInv2 = Ticket::create(
            $projectInventory,
            'Dashboard charts not loading on Safari',
            $ticketStatusPaused,
            'Charts component throws JavaScript error on Safari 16+. Waiting for library update.',
            [$tagBug, $tagFrontend],
        );
        $manager->persist($ticketInv2);

        $ticketInv3 = Ticket::create(
            $projectInventory,
            'Automated reorder notifications',
            $ticketStatusToDo,
            null,
            [$tagFeature],
        );
        $manager->persist($ticketInv3);

        $ticketInv4 = Ticket::create(
            $projectInventory,
            'Update API documentation',
            $ticketStatusToDo,
            null,
            [$tagDocumentation],
        );
        $manager->persist($ticketInv4);

        // Tickets for CRM project
        $ticketCrm1 = Ticket::create(
            $projectCRM,
            'Email template builder',
            $ticketStatusInProgress,
            'Drag-and-drop email template builder for marketing campaigns.',
            [$tagFeature, $tagFrontend],
        );
        $ticketCrm1->addTask(TicketTask::create($ticketCrm1, 'Design component library', true));
        $ticketCrm1->addTask(TicketTask::create($ticketCrm1, 'Implement drag-and-drop functionality'));
        $ticketCrm1->addTask(TicketTask::create($ticketCrm1, 'Add template preview'));
        $ticketCrm1->addTask(TicketTask::create($ticketCrm1, 'Connect to email sending service'));
        $manager->persist($ticketCrm1);

        $ticketCrm2 = Ticket::create(
            $projectCRM,
            'Refactor contact search queries',
            $ticketStatusToDo,
            'Current search is slow with large datasets. Implement Elasticsearch.',
            [$tagRefactor, $tagBackend],
        );
        $manager->persist($ticketCrm2);

        $ticketCrm3 = Ticket::create(
            $projectCRM,
            'Client import from CSV fails silently',
            $ticketStatusDone,
            null,
            [$tagBug],
        );
        $manager->persist($ticketCrm3);

        $ticketCrm4 = Ticket::create(
            $projectCRM,
            'Add GDPR data export functionality',
            $ticketStatusToDo,
            null,
            [$tagFeature, $tagSecurity],
        );
        $manager->persist($ticketCrm4);

        $ticketCrm5 = Ticket::create(
            $projectCRM,
            'Mobile app crashes on contact list scroll',
            $ticketStatusInProgress,
            'App freezes when scrolling through 500+ contacts. Memory leak suspected.',
            [$tagBug, $tagMobile, $tagPerformance],
        );
        $ticketCrm5->addTask(TicketTask::create($ticketCrm5, 'Profile memory usage', true));
        $ticketCrm5->addTask(TicketTask::create($ticketCrm5, 'Implement virtualized list'));
        $manager->persist($ticketCrm5);

        $ticketCrm6 = Ticket::create(
            $projectCRM,
            'Add dark mode toggle',
            $ticketStatusToDo,
            null,
            [$tagQuickWin, $tagFrontend, $tagUx],
        );
        $manager->persist($ticketCrm6);

        // More tickets for ERP project
        $ticketErp4 = Ticket::create(
            $projectERP,
            'Legacy report generator still uses jQuery 1.x',
            $ticketStatusPaused,
            'The old reports module is a house of cards. Touch nothing until Q2.',
            [$tagTechDebt, $tagFrontend],
        );
        $manager->persist($ticketErp4);

        $ticketErp5 = Ticket::create(
            $projectERP,
            'API rate limiting for external integrations',
            $ticketStatusToDo,
            null,
            [$tagApi, $tagSecurity],
        );
        $manager->persist($ticketErp5);

        $ticketErp6 = Ticket::create(
            $projectERP,
            'Optimize batch invoice generation',
            $ticketStatusInProgress,
            'Currently takes 45 minutes for 10k invoices. Target: under 5 minutes.',
            [$tagPerformance, $tagBackend],
        );
        $ticketErp6->addTask(TicketTask::create($ticketErp6, 'Add database indexes', true));
        $ticketErp6->addTask(TicketTask::create($ticketErp6, 'Implement batch processing'));
        $ticketErp6->addTask(TicketTask::create($ticketErp6, 'Add progress indicator'));
        $manager->persist($ticketErp6);

        $ticketErp7 = Ticket::create(
            $projectERP,
            'Fix typo in welcome email',
            $ticketStatusDone,
            null,
            [$tagQuickWin],
        );
        $manager->persist($ticketErp7);

        // More tickets for Inventory project
        $ticketInv5 = Ticket::create(
            $projectInventory,
            'Warehouse map visualization',
            $ticketStatusToDo,
            'Interactive SVG map showing real-time stock levels per shelf.',
            [$tagFeature, $tagFrontend, $tagUx],
        );
        $manager->persist($ticketInv5);

        $ticketInv6 = Ticket::create(
            $projectInventory,
            'The forbidden stored procedure',
            $ticketStatusPaused,
            'Nobody knows what sp_calculate_mystery does. It was written in 2003. We are afraid.',
            [$tagTechDebt, $tagBackend],
        );
        $manager->persist($ticketInv6);

        $ticketInv7 = Ticket::create(
            $projectInventory,
            'Mobile stock check app',
            $ticketStatusInProgress,
            null,
            [$tagFeature, $tagMobile],
        );
        $ticketInv7->addTask(TicketTask::create($ticketInv7, 'Setup React Native project', true));
        $ticketInv7->addTask(TicketTask::create($ticketInv7, 'Implement barcode scanning', true));
        $ticketInv7->addTask(TicketTask::create($ticketInv7, 'Build stock lookup screen'));
        $ticketInv7->addTask(TicketTask::create($ticketInv7, 'Add offline mode'));
        $ticketInv7->addTask(TicketTask::create($ticketInv7, 'Submit to app stores'));
        $manager->persist($ticketInv7);

        $ticketInv8 = Ticket::create(
            $projectInventory,
            'REST API versioning strategy',
            $ticketStatusDone,
            null,
            [$tagApi, $tagDocumentation],
        );
        $manager->persist($ticketInv8);

        $manager->flush();
    }
}
