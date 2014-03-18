<?php

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use Core\Test\TestCase;
use MyCLabs\ACL\ACLManager;
use MyCLabs\ACL\Model\Actions;
use MyCLabs\ACL\Model\ClassResource;
use MyCLabs\ACL\Model\ResourceInterface;
use Orga\Model\ACL\Action\CellAction;
use Orga\Model\ACL\CellAdminRole;
use Orga\Model\ACL\CellContributorRole;
use Orga\Model\ACL\CellObserverRole;
use Orga\Model\ACL\OrganizationAdminRole;
use User\Domain\User;
use User\Domain\UserService;

class Orga_Test_ACLTest
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Orga_Test_ACL');
        return $suite;
    }
}

/**
 * Test des ACL dans Orga.
 *
 * @author valentin.claras
 */
class Orga_Test_ACL extends TestCase
{
    /**
     * @Inject
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Inject
     * @var ACLManager
     */
    protected $aclManager;

    /**
     * @var Orga_Model_Organization
     */
    protected $organization;

    /**
     * @var Orga_Model_Axis
     */
    protected $axisAnnee;

    /**
     * @var Orga_Model_Axis
     */
    protected $axisSite;

    /**
     * @var Orga_Model_Axis
     */
    protected $axisPays;

    /**
     * @var Orga_Model_Axis
     */
    protected $axisZone;

    /**
     * @var Orga_Model_Axis
     */
    protected $axisMarque;

    /**
     * @var Orga_Model_Axis
     */
    protected $axisCategorie;

    /**
     * @var Orga_Model_Member
     */
    protected $memberAnnee2012;

    /**
     * @var Orga_Model_Member
     */
    protected $memberAnnee2013;

    /**
     * @var Orga_Model_Member
     */
    protected $memberZoneEurope;

    /**
     * @var Orga_Model_Member
     */
    protected $memberZoneSudamerique;

    /**
     * @var Orga_Model_Member
     */
    protected $memberPaysFrance;

    /**
     * @var Orga_Model_Member
     */
    protected $memberPaysAllemagne;

    /**
     * @var Orga_Model_Member
     */
    protected $memberPaysPerou;

    /**
     * @var Orga_Model_Member
     */
    protected $memberMarqueA;

    /**
     * @var Orga_Model_Member
     */
    protected $memberMarqueB;

    /**
     * @var Orga_Model_Member
     */
    protected $memberSiteAnnecy;

    /**
     * @var Orga_Model_Member
     */
    protected $memberSiteChambery;

    /**
     * @var Orga_Model_Member
     */
    protected $memberSiteBerlin;

    /**
     * @var Orga_Model_Member
     */
    protected $memberSiteLima;

    /**
     * @var Orga_Model_Member
     */
    protected $memberCategorieEnergie;

    /**
     * @var Orga_Model_Member
     */
    protected $memberCategorieTransport;

    /**
     * @var Orga_Model_Granularity
     */
    protected $granularityGlobale;

    /**
     * @var Orga_Model_Granularity
     */
    protected $granularityZoneMarque;

    /**
     * @var Orga_Model_Granularity
     */
    protected $granularitySite;

    /**
     * @var Orga_Model_Granularity
     */
    protected $granularityAnnee;

    /**
     * @var Orga_Model_Granularity
     */
    protected $granularityAnneeCategorie;

    /**
     * @var Orga_Model_Granularity
     */
    protected $granularityAnneeZoneMarque;

    /**
     * @var Orga_Model_Granularity
     */
    protected $granularityAnneeSite;

    /**
     * @var Orga_Model_Granularity
     */
    protected $granularityAnneeSiteCategorie;

    /**
     * @var User
     */
    protected $organizationAdministrator;

    /**
     * @var User
     */
    protected $globaleCellAdministrator;

    /**
     * @var User
     */
    protected $europeaCellContributor;

    /**
     * @var User
     */
    protected $sudameriquebCellObserver;

    /**
     * @var User
     */
    protected $annecyCellAdministrator;

    /**
     * @var User
     */
    protected $limaCellContributor;

    /**
     * @var User
     */
    protected $berlinCellObserver;

    public function setUp()
    {
        parent::setUp();

        $account = new Account('Test');
        $this->accountRepository->add($account);

        // Création de l'organization (proche de populateTest au 08/08/2013).
        $this->organization = new Orga_Model_Organization($account);
        $this->organization->setLabel('ACL Test');
        $this->organization->save();

        // Nécéssaire du fait du bug Doctrine inserant les granularités avant les organisations.
        $this->entityManager->flush();

        // Création d'un ensemble d'axes.

        // Année.
        $this->axisAnnee = new Orga_Model_Axis($this->organization, 'annee');
        $this->axisAnnee->setLabel('Année');

        // Site.
        $this->axisSite = new Orga_Model_Axis($this->organization, 'site');
        $this->axisSite->setLabel('Site');

        // Pays.
        $this->axisPays = new Orga_Model_Axis($this->organization, 'pays', $this->axisSite);
        $this->axisPays->setLabel('Pays');

        // Zone.
        $this->axisZone = new Orga_Model_Axis($this->organization, 'zone', $this->axisPays);
        $this->axisZone->setLabel('Zone');

        // Marque.
        $this->axisMarque = new Orga_Model_Axis($this->organization, 'marque', $this->axisSite);
        $this->axisMarque->setLabel('Marque');

        // Catégories.
        $this->axisCategorie = new Orga_Model_Axis($this->organization, 'categorie');
        $this->axisCategorie->setLabel('Catégorie');

        // Création des membres des axes.

        // Années.
        $this->memberAnnee2012 = new Orga_Model_Member($this->axisAnnee, '2012');
        $this->memberAnnee2012->setLabel('2012');
        $this->memberAnnee2013 = new Orga_Model_Member($this->axisAnnee, '2013');
        $this->memberAnnee2013->setLabel('2013');

        // Zones.
        $this->memberZoneEurope = new Orga_Model_Member($this->axisZone, 'europe');
        $this->memberZoneEurope->setLabel('Europe');
        $this->memberZoneSudamerique = new Orga_Model_Member($this->axisZone, 'sudamerique');
        $this->memberZoneSudamerique->setLabel('Amerique du Sud');

        // Pays.
        $this->memberPaysFrance = new Orga_Model_Member($this->axisPays, 'france', [$this->memberZoneEurope]);
        $this->memberPaysFrance->setLabel('France');
        $this->memberPaysAllemagne = new Orga_Model_Member($this->axisPays, 'allemagne', [$this->memberZoneEurope]);
        $this->memberPaysAllemagne->setLabel('Allemagne');
        $this->memberPaysPerou = new Orga_Model_Member($this->axisPays, 'perou', [$this->memberZoneSudamerique]);
        $this->memberPaysPerou->setLabel('Pérou');

        // Marques.
        $this->memberMarqueA = new Orga_Model_Member($this->axisMarque, 'a');
        $this->memberMarqueA->setLabel('A');
        $this->memberMarqueB = new Orga_Model_Member($this->axisMarque, 'b');
        $this->memberMarqueB->setRef('b');
        $this->memberMarqueB->setLabel('B');

        // Sites.
        $this->memberSiteAnnecy = new Orga_Model_Member($this->axisSite, 'annecy', [$this->memberPaysFrance, $this->memberMarqueA]);
        $this->memberSiteAnnecy->setLabel('Annecy');
        $this->memberSiteChambery = new Orga_Model_Member($this->axisSite, 'chambery', [$this->memberPaysFrance, $this->memberMarqueA]);
        $this->memberSiteChambery->setLabel('Chambery');
        $this->memberSiteBerlin = new Orga_Model_Member($this->axisSite, 'berlin', [$this->memberPaysAllemagne, $this->memberMarqueB]);
        $this->memberSiteBerlin->setLabel('Berlin');
        $this->memberSiteLima = new Orga_Model_Member($this->axisSite, 'lima', [$this->memberPaysPerou, $this->memberMarqueB]);
        $this->memberSiteLima->setLabel('Lima');

        // Catégories.
        $this->memberCategorieEnergie = new Orga_Model_Member($this->axisCategorie, 'energie');
        $this->memberCategorieEnergie->setLabel('Énergie');
        $this->memberCategorieTransport = new Orga_Model_Member($this->axisCategorie, 'transport');
        $this->memberCategorieTransport->setLabel('Transport');

        // Création des granularités de l'organisation.

        // Création de la granularité globale.
        $this->granularityGlobale = new Orga_Model_Granularity($this->organization);
        $this->granularityGlobale->setCellsWithACL(true);
        $this->granularityGlobale->setCellsGenerateDWCubes(true);

        // Création de la granularité zone marque.
        $this->granularityZoneMarque = new Orga_Model_Granularity($this->organization, [$this->axisZone, $this->axisMarque]);
        $this->granularityZoneMarque->setCellsWithACL(true);
        $this->granularityZoneMarque->setCellsGenerateDWCubes(true);

        // Création de la granularité site.
        $this->granularitySite = new Orga_Model_Granularity($this->organization, [$this->axisSite]);
        $this->granularitySite->setCellsWithACL(true);
        $this->granularitySite->setCellsGenerateDWCubes(true);

        // Création de la granularité année.
        $this->granularityAnnee = new Orga_Model_Granularity($this->organization, [$this->axisAnnee]);

        // Création de la granularité année categorie.
        $this->granularityAnneeCategorie = new Orga_Model_Granularity($this->organization, [$this->axisAnnee, $this->axisCategorie]);

        // Création de la granularité année zone marque.
        $this->granularityAnneeZoneMarque = new Orga_Model_Granularity($this->organization, [$this->axisAnnee, $this->axisZone, $this->axisMarque]);

        // Création de la granularité année site.
        $this->granularityAnneeSite = new Orga_Model_Granularity($this->organization, [$this->axisAnnee, $this->axisSite]);

        // Création de la granularité année site categorie.
        $this->granularityAnneeSiteCategorie = new Orga_Model_Granularity($this->organization, [$this->axisAnnee, $this->axisSite, $this->axisCategorie]);

        // Sauvegarde.
        $this->organization->save();
        $this->entityManager->flush();

        // Ajout d'utilisateurs.

        // Ajout d'un utilisateur administrateur de l'administration.
        $this->organizationAdministrator= $this->userService->createUser('organizationAdministrator@example.com', 'organizationAdministrator');
        $this->aclManager->grant(
            $this->organizationAdministrator,
            new OrganizationAdminRole($this->organizationAdministrator, $this->organization)
        );

        // Ajout d'un administrateur de cellule globale.
        $this->globaleCellAdministrator = $this->userService->createUser('globalAdministrator@example.com', 'globalAdministrator');
        $this->aclManager->grant(
            $this->globaleCellAdministrator,
            new CellAdminRole($this->globaleCellAdministrator, $this->granularityGlobale->getCellByMembers([]))
        );

        // Ajout d'un contributeur de cellule zone marque.
        $this->europeaCellContributor = $this->userService->createUser('europeaContributor@example.com', 'europeaContributor');
        $this->aclManager->grant(
            $this->europeaCellContributor,
            new CellContributorRole($this->europeaCellContributor, $this->granularityZoneMarque->getCellByMembers(
                [$this->memberZoneEurope, $this->memberMarqueA]
            ))
        );

        // Ajout d'un observatur de cellule zone marque.
        $this->sudameriquebCellObserver = $this->userService->createUser('sudameriquebObserver@example.com', 'sudameriquebObserver');
        $this->aclManager->grant(
            $this->sudameriquebCellObserver,
            new CellObserverRole($this->sudameriquebCellObserver, $this->granularityZoneMarque->getCellByMembers(
                [$this->memberZoneSudamerique, $this->memberMarqueB]
            ))
        );

        // Ajout d'un administrateur de cellule site.
        $this->annecyCellAdministrator = $this->userService->createUser('annecyAdministrator@example.com', 'annecyAdministrator');
        $this->aclManager->grant(
            $this->annecyCellAdministrator,
            new CellAdminRole($this->annecyCellAdministrator, $this->granularitySite->getCellByMembers(
                [$this->memberSiteAnnecy]
            ))
        );

        // Ajout d'un contributeur de cellule site.
        $this->limaCellContributor = $this->userService->createUser('limaContributor@example.com', 'limaContributor');
        $this->aclManager->grant(
            $this->limaCellContributor,
            new CellContributorRole($this->limaCellContributor, $this->granularitySite->getCellByMembers(
                [$this->memberSiteLima]
            ))
        );

        // Ajout d'un observateur de cellule site.
        $this->berlinCellObserver = $this->userService->createUser('berlinObserver@example.com', 'berlinObserver');
        $this->aclManager->grant(
            $this->berlinCellObserver,
            new CellObserverRole($this->berlinCellObserver, $this->granularitySite->getCellByMembers(
                [$this->memberSiteBerlin]
            ))
        );

        $this->entityManager->flush();
    }

    /**
     * Test les points du vue formel (IsAllow) des utilisateurs.
     */
    public function testUsersIsAllowed()
    {
        $this->isAllowedOrganizationAdministrator();
        $this->isAllowedGlobalCellAdmin();
        $this->isAllowedAnnecyCellAdmin();

        $this->tIsAllowEuropeACellManager();
        $this->tIsAllowEuropeACellContributor();
        $this->tIsAllowSudameriqueBCellObserver();
        $this->tIsAllowBerlinCellObserver();
        $this->tIsAllowLimaCellContributor();
    }

    /**
     * Administrateur de l'organisation.
     */
    public function isAllowedOrganizationAdministrator()
    {
        // Ne peut pas créer d'organisation
        $this->assertNotAllowed(
            $this->organizationAdministrator,
            Actions::CREATE,
            new ClassResource(Orga_Model_Organization::class)
        );

        // Droit d'admin sur l'organisation
        $this->assertAdminOrganization($this->organizationAdministrator, $this->organization);

        // Droit en cascade d'admin sur toutes les cellules
        foreach ($this->organization->getGranularities() as $granularity) {
            foreach ($granularity->getCells() as $cell) {
                $this->assertAdminCell($this->organizationAdministrator, $cell);
            }
        }
    }

    /**
     * Administrateur de la cellule globale.
     */
    public function isAllowedGlobalCellAdmin()
    {
        $user = $this->globaleCellAdministrator;
        $cell = $this->granularityGlobale->getCellByMembers([]);

        $this->assertAdminCell($user, $cell);

        // Pas de droit sur l'organisation
        $this->assertNotAllowed($user, Actions::VIEW, $this->organization);
        $this->assertNotAllowed($user, Actions::EDIT, $this->organization);
        $this->assertNotAllowed($user, Actions::DELETE, $this->organization);
        $this->assertNotAllowed($user, Actions::UNDELETE, $this->organization);
        $this->assertNotAllowed($user, Actions::ALLOW, $this->organization);

        // Ne peut pas créer d'organisation
        $this->assertNotAllowed(
            $this->organizationAdministrator,
            Actions::CREATE,
            new ClassResource(Orga_Model_Organization::class)
        );
    }

    /**
     * Administrateur de la cellule Annecy.
     */
    public function isAllowedAnnecyCellAdmin()
    {
        $user = $this->annecyCellAdministrator;

        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertAdminCell($user, $cellAnnecy);

        // Pas de droits sur la cellule globale
        $globalCell = $this->granularityGlobale->getCellByMembers([]);
        $this->assertNotAllowed($user, Actions::VIEW, $globalCell);
        $this->assertNotAllowed($user, Actions::EDIT, $globalCell);
        $this->assertNotAllowed($user, Actions::DELETE, $globalCell);
        $this->assertNotAllowed($user, Actions::UNDELETE, $globalCell);
        $this->assertNotAllowed($user, Actions::ALLOW, $globalCell);

        // Pas de droit sur l'organisation
        $this->assertNotAllowed($user, Actions::VIEW, $this->organization);
        $this->assertNotAllowed($user, Actions::EDIT, $this->organization);
        $this->assertNotAllowed($user, Actions::DELETE, $this->organization);
        $this->assertNotAllowed($user, Actions::UNDELETE, $this->organization);
        $this->assertNotAllowed($user, Actions::ALLOW, $this->organization);

        // Ne peut pas créer d'organisation
        $this->assertNotAllowed(
            $this->organizationAdministrator,
            Actions::CREATE,
            new ClassResource(Orga_Model_Organization::class)
        );
    }

    /**
     * Test le point du vue (formel) du manager de la cellule europe a.
     */
    public function tIsAllowEuropeACellManager()
    {
        return; // désactivé pour le moment, role non créé (les droits sont normalement bons)
        $user = $this->europeaCellContributor;

        // Test toutes les ressources.

        // Organisation.
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::DELETE, $this->organization));

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell0));

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cellEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cellEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cellEuropeA));
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cellEuropeB));
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cellSudameriqueA));
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cellSudameriqueB));

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cellAnnecy));
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cellChambery));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cellChambery));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cellChambery));
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cellBerlin));
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cellLima));

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012));
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013));

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012Energie));
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012Transport));
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013Energie));
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013Transport));

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2012EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012EuropeA));
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012EuropeB));
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012SudameriqueA));
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012SudameriqueB));
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2013EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013EuropeA));
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013EuropeB));
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013SudameriqueA));
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013SudameriqueB));

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012Annecy));
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2012Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012Chambery));
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012Berlin));
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012Lima));
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013Annecy));
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2013Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013Chambery));
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013Berlin));
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013Lima));

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012AnnecyEnergie));
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012ChamberyEnergie));
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012BerlinEnergie));
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012LimaEnergie));
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013AnnecyEnergie));
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013ChamberyEnergie));
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013BerlinEnergie));
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013LimaEnergie));
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012AnnecyTransport));
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2012ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012ChamberyTransport));
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012BerlinTransport));
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2012LimaTransport));
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013AnnecyTransport));
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, Actions::VIEW, $cell2013ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013ChamberyTransport));
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013BerlinTransport));
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, Actions::VIEW, $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::COMMENT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, CellAction::INPUT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::EDIT, $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Actions::ALLOW, $cell2013LimaTransport));
    }

    /**
     * Test le point du vue (formel) du contributeur de la cellule europe a.
     */
    public function tIsAllowEuropeACellContributor()
    {
        $user = $this->europeaCellContributor;

        // Test toutes les ressources.

        // Organisation.
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $this->organization));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $this->organization));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::DELETE, $this->organization));

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell0));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell0));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell0));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell0));

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cellEuropeA));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellEuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellEuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellEuropeA));
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellEuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellEuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellEuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellEuropeB));
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellSudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellSudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellSudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellSudameriqueA));
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellSudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellSudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellSudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellSudameriqueB));

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cellAnnecy));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellAnnecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellAnnecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellAnnecy));
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cellChambery));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellChambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellChambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellChambery));
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellBerlin));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellBerlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellBerlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellBerlin));
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellLima));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellLima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellLima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellLima));

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012));
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013));

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Energie));
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Transport));
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Energie));
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Transport));

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012EuropeA));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012EuropeA));
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012EuropeB));
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012SudameriqueA));
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012SudameriqueB));
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013EuropeA));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013EuropeA));
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013EuropeB));
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013SudameriqueA));
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013SudameriqueB));

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Annecy));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Annecy));
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Chambery));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Chambery));
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Berlin));
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Lima));
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Annecy));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Annecy));
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Chambery));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Chambery));
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Berlin));
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Lima));

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012AnnecyEnergie));
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012ChamberyEnergie));
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012BerlinEnergie));
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012LimaEnergie));
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013AnnecyEnergie));
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013ChamberyEnergie));
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013BerlinEnergie));
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013LimaEnergie));
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012AnnecyTransport));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012AnnecyTransport));
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012ChamberyTransport));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012ChamberyTransport));
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012BerlinTransport));
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012LimaTransport));
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013AnnecyTransport));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013AnnecyTransport));
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013ChamberyTransport));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013ChamberyTransport));
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013BerlinTransport));
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013LimaTransport));
    }

    /**
     * Test le point du vue (formel) de l'observateur de la cellule sudamerique b.
     */
    public function tIsAllowSudameriqueBCellObserver()
    {
        $user = $this->sudameriquebCellObserver;

        // Test toutes les ressources.

        // Organisation.
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $this->organization));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $this->organization));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::DELETE, $this->organization));

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell0));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell0));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell0));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell0));

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellEuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellEuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellEuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellEuropeA));
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellEuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellEuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellEuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellEuropeB));
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellSudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellSudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellSudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellSudameriqueA));
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cellSudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellSudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellSudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellSudameriqueB));

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellAnnecy));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellAnnecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellAnnecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellAnnecy));
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellChambery));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellChambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellChambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellChambery));
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellBerlin));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellBerlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellBerlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellBerlin));
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cellLima));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellLima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellLima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellLima));

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012));
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013));

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Energie));
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Transport));
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Energie));
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Transport));

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012EuropeA));
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012EuropeB));
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012SudameriqueA));
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012SudameriqueB));
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013EuropeA));
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013EuropeB));
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013SudameriqueA));
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013SudameriqueB));

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Annecy));
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Chambery));
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Berlin));
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Lima));
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Annecy));
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Chambery));
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Berlin));
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Lima));

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012AnnecyEnergie));
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012ChamberyEnergie));
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012BerlinEnergie));
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012LimaEnergie));
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013AnnecyEnergie));
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013ChamberyEnergie));
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013BerlinEnergie));
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013LimaEnergie));
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012AnnecyTransport));
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012ChamberyTransport));
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012BerlinTransport));
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012LimaTransport));
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013AnnecyTransport));
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013ChamberyTransport));
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013BerlinTransport));
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013LimaTransport));
    }

    /**
     * Test le point du vue (formel) de l'observateur de la cellule berlin.
     */
    public function tIsAllowBerlinCellObserver()
    {
        $user = $this->berlinCellObserver;

        // Test toutes les ressources.

        // Organisation.
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $this->organization));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $this->organization));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::DELETE, $this->organization));

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell0));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell0));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell0));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell0));

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellEuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellEuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellEuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellEuropeA));
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellEuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellEuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellEuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellEuropeB));
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellSudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellSudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellSudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellSudameriqueA));
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellSudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellSudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellSudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellSudameriqueB));

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellAnnecy));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellAnnecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellAnnecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellAnnecy));
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellChambery));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellChambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellChambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellChambery));
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cellBerlin));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellBerlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellBerlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellBerlin));
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellLima));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellLima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellLima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellLima));

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012));
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013));

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Energie));
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Transport));
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Energie));
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Transport));

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012EuropeA));
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012EuropeB));
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012SudameriqueA));
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012SudameriqueB));
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013EuropeA));
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013EuropeB));
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013SudameriqueA));
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013SudameriqueB));

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Annecy));
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Chambery));
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Berlin));
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Lima));
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Annecy));
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Chambery));
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Berlin));
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Lima));

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012AnnecyEnergie));
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012ChamberyEnergie));
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012BerlinEnergie));
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012LimaEnergie));
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013AnnecyEnergie));
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013ChamberyEnergie));
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013BerlinEnergie));
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013LimaEnergie));
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012AnnecyTransport));
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012ChamberyTransport));
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012BerlinTransport));
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012LimaTransport));
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013AnnecyTransport));
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013ChamberyTransport));
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013BerlinTransport));
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013LimaTransport));
    }

    /**
     * Test le point du vue (formel) du contributeur de la cellule lima.
     */
    public function tIsAllowLimaCellContributor()
    {
        $user = $this->limaCellContributor;

        // Test toutes les ressources.

        // Organisation.
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $this->organization));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $this->organization));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::DELETE, $this->organization));

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell0));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell0));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell0));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell0));

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellEuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellEuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellEuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellEuropeA));
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellEuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellEuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellEuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellEuropeB));
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellSudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellSudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellSudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellSudameriqueA));
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellSudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellSudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellSudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellSudameriqueB));

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellAnnecy));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellAnnecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellAnnecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellAnnecy));
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellChambery));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellChambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellChambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellChambery));
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cellBerlin));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellBerlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellBerlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellBerlin));
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cellLima));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cellLima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cellLima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cellLima));

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012));
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013));

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Energie));
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Transport));
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Energie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Energie));
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Transport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Transport));

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012EuropeA));
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012EuropeB));
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012SudameriqueA));
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012SudameriqueB));
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013EuropeA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013EuropeA));
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013EuropeB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013EuropeB));
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013SudameriqueA));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013SudameriqueA));
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013SudameriqueB));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013SudameriqueB));

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Annecy));
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Chambery));
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Berlin));
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012Lima));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012Lima));
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Annecy));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Annecy));
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Chambery));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Chambery));
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Berlin));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Berlin));
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013Lima));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013Lima));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013Lima));

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012AnnecyEnergie));
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012ChamberyEnergie));
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012BerlinEnergie));
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012LimaEnergie));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012LimaEnergie));
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013AnnecyEnergie));
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013ChamberyEnergie));
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013BerlinEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013BerlinEnergie));
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013LimaEnergie));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013LimaEnergie));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013LimaEnergie));
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012AnnecyTransport));
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012ChamberyTransport));
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012BerlinTransport));
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2012LimaTransport));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2012LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2012LimaTransport));
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013AnnecyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013AnnecyTransport));
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013ChamberyTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013ChamberyTransport));
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013BerlinTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013BerlinTransport));
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclManager->isAllowed($user, Actions::VIEW, $cell2013LimaTransport));
        $this->assertTrue($this->aclManager->isAllowed($user, CellAction::INPUT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::EDIT, $cell2013LimaTransport));
        $this->assertFalse($this->aclManager->isAllowed($user, Actions::ALLOW, $cell2013LimaTransport));
    }

    /**
     * Test les points du vue effectifs (ACLFilter) des utilisateurs.
     */
    public function testUsersACLFilter()
    {
        $this->tACLFilterOrganizationAdministrator();
        $this->tACLFilterGlobaleCellAdministrator();
        $this->tACLFilterEuropeACellContributor();
        $this->tACLFilterSudameriqueBCellObserver();
        $this->tACLFilterAnnecyCellAdministrator();
        $this->tACLFilterBerlinCellObserver();
        $this->tACLFilterLimaCellContributor();
    }

    /**
     * Test le point du vue (effectif) de l'administrateur de l'organisation.
     */
    public function tACLFilterOrganizationAdministrator()
    {
        $user = $this->organizationAdministrator;

        // Query des différentes actions.
        $queryView = new Core_Model_Query();
        $queryView->aclFilter->enabled = true;
        $queryView->aclFilter->user = $user;
        $queryView->aclFilter->action = Actions::VIEW;
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = Actions::EDIT;
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = Actions::DELETE;
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = CellAction::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = Actions::ALLOW;

        // Test toutes les ressources.

        // Organisation.
        $organisationsView = Orga_Model_Organization::loadList($queryView);
        $this->assertCount(1, $organisationsView);
        $this->assertContains($this->organization, $organisationsView);
        $organisationsEdit = Orga_Model_Organization::loadList($queryEdit);
        $this->assertCount(1, $organisationsEdit);
        $this->assertContains($this->organization, $organisationsEdit);
        $organisationsDelete = Orga_Model_Organization::loadList($queryDelete);
        $this->assertCount(1, $organisationsDelete);
        $this->assertContains($this->organization, $organisationsDelete);

        $cellsView = Orga_Model_Cell::loadList($queryView);
        $this->assertCount(47, $cellsView);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(47, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(47, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(47, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertContains($cell0, $cellsView);
        $this->assertContains($cell0, $cellsInput);
        $this->assertContains($cell0, $cellsEdit);
        $this->assertContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cellEuropeA, $cellsView);
        $this->assertContains($cellEuropeA, $cellsInput);
        $this->assertContains($cellEuropeA, $cellsEdit);
        $this->assertContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertContains($cellEuropeB, $cellsView);
        $this->assertContains($cellEuropeB, $cellsInput);
        $this->assertContains($cellEuropeB, $cellsEdit);
        $this->assertContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertContains($cellSudameriqueA, $cellsView);
        $this->assertContains($cellSudameriqueA, $cellsInput);
        $this->assertContains($cellSudameriqueA, $cellsEdit);
        $this->assertContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cellSudameriqueB, $cellsView);
        $this->assertContains($cellSudameriqueB, $cellsInput);
        $this->assertContains($cellSudameriqueB, $cellsEdit);
        $this->assertContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertContains($cellAnnecy, $cellsView);
        $this->assertContains($cellAnnecy, $cellsInput);
        $this->assertContains($cellAnnecy, $cellsEdit);
        $this->assertContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertContains($cellChambery, $cellsView);
        $this->assertContains($cellChambery, $cellsInput);
        $this->assertContains($cellChambery, $cellsEdit);
        $this->assertContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertContains($cellBerlin, $cellsView);
        $this->assertContains($cellBerlin, $cellsInput);
        $this->assertContains($cellBerlin, $cellsEdit);
        $this->assertContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertContains($cellLima, $cellsView);
        $this->assertContains($cellLima, $cellsInput);
        $this->assertContains($cellLima, $cellsEdit);
        $this->assertContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertContains($cell2012, $cellsView);
        $this->assertContains($cell2012, $cellsInput);
        $this->assertContains($cell2012, $cellsEdit);
        $this->assertContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertContains($cell2013, $cellsView);
        $this->assertContains($cell2013, $cellsInput);
        $this->assertContains($cell2013, $cellsEdit);
        $this->assertContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012Energie, $cellsView);
        $this->assertContains($cell2012Energie, $cellsInput);
        $this->assertContains($cell2012Energie, $cellsEdit);
        $this->assertContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertContains($cell2012Transport, $cellsView);
        $this->assertContains($cell2012Transport, $cellsInput);
        $this->assertContains($cell2012Transport, $cellsEdit);
        $this->assertContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013Energie, $cellsView);
        $this->assertContains($cell2013Energie, $cellsInput);
        $this->assertContains($cell2013Energie, $cellsEdit);
        $this->assertContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertContains($cell2013Transport, $cellsView);
        $this->assertContains($cell2013Transport, $cellsInput);
        $this->assertContains($cell2013Transport, $cellsEdit);
        $this->assertContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cell2012EuropeA, $cellsView);
        $this->assertContains($cell2012EuropeA, $cellsInput);
        $this->assertContains($cell2012EuropeA, $cellsEdit);
        $this->assertContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertContains($cell2012EuropeB, $cellsView);
        $this->assertContains($cell2012EuropeB, $cellsInput);
        $this->assertContains($cell2012EuropeB, $cellsEdit);
        $this->assertContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertContains($cell2012SudameriqueA, $cellsView);
        $this->assertContains($cell2012SudameriqueA, $cellsInput);
        $this->assertContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cell2012SudameriqueB, $cellsView);
        $this->assertContains($cell2012SudameriqueB, $cellsInput);
        $this->assertContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cell2013EuropeA, $cellsView);
        $this->assertContains($cell2013EuropeA, $cellsInput);
        $this->assertContains($cell2013EuropeA, $cellsEdit);
        $this->assertContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertContains($cell2013EuropeB, $cellsView);
        $this->assertContains($cell2013EuropeB, $cellsInput);
        $this->assertContains($cell2013EuropeB, $cellsEdit);
        $this->assertContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertContains($cell2013SudameriqueA, $cellsView);
        $this->assertContains($cell2013SudameriqueA, $cellsInput);
        $this->assertContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cell2013SudameriqueB, $cellsView);
        $this->assertContains($cell2013SudameriqueB, $cellsInput);
        $this->assertContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertContains($cell2012Annecy, $cellsView);
        $this->assertContains($cell2012Annecy, $cellsInput);
        $this->assertContains($cell2012Annecy, $cellsEdit);
        $this->assertContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertContains($cell2012Chambery, $cellsView);
        $this->assertContains($cell2012Chambery, $cellsInput);
        $this->assertContains($cell2012Chambery, $cellsEdit);
        $this->assertContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertContains($cell2012Berlin, $cellsView);
        $this->assertContains($cell2012Berlin, $cellsInput);
        $this->assertContains($cell2012Berlin, $cellsEdit);
        $this->assertContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertContains($cell2012Lima, $cellsView);
        $this->assertContains($cell2012Lima, $cellsInput);
        $this->assertContains($cell2012Lima, $cellsEdit);
        $this->assertContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertContains($cell2013Annecy, $cellsView);
        $this->assertContains($cell2013Annecy, $cellsInput);
        $this->assertContains($cell2013Annecy, $cellsEdit);
        $this->assertContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertContains($cell2013Chambery, $cellsView);
        $this->assertContains($cell2013Chambery, $cellsInput);
        $this->assertContains($cell2013Chambery, $cellsEdit);
        $this->assertContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertContains($cell2013Berlin, $cellsView);
        $this->assertContains($cell2013Berlin, $cellsInput);
        $this->assertContains($cell2013Berlin, $cellsEdit);
        $this->assertContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertContains($cell2013Lima, $cellsView);
        $this->assertContains($cell2013Lima, $cellsInput);
        $this->assertContains($cell2013Lima, $cellsEdit);
        $this->assertContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012BerlinEnergie, $cellsView);
        $this->assertContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012LimaEnergie, $cellsView);
        $this->assertContains($cell2012LimaEnergie, $cellsInput);
        $this->assertContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013BerlinEnergie, $cellsView);
        $this->assertContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013LimaEnergie, $cellsView);
        $this->assertContains($cell2013LimaEnergie, $cellsInput);
        $this->assertContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2012AnnecyTransport, $cellsView);
        $this->assertContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertContains($cell2012ChamberyTransport, $cellsView);
        $this->assertContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertContains($cell2012BerlinTransport, $cellsView);
        $this->assertContains($cell2012BerlinTransport, $cellsInput);
        $this->assertContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2012LimaTransport, $cellsView);
        $this->assertContains($cell2012LimaTransport, $cellsInput);
        $this->assertContains($cell2012LimaTransport, $cellsEdit);
        $this->assertContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2013AnnecyTransport, $cellsView);
        $this->assertContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertContains($cell2013ChamberyTransport, $cellsView);
        $this->assertContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertContains($cell2013BerlinTransport, $cellsView);
        $this->assertContains($cell2013BerlinTransport, $cellsInput);
        $this->assertContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2013LimaTransport, $cellsView);
        $this->assertContains($cellLima, $cellsInput);
        $this->assertContains($cell2013LimaTransport, $cellsEdit);
        $this->assertContains($cell2013LimaTransport, $cellsAllow);
    }

    /**
     * Test le point du vue (effectif) de l'administrateur de la cellule globale.
     */
    public function tACLFilterGlobaleCellAdministrator()
    {
        $user = $this->globaleCellAdministrator;

        // Query des différentes actions.
        $queryView = new Core_Model_Query();
        $queryView->aclFilter->enabled = true;
        $queryView->aclFilter->user = $user;
        $queryView->aclFilter->action = Actions::VIEW;
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = Actions::EDIT;
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = Actions::DELETE;
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = CellAction::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = Actions::ALLOW;

        // Test toutes les ressources.

        // Organisation.
        $organisationsView = Orga_Model_Organization::loadList($queryView);
        $this->assertCount(1, $organisationsView);
        $this->assertContains($this->organization, $organisationsView);
        $organisationsEdit = Orga_Model_Organization::loadList($queryEdit);
        $this->assertCount(0, $organisationsEdit);
        $this->assertNotContains($this->organization, $organisationsEdit);
        $organisationsDelete = Orga_Model_Organization::loadList($queryDelete);
        $this->assertCount(0, $organisationsDelete);
        $this->assertNotContains($this->organization, $organisationsDelete);

        $cellsView = Orga_Model_Cell::loadList($queryView);
        $this->assertCount(47, $cellsView);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(47, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(47, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(47, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertContains($cell0, $cellsView);
        $this->assertContains($cell0, $cellsInput);
        $this->assertContains($cell0, $cellsEdit);
        $this->assertContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cellEuropeA, $cellsView);
        $this->assertContains($cellEuropeA, $cellsInput);
        $this->assertContains($cellEuropeA, $cellsEdit);
        $this->assertContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertContains($cellEuropeB, $cellsView);
        $this->assertContains($cellEuropeB, $cellsInput);
        $this->assertContains($cellEuropeB, $cellsEdit);
        $this->assertContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertContains($cellSudameriqueA, $cellsView);
        $this->assertContains($cellSudameriqueA, $cellsInput);
        $this->assertContains($cellSudameriqueA, $cellsEdit);
        $this->assertContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cellSudameriqueB, $cellsView);
        $this->assertContains($cellSudameriqueB, $cellsInput);
        $this->assertContains($cellSudameriqueB, $cellsEdit);
        $this->assertContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertContains($cellAnnecy, $cellsView);
        $this->assertContains($cellAnnecy, $cellsInput);
        $this->assertContains($cellAnnecy, $cellsEdit);
        $this->assertContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertContains($cellChambery, $cellsView);
        $this->assertContains($cellChambery, $cellsInput);
        $this->assertContains($cellChambery, $cellsEdit);
        $this->assertContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertContains($cellBerlin, $cellsView);
        $this->assertContains($cellBerlin, $cellsInput);
        $this->assertContains($cellBerlin, $cellsEdit);
        $this->assertContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertContains($cellLima, $cellsView);
        $this->assertContains($cellLima, $cellsInput);
        $this->assertContains($cellLima, $cellsEdit);
        $this->assertContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertContains($cell2012, $cellsView);
        $this->assertContains($cell2012, $cellsInput);
        $this->assertContains($cell2012, $cellsEdit);
        $this->assertContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertContains($cell2013, $cellsView);
        $this->assertContains($cell2013, $cellsInput);
        $this->assertContains($cell2013, $cellsEdit);
        $this->assertContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012Energie, $cellsView);
        $this->assertContains($cell2012Energie, $cellsInput);
        $this->assertContains($cell2012Energie, $cellsEdit);
        $this->assertContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertContains($cell2012Transport, $cellsView);
        $this->assertContains($cell2012Transport, $cellsInput);
        $this->assertContains($cell2012Transport, $cellsEdit);
        $this->assertContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013Energie, $cellsView);
        $this->assertContains($cell2013Energie, $cellsInput);
        $this->assertContains($cell2013Energie, $cellsEdit);
        $this->assertContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertContains($cell2013Transport, $cellsView);
        $this->assertContains($cell2013Transport, $cellsInput);
        $this->assertContains($cell2013Transport, $cellsEdit);
        $this->assertContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cell2012EuropeA, $cellsView);
        $this->assertContains($cell2012EuropeA, $cellsInput);
        $this->assertContains($cell2012EuropeA, $cellsEdit);
        $this->assertContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertContains($cell2012EuropeB, $cellsView);
        $this->assertContains($cell2012EuropeB, $cellsInput);
        $this->assertContains($cell2012EuropeB, $cellsEdit);
        $this->assertContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertContains($cell2012SudameriqueA, $cellsView);
        $this->assertContains($cell2012SudameriqueA, $cellsInput);
        $this->assertContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cell2012SudameriqueB, $cellsView);
        $this->assertContains($cell2012SudameriqueB, $cellsInput);
        $this->assertContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cell2013EuropeA, $cellsView);
        $this->assertContains($cell2013EuropeA, $cellsInput);
        $this->assertContains($cell2013EuropeA, $cellsEdit);
        $this->assertContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertContains($cell2013EuropeB, $cellsView);
        $this->assertContains($cell2013EuropeB, $cellsInput);
        $this->assertContains($cell2013EuropeB, $cellsEdit);
        $this->assertContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertContains($cell2013SudameriqueA, $cellsView);
        $this->assertContains($cell2013SudameriqueA, $cellsInput);
        $this->assertContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cell2013SudameriqueB, $cellsView);
        $this->assertContains($cell2013SudameriqueB, $cellsInput);
        $this->assertContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertContains($cell2012Annecy, $cellsView);
        $this->assertContains($cell2012Annecy, $cellsInput);
        $this->assertContains($cell2012Annecy, $cellsEdit);
        $this->assertContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertContains($cell2012Chambery, $cellsView);
        $this->assertContains($cell2012Chambery, $cellsInput);
        $this->assertContains($cell2012Chambery, $cellsEdit);
        $this->assertContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertContains($cell2012Berlin, $cellsView);
        $this->assertContains($cell2012Berlin, $cellsInput);
        $this->assertContains($cell2012Berlin, $cellsEdit);
        $this->assertContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertContains($cell2012Lima, $cellsView);
        $this->assertContains($cell2012Lima, $cellsInput);
        $this->assertContains($cell2012Lima, $cellsEdit);
        $this->assertContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertContains($cell2013Annecy, $cellsView);
        $this->assertContains($cell2013Annecy, $cellsInput);
        $this->assertContains($cell2013Annecy, $cellsEdit);
        $this->assertContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertContains($cell2013Chambery, $cellsView);
        $this->assertContains($cell2013Chambery, $cellsInput);
        $this->assertContains($cell2013Chambery, $cellsEdit);
        $this->assertContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertContains($cell2013Berlin, $cellsView);
        $this->assertContains($cell2013Berlin, $cellsInput);
        $this->assertContains($cell2013Berlin, $cellsEdit);
        $this->assertContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertContains($cell2013Lima, $cellsView);
        $this->assertContains($cell2013Lima, $cellsInput);
        $this->assertContains($cell2013Lima, $cellsEdit);
        $this->assertContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012BerlinEnergie, $cellsView);
        $this->assertContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012LimaEnergie, $cellsView);
        $this->assertContains($cell2012LimaEnergie, $cellsInput);
        $this->assertContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013BerlinEnergie, $cellsView);
        $this->assertContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013LimaEnergie, $cellsView);
        $this->assertContains($cell2013LimaEnergie, $cellsInput);
        $this->assertContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2012AnnecyTransport, $cellsView);
        $this->assertContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertContains($cell2012ChamberyTransport, $cellsView);
        $this->assertContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertContains($cell2012BerlinTransport, $cellsView);
        $this->assertContains($cell2012BerlinTransport, $cellsInput);
        $this->assertContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2012LimaTransport, $cellsView);
        $this->assertContains($cell2012LimaTransport, $cellsInput);
        $this->assertContains($cell2012LimaTransport, $cellsEdit);
        $this->assertContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2013AnnecyTransport, $cellsView);
        $this->assertContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertContains($cell2013ChamberyTransport, $cellsView);
        $this->assertContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertContains($cell2013BerlinTransport, $cellsView);
        $this->assertContains($cell2013BerlinTransport, $cellsInput);
        $this->assertContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2013LimaTransport, $cellsView);
        $this->assertContains($cellLima, $cellsInput);
        $this->assertContains($cell2013LimaTransport, $cellsEdit);
        $this->assertContains($cell2013LimaTransport, $cellsAllow);
    }

    /**
     * Test le point du vue (effectif) du contributeur de la cellule europe a.
     */
    public function tACLFilterEuropeACellContributor()
    {
        $user = $this->europeaCellContributor;

        // Query des différentes actions.
        $queryView = new Core_Model_Query();
        $queryView->aclFilter->enabled = true;
        $queryView->aclFilter->user = $user;
        $queryView->aclFilter->action = Actions::VIEW;
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = Actions::EDIT;
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = Actions::DELETE;
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = CellAction::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = Actions::ALLOW;

        // Test toutes les ressources.

        // Organisation.
        $organisationsView = Orga_Model_Organization::loadList($queryView);
        $this->assertCount(1, $organisationsView);
        $this->assertContains($this->organization, $organisationsView);
        $organisationsEdit = Orga_Model_Organization::loadList($queryEdit);
        $this->assertCount(0, $organisationsEdit);
        $this->assertNotContains($this->organization, $organisationsEdit);
        $organisationsDelete = Orga_Model_Organization::loadList($queryDelete);
        $this->assertCount(0, $organisationsDelete);
        $this->assertNotContains($this->organization, $organisationsDelete);

        $cellsView = Orga_Model_Cell::loadList($queryView);
        $this->assertCount(17, $cellsView);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(17, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(0, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(0, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertNotContains($cell0, $cellsView);
        $this->assertNotContains($cell0, $cellsInput);
        $this->assertNotContains($cell0, $cellsEdit);
        $this->assertNotContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cellEuropeA, $cellsView);
        $this->assertContains($cellEuropeA, $cellsInput);
        $this->assertNotContains($cellEuropeA, $cellsEdit);
        $this->assertNotContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cellEuropeB, $cellsView);
        $this->assertNotContains($cellEuropeB, $cellsInput);
        $this->assertNotContains($cellEuropeB, $cellsEdit);
        $this->assertNotContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cellSudameriqueA, $cellsView);
        $this->assertNotContains($cellSudameriqueA, $cellsInput);
        $this->assertNotContains($cellSudameriqueA, $cellsEdit);
        $this->assertNotContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cellSudameriqueB, $cellsView);
        $this->assertNotContains($cellSudameriqueB, $cellsInput);
        $this->assertNotContains($cellSudameriqueB, $cellsEdit);
        $this->assertNotContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertContains($cellAnnecy, $cellsView);
        $this->assertContains($cellAnnecy, $cellsInput);
        $this->assertNotContains($cellAnnecy, $cellsEdit);
        $this->assertNotContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertContains($cellChambery, $cellsView);
        $this->assertContains($cellChambery, $cellsInput);
        $this->assertNotContains($cellChambery, $cellsEdit);
        $this->assertNotContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertNotContains($cellBerlin, $cellsView);
        $this->assertNotContains($cellBerlin, $cellsInput);
        $this->assertNotContains($cellBerlin, $cellsEdit);
        $this->assertNotContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertNotContains($cellLima, $cellsView);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cellLima, $cellsEdit);
        $this->assertNotContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertNotContains($cell2012, $cellsView);
        $this->assertNotContains($cell2012, $cellsInput);
        $this->assertNotContains($cell2012, $cellsEdit);
        $this->assertNotContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertNotContains($cell2013, $cellsView);
        $this->assertNotContains($cell2013, $cellsInput);
        $this->assertNotContains($cell2013, $cellsEdit);
        $this->assertNotContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012Energie, $cellsView);
        $this->assertNotContains($cell2012Energie, $cellsInput);
        $this->assertNotContains($cell2012Energie, $cellsEdit);
        $this->assertNotContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012Transport, $cellsView);
        $this->assertNotContains($cell2012Transport, $cellsInput);
        $this->assertNotContains($cell2012Transport, $cellsEdit);
        $this->assertNotContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013Energie, $cellsView);
        $this->assertNotContains($cell2013Energie, $cellsInput);
        $this->assertNotContains($cell2013Energie, $cellsEdit);
        $this->assertNotContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013Transport, $cellsView);
        $this->assertNotContains($cell2013Transport, $cellsInput);
        $this->assertNotContains($cell2013Transport, $cellsEdit);
        $this->assertNotContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cell2012EuropeA, $cellsView);
        $this->assertContains($cell2012EuropeA, $cellsInput);
        $this->assertNotContains($cell2012EuropeA, $cellsEdit);
        $this->assertNotContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2012EuropeB, $cellsView);
        $this->assertNotContains($cell2012EuropeB, $cellsInput);
        $this->assertNotContains($cell2012EuropeB, $cellsEdit);
        $this->assertNotContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2012SudameriqueA, $cellsView);
        $this->assertNotContains($cell2012SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2012SudameriqueB, $cellsView);
        $this->assertNotContains($cell2012SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cell2013EuropeA, $cellsView);
        $this->assertContains($cell2013EuropeA, $cellsInput);
        $this->assertNotContains($cell2013EuropeA, $cellsEdit);
        $this->assertNotContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2013EuropeB, $cellsView);
        $this->assertNotContains($cell2013EuropeB, $cellsInput);
        $this->assertNotContains($cell2013EuropeB, $cellsEdit);
        $this->assertNotContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2013SudameriqueA, $cellsView);
        $this->assertNotContains($cell2013SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2013SudameriqueB, $cellsView);
        $this->assertNotContains($cell2013SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertContains($cell2012Annecy, $cellsView);
        $this->assertContains($cell2012Annecy, $cellsInput);
        $this->assertNotContains($cell2012Annecy, $cellsEdit);
        $this->assertNotContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertContains($cell2012Chambery, $cellsView);
        $this->assertContains($cell2012Chambery, $cellsInput);
        $this->assertNotContains($cell2012Chambery, $cellsEdit);
        $this->assertNotContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2012Berlin, $cellsView);
        $this->assertNotContains($cell2012Berlin, $cellsInput);
        $this->assertNotContains($cell2012Berlin, $cellsEdit);
        $this->assertNotContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertNotContains($cell2012Lima, $cellsView);
        $this->assertNotContains($cell2012Lima, $cellsInput);
        $this->assertNotContains($cell2012Lima, $cellsEdit);
        $this->assertNotContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertContains($cell2013Annecy, $cellsView);
        $this->assertContains($cell2013Annecy, $cellsInput);
        $this->assertNotContains($cell2013Annecy, $cellsEdit);
        $this->assertNotContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertContains($cell2013Chambery, $cellsView);
        $this->assertContains($cell2013Chambery, $cellsInput);
        $this->assertNotContains($cell2013Chambery, $cellsEdit);
        $this->assertNotContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2013Berlin, $cellsView);
        $this->assertNotContains($cell2013Berlin, $cellsInput);
        $this->assertNotContains($cell2013Berlin, $cellsEdit);
        $this->assertNotContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertNotContains($cell2013Lima, $cellsView);
        $this->assertNotContains($cell2013Lima, $cellsInput);
        $this->assertNotContains($cell2013Lima, $cellsEdit);
        $this->assertNotContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012LimaEnergie, $cellsView);
        $this->assertNotContains($cell2012LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013LimaEnergie, $cellsView);
        $this->assertNotContains($cell2013LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2012AnnecyTransport, $cellsView);
        $this->assertContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertContains($cell2012ChamberyTransport, $cellsView);
        $this->assertContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012BerlinTransport, $cellsView);
        $this->assertNotContains($cell2012BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012LimaTransport, $cellsView);
        $this->assertNotContains($cell2012LimaTransport, $cellsInput);
        $this->assertNotContains($cell2012LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2013AnnecyTransport, $cellsView);
        $this->assertContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertContains($cell2013ChamberyTransport, $cellsView);
        $this->assertContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013BerlinTransport, $cellsView);
        $this->assertNotContains($cell2013BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013LimaTransport, $cellsView);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cell2013LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2013LimaTransport, $cellsAllow);
    }

    /**
     * Test le point du vue (effectif) de l'observateur de la cellule sudamerique b.
     */
    public function tACLFilterSudameriqueBCellObserver()
    {
        $user = $this->sudameriquebCellObserver;

        // Query des différentes actions.
        $queryView = new Core_Model_Query();
        $queryView->aclFilter->enabled = true;
        $queryView->aclFilter->user = $user;
        $queryView->aclFilter->action = Actions::VIEW;
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = Actions::EDIT;
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = Actions::DELETE;
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = CellAction::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = Actions::ALLOW;

        // Test toutes les ressources.

        // Organisation.
        $organisationsView = Orga_Model_Organization::loadList($queryView);
        $this->assertCount(1, $organisationsView);
        $this->assertContains($this->organization, $organisationsView);
        $organisationsEdit = Orga_Model_Organization::loadList($queryEdit);
        $this->assertCount(0, $organisationsEdit);
        $this->assertNotContains($this->organization, $organisationsEdit);
        $organisationsDelete = Orga_Model_Organization::loadList($queryDelete);
        $this->assertCount(0, $organisationsDelete);
        $this->assertNotContains($this->organization, $organisationsDelete);

        $cellsView = Orga_Model_Cell::loadList($queryView);
        $this->assertCount(10, $cellsView);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(0, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(0, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(0, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertNotContains($cell0, $cellsView);
        $this->assertNotContains($cell0, $cellsInput);
        $this->assertNotContains($cell0, $cellsEdit);
        $this->assertNotContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cellEuropeA, $cellsView);
        $this->assertNotContains($cellEuropeA, $cellsInput);
        $this->assertNotContains($cellEuropeA, $cellsEdit);
        $this->assertNotContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cellEuropeB, $cellsView);
        $this->assertNotContains($cellEuropeB, $cellsInput);
        $this->assertNotContains($cellEuropeB, $cellsEdit);
        $this->assertNotContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cellSudameriqueA, $cellsView);
        $this->assertNotContains($cellSudameriqueA, $cellsInput);
        $this->assertNotContains($cellSudameriqueA, $cellsEdit);
        $this->assertNotContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cellSudameriqueB, $cellsView);
        $this->assertNotContains($cellSudameriqueB, $cellsInput);
        $this->assertNotContains($cellSudameriqueB, $cellsEdit);
        $this->assertNotContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertNotContains($cellAnnecy, $cellsView);
        $this->assertNotContains($cellAnnecy, $cellsInput);
        $this->assertNotContains($cellAnnecy, $cellsEdit);
        $this->assertNotContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertNotContains($cellChambery, $cellsView);
        $this->assertNotContains($cellChambery, $cellsInput);
        $this->assertNotContains($cellChambery, $cellsEdit);
        $this->assertNotContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertNotContains($cellBerlin, $cellsView);
        $this->assertNotContains($cellBerlin, $cellsInput);
        $this->assertNotContains($cellBerlin, $cellsEdit);
        $this->assertNotContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertContains($cellLima, $cellsView);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cellLima, $cellsEdit);
        $this->assertNotContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertNotContains($cell2012, $cellsView);
        $this->assertNotContains($cell2012, $cellsInput);
        $this->assertNotContains($cell2012, $cellsEdit);
        $this->assertNotContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertNotContains($cell2013, $cellsView);
        $this->assertNotContains($cell2013, $cellsInput);
        $this->assertNotContains($cell2013, $cellsEdit);
        $this->assertNotContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012Energie, $cellsView);
        $this->assertNotContains($cell2012Energie, $cellsInput);
        $this->assertNotContains($cell2012Energie, $cellsEdit);
        $this->assertNotContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012Transport, $cellsView);
        $this->assertNotContains($cell2012Transport, $cellsInput);
        $this->assertNotContains($cell2012Transport, $cellsEdit);
        $this->assertNotContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013Energie, $cellsView);
        $this->assertNotContains($cell2013Energie, $cellsInput);
        $this->assertNotContains($cell2013Energie, $cellsEdit);
        $this->assertNotContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013Transport, $cellsView);
        $this->assertNotContains($cell2013Transport, $cellsInput);
        $this->assertNotContains($cell2013Transport, $cellsEdit);
        $this->assertNotContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2012EuropeA, $cellsView);
        $this->assertNotContains($cell2012EuropeA, $cellsInput);
        $this->assertNotContains($cell2012EuropeA, $cellsEdit);
        $this->assertNotContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2012EuropeB, $cellsView);
        $this->assertNotContains($cell2012EuropeB, $cellsInput);
        $this->assertNotContains($cell2012EuropeB, $cellsEdit);
        $this->assertNotContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2012SudameriqueA, $cellsView);
        $this->assertNotContains($cell2012SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cell2012SudameriqueB, $cellsView);
        $this->assertNotContains($cell2012SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2013EuropeA, $cellsView);
        $this->assertNotContains($cell2013EuropeA, $cellsInput);
        $this->assertNotContains($cell2013EuropeA, $cellsEdit);
        $this->assertNotContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2013EuropeB, $cellsView);
        $this->assertNotContains($cell2013EuropeB, $cellsInput);
        $this->assertNotContains($cell2013EuropeB, $cellsEdit);
        $this->assertNotContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2013SudameriqueA, $cellsView);
        $this->assertNotContains($cell2013SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cell2013SudameriqueB, $cellsView);
        $this->assertNotContains($cell2013SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertNotContains($cell2012Annecy, $cellsView);
        $this->assertNotContains($cell2012Annecy, $cellsInput);
        $this->assertNotContains($cell2012Annecy, $cellsEdit);
        $this->assertNotContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertNotContains($cell2012Chambery, $cellsView);
        $this->assertNotContains($cell2012Chambery, $cellsInput);
        $this->assertNotContains($cell2012Chambery, $cellsEdit);
        $this->assertNotContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2012Berlin, $cellsView);
        $this->assertNotContains($cell2012Berlin, $cellsInput);
        $this->assertNotContains($cell2012Berlin, $cellsEdit);
        $this->assertNotContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertContains($cell2012Lima, $cellsView);
        $this->assertNotContains($cell2012Lima, $cellsInput);
        $this->assertNotContains($cell2012Lima, $cellsEdit);
        $this->assertNotContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertNotContains($cell2013Annecy, $cellsView);
        $this->assertNotContains($cell2013Annecy, $cellsInput);
        $this->assertNotContains($cell2013Annecy, $cellsEdit);
        $this->assertNotContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertNotContains($cell2013Chambery, $cellsView);
        $this->assertNotContains($cell2013Chambery, $cellsInput);
        $this->assertNotContains($cell2013Chambery, $cellsEdit);
        $this->assertNotContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2013Berlin, $cellsView);
        $this->assertNotContains($cell2013Berlin, $cellsInput);
        $this->assertNotContains($cell2013Berlin, $cellsEdit);
        $this->assertNotContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertContains($cell2013Lima, $cellsView);
        $this->assertNotContains($cell2013Lima, $cellsInput);
        $this->assertNotContains($cell2013Lima, $cellsEdit);
        $this->assertNotContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012LimaEnergie, $cellsView);
        $this->assertNotContains($cell2012LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013LimaEnergie, $cellsView);
        $this->assertNotContains($cell2013LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsView);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012BerlinTransport, $cellsView);
        $this->assertNotContains($cell2012BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2012LimaTransport, $cellsView);
        $this->assertNotContains($cell2012LimaTransport, $cellsInput);
        $this->assertNotContains($cell2012LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsView);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013BerlinTransport, $cellsView);
        $this->assertNotContains($cell2013BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2013LimaTransport, $cellsView);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cell2013LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2013LimaTransport, $cellsAllow);
    }

    /**
     * Test le point du vue (effectif) de l'administrateur de la cellule annecy.
     */
    public function tACLFilterAnnecyCellAdministrator()
    {
        $user = $this->annecyCellAdministrator;

        // Query des différentes actions.
        $queryView = new Core_Model_Query();
        $queryView->aclFilter->enabled = true;
        $queryView->aclFilter->user = $user;
        $queryView->aclFilter->action = Actions::VIEW;
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = Actions::EDIT;
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = Actions::DELETE;
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = CellAction::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = Actions::ALLOW;

        // Test toutes les ressources.

        // Organisation.
        $organisationsView = Orga_Model_Organization::loadList($queryView);
        $this->assertCount(1, $organisationsView);
        $this->assertContains($this->organization, $organisationsView);
        $organisationsEdit = Orga_Model_Organization::loadList($queryEdit);
        $this->assertCount(0, $organisationsEdit);
        $this->assertNotContains($this->organization, $organisationsEdit);
        $organisationsDelete = Orga_Model_Organization::loadList($queryDelete);
        $this->assertCount(0, $organisationsDelete);
        $this->assertNotContains($this->organization, $organisationsDelete);

        $cellsView = Orga_Model_Cell::loadList($queryView);
        $this->assertCount(7, $cellsView);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(7, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(7, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(7, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertNotContains($cell0, $cellsView);
        $this->assertNotContains($cell0, $cellsInput);
        $this->assertNotContains($cell0, $cellsEdit);
        $this->assertNotContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cellEuropeA, $cellsView);
        $this->assertNotContains($cellEuropeA, $cellsInput);
        $this->assertNotContains($cellEuropeA, $cellsEdit);
        $this->assertNotContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cellEuropeB, $cellsView);
        $this->assertNotContains($cellEuropeB, $cellsInput);
        $this->assertNotContains($cellEuropeB, $cellsEdit);
        $this->assertNotContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cellSudameriqueA, $cellsView);
        $this->assertNotContains($cellSudameriqueA, $cellsInput);
        $this->assertNotContains($cellSudameriqueA, $cellsEdit);
        $this->assertNotContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cellSudameriqueB, $cellsView);
        $this->assertNotContains($cellSudameriqueB, $cellsInput);
        $this->assertNotContains($cellSudameriqueB, $cellsEdit);
        $this->assertNotContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertContains($cellAnnecy, $cellsView);
        $this->assertContains($cellAnnecy, $cellsInput);
        $this->assertContains($cellAnnecy, $cellsEdit);
        $this->assertContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertNotContains($cellChambery, $cellsView);
        $this->assertNotContains($cellChambery, $cellsInput);
        $this->assertNotContains($cellChambery, $cellsEdit);
        $this->assertNotContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertNotContains($cellBerlin, $cellsView);
        $this->assertNotContains($cellBerlin, $cellsInput);
        $this->assertNotContains($cellBerlin, $cellsEdit);
        $this->assertNotContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertNotContains($cellLima, $cellsView);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cellLima, $cellsEdit);
        $this->assertNotContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertNotContains($cell2012, $cellsView);
        $this->assertNotContains($cell2012, $cellsInput);
        $this->assertNotContains($cell2012, $cellsEdit);
        $this->assertNotContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertNotContains($cell2013, $cellsView);
        $this->assertNotContains($cell2013, $cellsInput);
        $this->assertNotContains($cell2013, $cellsEdit);
        $this->assertNotContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012Energie, $cellsView);
        $this->assertNotContains($cell2012Energie, $cellsInput);
        $this->assertNotContains($cell2012Energie, $cellsEdit);
        $this->assertNotContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012Transport, $cellsView);
        $this->assertNotContains($cell2012Transport, $cellsInput);
        $this->assertNotContains($cell2012Transport, $cellsEdit);
        $this->assertNotContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013Energie, $cellsView);
        $this->assertNotContains($cell2013Energie, $cellsInput);
        $this->assertNotContains($cell2013Energie, $cellsEdit);
        $this->assertNotContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013Transport, $cellsView);
        $this->assertNotContains($cell2013Transport, $cellsInput);
        $this->assertNotContains($cell2013Transport, $cellsEdit);
        $this->assertNotContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2012EuropeA, $cellsView);
        $this->assertNotContains($cell2012EuropeA, $cellsInput);
        $this->assertNotContains($cell2012EuropeA, $cellsEdit);
        $this->assertNotContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2012EuropeB, $cellsView);
        $this->assertNotContains($cell2012EuropeB, $cellsInput);
        $this->assertNotContains($cell2012EuropeB, $cellsEdit);
        $this->assertNotContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2012SudameriqueA, $cellsView);
        $this->assertNotContains($cell2012SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2012SudameriqueB, $cellsView);
        $this->assertNotContains($cell2012SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2013EuropeA, $cellsView);
        $this->assertNotContains($cell2013EuropeA, $cellsInput);
        $this->assertNotContains($cell2013EuropeA, $cellsEdit);
        $this->assertNotContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2013EuropeB, $cellsView);
        $this->assertNotContains($cell2013EuropeB, $cellsInput);
        $this->assertNotContains($cell2013EuropeB, $cellsEdit);
        $this->assertNotContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2013SudameriqueA, $cellsView);
        $this->assertNotContains($cell2013SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2013SudameriqueB, $cellsView);
        $this->assertNotContains($cell2013SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertContains($cell2012Annecy, $cellsView);
        $this->assertContains($cell2012Annecy, $cellsInput);
        $this->assertContains($cell2012Annecy, $cellsEdit);
        $this->assertContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertNotContains($cell2012Chambery, $cellsView);
        $this->assertNotContains($cell2012Chambery, $cellsInput);
        $this->assertNotContains($cell2012Chambery, $cellsEdit);
        $this->assertNotContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2012Berlin, $cellsView);
        $this->assertNotContains($cell2012Berlin, $cellsInput);
        $this->assertNotContains($cell2012Berlin, $cellsEdit);
        $this->assertNotContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertNotContains($cell2012Lima, $cellsView);
        $this->assertNotContains($cell2012Lima, $cellsInput);
        $this->assertNotContains($cell2012Lima, $cellsEdit);
        $this->assertNotContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertContains($cell2013Annecy, $cellsView);
        $this->assertContains($cell2013Annecy, $cellsInput);
        $this->assertContains($cell2013Annecy, $cellsEdit);
        $this->assertContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertNotContains($cell2013Chambery, $cellsView);
        $this->assertNotContains($cell2013Chambery, $cellsInput);
        $this->assertNotContains($cell2013Chambery, $cellsEdit);
        $this->assertNotContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2013Berlin, $cellsView);
        $this->assertNotContains($cell2013Berlin, $cellsInput);
        $this->assertNotContains($cell2013Berlin, $cellsEdit);
        $this->assertNotContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertNotContains($cell2013Lima, $cellsView);
        $this->assertNotContains($cell2013Lima, $cellsInput);
        $this->assertNotContains($cell2013Lima, $cellsEdit);
        $this->assertNotContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012LimaEnergie, $cellsView);
        $this->assertNotContains($cell2012LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013LimaEnergie, $cellsView);
        $this->assertNotContains($cell2013LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2012AnnecyTransport, $cellsView);
        $this->assertContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012BerlinTransport, $cellsView);
        $this->assertNotContains($cell2012BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012LimaTransport, $cellsView);
        $this->assertNotContains($cell2012LimaTransport, $cellsInput);
        $this->assertNotContains($cell2012LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2013AnnecyTransport, $cellsView);
        $this->assertContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013BerlinTransport, $cellsView);
        $this->assertNotContains($cell2013BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013LimaTransport, $cellsView);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cell2013LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2013LimaTransport, $cellsAllow);
    }

    /**
     * Test le point du vue (effectif) de l'observateur de la cellule berlin.
     */
    public function tACLFilterBerlinCellObserver()
    {
        $user = $this->berlinCellObserver;

        // Query des différentes actions.
        $queryView = new Core_Model_Query();
        $queryView->aclFilter->enabled = true;
        $queryView->aclFilter->user = $user;
        $queryView->aclFilter->action = Actions::VIEW;
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = Actions::EDIT;
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = Actions::DELETE;
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = CellAction::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = Actions::ALLOW;

        // Test toutes les ressources.

        // Organisation.
        $organisationsView = Orga_Model_Organization::loadList($queryView);
        $this->assertCount(1, $organisationsView);
        $this->assertContains($this->organization, $organisationsView);
        $organisationsEdit = Orga_Model_Organization::loadList($queryEdit);
        $this->assertCount(0, $organisationsEdit);
        $this->assertNotContains($this->organization, $organisationsEdit);
        $organisationsDelete = Orga_Model_Organization::loadList($queryDelete);
        $this->assertCount(0, $organisationsDelete);
        $this->assertNotContains($this->organization, $organisationsDelete);

        $cellsView = Orga_Model_Cell::loadList($queryView);
        $this->assertCount(7, $cellsView);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(0, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(0, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(0, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertNotContains($cell0, $cellsView);
        $this->assertNotContains($cell0, $cellsInput);
        $this->assertNotContains($cell0, $cellsEdit);
        $this->assertNotContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cellEuropeA, $cellsView);
        $this->assertNotContains($cellEuropeA, $cellsInput);
        $this->assertNotContains($cellEuropeA, $cellsEdit);
        $this->assertNotContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cellEuropeB, $cellsView);
        $this->assertNotContains($cellEuropeB, $cellsInput);
        $this->assertNotContains($cellEuropeB, $cellsEdit);
        $this->assertNotContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cellSudameriqueA, $cellsView);
        $this->assertNotContains($cellSudameriqueA, $cellsInput);
        $this->assertNotContains($cellSudameriqueA, $cellsEdit);
        $this->assertNotContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cellSudameriqueB, $cellsView);
        $this->assertNotContains($cellSudameriqueB, $cellsInput);
        $this->assertNotContains($cellSudameriqueB, $cellsEdit);
        $this->assertNotContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertNotContains($cellAnnecy, $cellsView);
        $this->assertNotContains($cellAnnecy, $cellsInput);
        $this->assertNotContains($cellAnnecy, $cellsEdit);
        $this->assertNotContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertNotContains($cellChambery, $cellsView);
        $this->assertNotContains($cellChambery, $cellsInput);
        $this->assertNotContains($cellChambery, $cellsEdit);
        $this->assertNotContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertContains($cellBerlin, $cellsView);
        $this->assertNotContains($cellBerlin, $cellsInput);
        $this->assertNotContains($cellBerlin, $cellsEdit);
        $this->assertNotContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertNotContains($cellLima, $cellsView);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cellLima, $cellsEdit);
        $this->assertNotContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertNotContains($cell2012, $cellsView);
        $this->assertNotContains($cell2012, $cellsInput);
        $this->assertNotContains($cell2012, $cellsEdit);
        $this->assertNotContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertNotContains($cell2013, $cellsView);
        $this->assertNotContains($cell2013, $cellsInput);
        $this->assertNotContains($cell2013, $cellsEdit);
        $this->assertNotContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012Energie, $cellsView);
        $this->assertNotContains($cell2012Energie, $cellsInput);
        $this->assertNotContains($cell2012Energie, $cellsEdit);
        $this->assertNotContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012Transport, $cellsView);
        $this->assertNotContains($cell2012Transport, $cellsInput);
        $this->assertNotContains($cell2012Transport, $cellsEdit);
        $this->assertNotContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013Energie, $cellsView);
        $this->assertNotContains($cell2013Energie, $cellsInput);
        $this->assertNotContains($cell2013Energie, $cellsEdit);
        $this->assertNotContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013Transport, $cellsView);
        $this->assertNotContains($cell2013Transport, $cellsInput);
        $this->assertNotContains($cell2013Transport, $cellsEdit);
        $this->assertNotContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2012EuropeA, $cellsView);
        $this->assertNotContains($cell2012EuropeA, $cellsInput);
        $this->assertNotContains($cell2012EuropeA, $cellsEdit);
        $this->assertNotContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2012EuropeB, $cellsView);
        $this->assertNotContains($cell2012EuropeB, $cellsInput);
        $this->assertNotContains($cell2012EuropeB, $cellsEdit);
        $this->assertNotContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2012SudameriqueA, $cellsView);
        $this->assertNotContains($cell2012SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2012SudameriqueB, $cellsView);
        $this->assertNotContains($cell2012SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2013EuropeA, $cellsView);
        $this->assertNotContains($cell2013EuropeA, $cellsInput);
        $this->assertNotContains($cell2013EuropeA, $cellsEdit);
        $this->assertNotContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2013EuropeB, $cellsView);
        $this->assertNotContains($cell2013EuropeB, $cellsInput);
        $this->assertNotContains($cell2013EuropeB, $cellsEdit);
        $this->assertNotContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2013SudameriqueA, $cellsView);
        $this->assertNotContains($cell2013SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2013SudameriqueB, $cellsView);
        $this->assertNotContains($cell2013SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertNotContains($cell2012Annecy, $cellsView);
        $this->assertNotContains($cell2012Annecy, $cellsInput);
        $this->assertNotContains($cell2012Annecy, $cellsEdit);
        $this->assertNotContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertNotContains($cell2012Chambery, $cellsView);
        $this->assertNotContains($cell2012Chambery, $cellsInput);
        $this->assertNotContains($cell2012Chambery, $cellsEdit);
        $this->assertNotContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertContains($cell2012Berlin, $cellsView);
        $this->assertNotContains($cell2012Berlin, $cellsInput);
        $this->assertNotContains($cell2012Berlin, $cellsEdit);
        $this->assertNotContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertNotContains($cell2012Lima, $cellsView);
        $this->assertNotContains($cell2012Lima, $cellsInput);
        $this->assertNotContains($cell2012Lima, $cellsEdit);
        $this->assertNotContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertNotContains($cell2013Annecy, $cellsView);
        $this->assertNotContains($cell2013Annecy, $cellsInput);
        $this->assertNotContains($cell2013Annecy, $cellsEdit);
        $this->assertNotContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertNotContains($cell2013Chambery, $cellsView);
        $this->assertNotContains($cell2013Chambery, $cellsInput);
        $this->assertNotContains($cell2013Chambery, $cellsEdit);
        $this->assertNotContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertContains($cell2013Berlin, $cellsView);
        $this->assertNotContains($cell2013Berlin, $cellsInput);
        $this->assertNotContains($cell2013Berlin, $cellsEdit);
        $this->assertNotContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertNotContains($cell2013Lima, $cellsView);
        $this->assertNotContains($cell2013Lima, $cellsInput);
        $this->assertNotContains($cell2013Lima, $cellsEdit);
        $this->assertNotContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012LimaEnergie, $cellsView);
        $this->assertNotContains($cell2012LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013LimaEnergie, $cellsView);
        $this->assertNotContains($cell2013LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsView);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertContains($cell2012BerlinTransport, $cellsView);
        $this->assertNotContains($cell2012BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012LimaTransport, $cellsView);
        $this->assertNotContains($cell2012LimaTransport, $cellsInput);
        $this->assertNotContains($cell2012LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsView);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertContains($cell2013BerlinTransport, $cellsView);
        $this->assertNotContains($cell2013BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013LimaTransport, $cellsView);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cell2013LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2013LimaTransport, $cellsAllow);
    }

    /**
     * Test le point du vue (effectif) du contributeur de la cellule lima.
     */
    public function tACLFilterLimaCellContributor()
    {
        $user = $this->limaCellContributor;

        // Query des différentes actions.
        $queryView = new Core_Model_Query();
        $queryView->aclFilter->enabled = true;
        $queryView->aclFilter->user = $user;
        $queryView->aclFilter->action = Actions::VIEW;
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = Actions::EDIT;
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = Actions::DELETE;
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = CellAction::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = Actions::ALLOW;

        // Test toutes les ressources.

        // Organisation.
        $organisationsView = Orga_Model_Organization::loadList($queryView);
        $this->assertCount(1, $organisationsView);
        $this->assertContains($this->organization, $organisationsView);
        $organisationsEdit = Orga_Model_Organization::loadList($queryEdit);
        $this->assertCount(0, $organisationsEdit);
        $this->assertNotContains($this->organization, $organisationsEdit);
        $organisationsDelete = Orga_Model_Organization::loadList($queryDelete);
        $this->assertCount(0, $organisationsDelete);
        $this->assertNotContains($this->organization, $organisationsDelete);

        $cellsView = Orga_Model_Cell::loadList($queryView);
        $this->assertCount(7, $cellsView);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(7, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(0, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(0, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertNotContains($cell0, $cellsView);
        $this->assertNotContains($cell0, $cellsInput);
        $this->assertNotContains($cell0, $cellsEdit);
        $this->assertNotContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cellEuropeA, $cellsView);
        $this->assertNotContains($cellEuropeA, $cellsInput);
        $this->assertNotContains($cellEuropeA, $cellsEdit);
        $this->assertNotContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cellEuropeB, $cellsView);
        $this->assertNotContains($cellEuropeB, $cellsInput);
        $this->assertNotContains($cellEuropeB, $cellsEdit);
        $this->assertNotContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cellSudameriqueA, $cellsView);
        $this->assertNotContains($cellSudameriqueA, $cellsInput);
        $this->assertNotContains($cellSudameriqueA, $cellsEdit);
        $this->assertNotContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cellSudameriqueB, $cellsView);
        $this->assertNotContains($cellSudameriqueB, $cellsInput);
        $this->assertNotContains($cellSudameriqueB, $cellsEdit);
        $this->assertNotContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertNotContains($cellAnnecy, $cellsView);
        $this->assertNotContains($cellAnnecy, $cellsInput);
        $this->assertNotContains($cellAnnecy, $cellsEdit);
        $this->assertNotContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertNotContains($cellChambery, $cellsView);
        $this->assertNotContains($cellChambery, $cellsInput);
        $this->assertNotContains($cellChambery, $cellsEdit);
        $this->assertNotContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertNotContains($cellBerlin, $cellsView);
        $this->assertNotContains($cellBerlin, $cellsInput);
        $this->assertNotContains($cellBerlin, $cellsEdit);
        $this->assertNotContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertContains($cellLima, $cellsView);
        $this->assertContains($cellLima, $cellsInput);
        $this->assertNotContains($cellLima, $cellsEdit);
        $this->assertNotContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertNotContains($cell2012, $cellsView);
        $this->assertNotContains($cell2012, $cellsInput);
        $this->assertNotContains($cell2012, $cellsEdit);
        $this->assertNotContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertNotContains($cell2013, $cellsView);
        $this->assertNotContains($cell2013, $cellsInput);
        $this->assertNotContains($cell2013, $cellsEdit);
        $this->assertNotContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012Energie, $cellsView);
        $this->assertNotContains($cell2012Energie, $cellsInput);
        $this->assertNotContains($cell2012Energie, $cellsEdit);
        $this->assertNotContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012Transport, $cellsView);
        $this->assertNotContains($cell2012Transport, $cellsInput);
        $this->assertNotContains($cell2012Transport, $cellsEdit);
        $this->assertNotContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013Energie, $cellsView);
        $this->assertNotContains($cell2013Energie, $cellsInput);
        $this->assertNotContains($cell2013Energie, $cellsEdit);
        $this->assertNotContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013Transport, $cellsView);
        $this->assertNotContains($cell2013Transport, $cellsInput);
        $this->assertNotContains($cell2013Transport, $cellsEdit);
        $this->assertNotContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2012EuropeA, $cellsView);
        $this->assertNotContains($cell2012EuropeA, $cellsInput);
        $this->assertNotContains($cell2012EuropeA, $cellsEdit);
        $this->assertNotContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2012EuropeB, $cellsView);
        $this->assertNotContains($cell2012EuropeB, $cellsInput);
        $this->assertNotContains($cell2012EuropeB, $cellsEdit);
        $this->assertNotContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2012SudameriqueA, $cellsView);
        $this->assertNotContains($cell2012SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2012SudameriqueB, $cellsView);
        $this->assertNotContains($cell2012SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2013EuropeA, $cellsView);
        $this->assertNotContains($cell2013EuropeA, $cellsInput);
        $this->assertNotContains($cell2013EuropeA, $cellsEdit);
        $this->assertNotContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2013EuropeB, $cellsView);
        $this->assertNotContains($cell2013EuropeB, $cellsInput);
        $this->assertNotContains($cell2013EuropeB, $cellsEdit);
        $this->assertNotContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2013SudameriqueA, $cellsView);
        $this->assertNotContains($cell2013SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2013SudameriqueB, $cellsView);
        $this->assertNotContains($cell2013SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertNotContains($cell2012Annecy, $cellsView);
        $this->assertNotContains($cell2012Annecy, $cellsInput);
        $this->assertNotContains($cell2012Annecy, $cellsEdit);
        $this->assertNotContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertNotContains($cell2012Chambery, $cellsView);
        $this->assertNotContains($cell2012Chambery, $cellsInput);
        $this->assertNotContains($cell2012Chambery, $cellsEdit);
        $this->assertNotContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2012Berlin, $cellsView);
        $this->assertNotContains($cell2012Berlin, $cellsInput);
        $this->assertNotContains($cell2012Berlin, $cellsEdit);
        $this->assertNotContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertContains($cell2012Lima, $cellsView);
        $this->assertContains($cell2012Lima, $cellsInput);
        $this->assertNotContains($cell2012Lima, $cellsEdit);
        $this->assertNotContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertNotContains($cell2013Annecy, $cellsView);
        $this->assertNotContains($cell2013Annecy, $cellsInput);
        $this->assertNotContains($cell2013Annecy, $cellsEdit);
        $this->assertNotContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertNotContains($cell2013Chambery, $cellsView);
        $this->assertNotContains($cell2013Chambery, $cellsInput);
        $this->assertNotContains($cell2013Chambery, $cellsEdit);
        $this->assertNotContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2013Berlin, $cellsView);
        $this->assertNotContains($cell2013Berlin, $cellsInput);
        $this->assertNotContains($cell2013Berlin, $cellsEdit);
        $this->assertNotContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertContains($cell2013Lima, $cellsView);
        $this->assertContains($cell2013Lima, $cellsInput);
        $this->assertNotContains($cell2013Lima, $cellsEdit);
        $this->assertNotContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012LimaEnergie, $cellsView);
        $this->assertContains($cell2012LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013LimaEnergie, $cellsView);
        $this->assertContains($cell2013LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsView);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012BerlinTransport, $cellsView);
        $this->assertNotContains($cell2012BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2012LimaTransport, $cellsView);
        $this->assertContains($cell2012LimaTransport, $cellsInput);
        $this->assertNotContains($cell2012LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsView);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013BerlinTransport, $cellsView);
        $this->assertNotContains($cell2013BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2013LimaTransport, $cellsView);
        $this->assertContains($cellLima, $cellsInput);
        $this->assertNotContains($cell2013LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2013LimaTransport, $cellsAllow);
    }

    /**
     *
     */
    protected function tearDown()
    {
        parent::tearDown();

        if (! $this->organizationAdministrator) {
            // Erreur dans le set up ?
            return;
        }

        $this->userService->deleteUser(User::load($this->organizationAdministrator->getId()));
        $this->userService->deleteUser(User::load($this->globaleCellAdministrator->getId()));
        $this->userService->deleteUser(User::load($this->europeaCellContributor->getId()));
        $this->userService->deleteUser(User::load($this->sudameriquebCellObserver->getId()));
        $this->userService->deleteUser(User::load($this->annecyCellAdministrator->getId()));
        $this->userService->deleteUser(User::load($this->berlinCellObserver->getId()));
        $this->userService->deleteUser(User::load($this->limaCellContributor->getId()));

        $this->entityManager->flush();
        $this->entityManager->clear();

        foreach (Orga_Model_Organization::load($this->organization->getId())->getOrderedGranularities() as $granularity) {
            $granularity->delete();
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        Orga_Model_Organization::load($this->organization->getId())->delete();

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function assertAllowed(User $user, $action, ResourceInterface $resource)
    {
        $this->assertTrue($this->aclManager->isAllowed($user, $action, $resource));
    }

    public function assertNotAllowed(User $user, $action, ResourceInterface $resource)
    {
        $this->assertFalse($this->aclManager->isAllowed($user, $action, $resource));
    }

    public function assertAdminOrganization(User $user, Orga_Model_Organization $organization)
    {
        $this->assertAllowed($user, Actions::VIEW, $organization);
        $this->assertAllowed($user, Actions::EDIT, $organization);
        $this->assertAllowed($user, Actions::DELETE, $organization);
        $this->assertNotAllowed($user, Actions::UNDELETE, $organization);
        $this->assertAllowed($user, Actions::ALLOW, $organization);
    }

    public function assertAdminCell(User $user, Orga_Model_Cell $cell)
    {
        $this->assertAllowed($user, Actions::VIEW, $cell);
        $this->assertAllowed($user, Actions::EDIT, $cell);
        $this->assertAllowed($user, Actions::ALLOW, $cell);

        $input = $cell->getAFInputSetPrimary();
        if ($input) {
            $this->assertAllowed($user, Actions::VIEW, $input);
            $this->assertAllowed($user, Actions::EDIT, $input);
        }
    }

    public function assertContributeCell(User $user, Orga_Model_Cell $cell)
    {
        $this->assertAllowed($user, Actions::VIEW, $cell);
        $this->assertNotAllowed($user, Actions::EDIT, $cell);
        $this->assertNotAllowed($user, Actions::ALLOW, $cell);

        $input = $cell->getAFInputSetPrimary();
        if ($input) {
            $this->assertAllowed($user, Actions::VIEW, $input);
            $this->assertAllowed($user, Actions::EDIT, $input);
        }
    }
}
