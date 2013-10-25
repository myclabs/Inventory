<?php
use User\Domain\ACL\Action\DefaultAction;
use User\Domain\ACL\ACLService;
use User\Domain\User;
use User\Domain\UserService;

/**
 * Class Orga_Test_ACLTest
 * @author valentin.claras
 * @package    Orga
 * @subpackage Test
 */


/**
 * Creation de la suite de test sur les ACL.
 * @package    Orga
 * @subpackage Test
 */
class Orga_Test_ACLTest
{
    /**
     * Creation de la suite de test
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Orga_Test_ACL');
        return $suite;
    }

}

/**
 * Test des ACL ser l'organization.
 * @package Orga
 * @subpackage Test
 */
class Orga_Test_ACL extends Core_Test_TestCase
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var ACLService
     */
    protected $aclService;

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


    /**
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $entityManagers['default'];

        /** @var \DI\Container $container */
        $container = Zend_Registry::get('container');

        /** @var Orga_Service_ACLManager $aclManagerService */
        $aclManagerService = $container->get('Orga_Service_ACLManager');
        $entityManager->getEventManager()->addEventListener(
            [Doctrine\ORM\Events::onFlush, Doctrine\ORM\Events::postFlush],
            $aclManagerService
        );
        /** @var Core_EventDispatcher $eventDispatcher */
        $eventDispatcher = $container->get('Core_EventDispatcher');
        $eventDispatcher->addListener('Orga_Model_GranularityReport', 'DW_Model_Report');

        // Vérification qu'il ne reste aucun User en base, sinon suppression !
        if (User::countTotal() > 0) {
            echo PHP_EOL . 'Des User_User restants ont été trouvé avant les tests, suppression en cours !';
            foreach (User::loadList() as $user) {
                $user->delete();
            }
            $entityManager->flush();
        }

        // Vérification qu'il ne reste aucun Orga_Model_Cell en base, sinon suppression !
        if (Orga_Model_Cell::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Cell restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Cell::loadList() as $cell) {
                $cell->delete();
            }
            $entityManager->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Granularity en base, sinon suppression !
        if (Orga_Model_Granularity::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Granularity restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Granularity::loadList() as $granularity) {
                $granularity->delete();
            }
            $entityManager->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Member en base, sinon suppression !
        if (Orga_Model_Member::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Member restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Member::loadList() as $member) {
                $member->delete();
            }
            $entityManager->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Axis en base, sinon suppression !
        if (Orga_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Axis restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            $entityManager->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Organization en base, sinon suppression !
        if (Orga_Model_Organization::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Organization restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Organization::loadList() as $organization) {
                $organization->delete();
            }
            $entityManager->flush();
        }
    }

    /**
     * Fonction appelee avant chaque test
     */
    public function setUp()
    {
        parent::setUp();

        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $entityManagers['default'];

        /** @var \DI\Container $container */
        $container = Zend_Registry::get('container');

        /** @var Orga_Service_ACLManager $aclManagerService */
        $aclManagerService = $container->get(Orga_Service_ACLManager::class);
        $this->userService = $container->get(UserService::class);
        $this->aclService = $container->get(ACLService::class);


        // Création de l'organization (proche de populateTest au 08/08/2013).
        $this->organization = new Orga_Model_Organization();
        $this->organization->setLabel('ACL Test');

        $entityManager->flush();

        // Création d'un ensemble d'axes.

        // Année.
        $this->axisAnnee = new Orga_Model_Axis($this->organization);
        $this->axisAnnee->setRef('annee');
        $this->axisAnnee->setLabel('Année');

        // Site.
        $this->axisSite = new Orga_Model_Axis($this->organization);
        $this->axisSite->setRef('site');
        $this->axisSite->setLabel('Site');

        // Pays.
        $this->axisPays = new Orga_Model_Axis($this->organization);
        $this->axisPays->setRef('pays');
        $this->axisPays->setLabel('Pays');
        $this->axisPays->setDirectNarrower($this->axisSite);

        // Zone.
        $this->axisZone = new Orga_Model_Axis($this->organization);
        $this->axisZone->setRef('zone');
        $this->axisZone->setLabel('Zone');
        $this->axisZone->setDirectNarrower($this->axisPays);

        // Marque.
        $this->axisMarque = new Orga_Model_Axis($this->organization);
        $this->axisMarque->setRef('marque');
        $this->axisMarque->setLabel('Marque');
        $this->axisMarque->setDirectNarrower($this->axisSite);

        // Catégories.
        $this->axisCategorie = new Orga_Model_Axis($this->organization);
        $this->axisCategorie->setRef('categorie');
        $this->axisCategorie->setLabel('Catégorie');

        // Création des membres des axes.

        // Années.
        $this->memberAnnee2012 = new Orga_Model_Member($this->axisAnnee);
        $this->memberAnnee2012->setRef('2012');
        $this->memberAnnee2012->setLabel('2012');
        $this->memberAnnee2013 = new Orga_Model_Member($this->axisAnnee);
        $this->memberAnnee2013->setRef('2013');
        $this->memberAnnee2013->setLabel('2013');

        // Zones.
        $this->memberZoneEurope = new Orga_Model_Member($this->axisZone);
        $this->memberZoneEurope->setRef('europe');
        $this->memberZoneEurope->setLabel('Europe');
        $this->memberZoneSudamerique = new Orga_Model_Member($this->axisZone);
        $this->memberZoneSudamerique->setRef('sudamerique');
        $this->memberZoneSudamerique->setLabel('Amerique du Sud');

        // Pays.
        $this->memberPaysFrance = new Orga_Model_Member($this->axisPays);
        $this->memberPaysFrance->setRef('france');
        $this->memberPaysFrance->setLabel('France');
        $this->memberPaysFrance->addDirectParent($this->memberZoneEurope);
        $this->memberPaysAllemagne = new Orga_Model_Member($this->axisPays);
        $this->memberPaysAllemagne->setRef('allemagne');
        $this->memberPaysAllemagne->setLabel('Allemagne');
        $this->memberPaysAllemagne->addDirectParent($this->memberZoneEurope);
        $this->memberPaysPerou = new Orga_Model_Member($this->axisPays);
        $this->memberPaysPerou->setRef('perou');
        $this->memberPaysPerou->setLabel('Pérou');
        $this->memberPaysPerou->addDirectParent($this->memberZoneSudamerique);

        // Marques.
        $this->memberMarqueA = new Orga_Model_Member($this->axisMarque);
        $this->memberMarqueA->setRef('a');
        $this->memberMarqueA->setLabel('A');
        $this->memberMarqueB = new Orga_Model_Member($this->axisMarque);
        $this->memberMarqueB->setRef('b');
        $this->memberMarqueB->setLabel('B');

        // Sites.
        $this->memberSiteAnnecy = new Orga_Model_Member($this->axisSite);
        $this->memberSiteAnnecy->setRef('annecy');
        $this->memberSiteAnnecy->setLabel('Annecy');
        $this->memberSiteAnnecy->addDirectParent($this->memberPaysFrance);
        $this->memberSiteAnnecy->addDirectParent($this->memberMarqueA);
        $this->memberSiteChambery = new Orga_Model_Member($this->axisSite);
        $this->memberSiteChambery->setRef('chambery');
        $this->memberSiteChambery->setLabel('Chambery');
        $this->memberSiteChambery->addDirectParent($this->memberPaysFrance);
        $this->memberSiteChambery->addDirectParent($this->memberMarqueA);
        $this->memberSiteBerlin = new Orga_Model_Member($this->axisSite);
        $this->memberSiteBerlin->setRef('berlin');
        $this->memberSiteBerlin->setLabel('Berlin');
        $this->memberSiteBerlin->addDirectParent($this->memberPaysAllemagne);
        $this->memberSiteBerlin->addDirectParent($this->memberMarqueB);
        $this->memberSiteLima = new Orga_Model_Member($this->axisSite);
        $this->memberSiteLima->setRef('lima');
        $this->memberSiteLima->setLabel('Lima');
        $this->memberSiteLima->addDirectParent($this->memberPaysPerou);
        $this->memberSiteLima->addDirectParent($this->memberMarqueB);

        // Catégories.
        $this->memberCategorieEnergie = new Orga_Model_Member($this->axisCategorie);
        $this->memberCategorieEnergie->setRef('energie');
        $this->memberCategorieEnergie->setLabel('Énergie');
        $this->memberCategorieTransport = new Orga_Model_Member($this->axisCategorie);
        $this->memberCategorieTransport->setRef('transport');
        $this->memberCategorieTransport->setLabel('Transport');

        // Création des granularités de l'organisation.

        // Création de la granularité globale.
        $this->granularityGlobale = new Orga_Model_Granularity($this->organization);
        $this->granularityGlobale->setNavigability(true);
        $this->granularityGlobale->setCellsWithOrgaTab(true);
        $this->granularityGlobale->setCellsWithACL(true);
        $this->granularityGlobale->setCellsWithAFConfigTab(true);

        // Création de la granularité zone marque.
        $this->granularityZoneMarque = new Orga_Model_Granularity($this->organization, [$this->axisZone, $this->axisMarque]);
        $this->granularityZoneMarque->setNavigability(true);
        $this->granularityZoneMarque->setCellsWithOrgaTab(true);
        $this->granularityZoneMarque->setCellsWithACL(true);

        // Création de la granularité site.
        $this->granularitySite = new Orga_Model_Granularity($this->organization, [$this->axisSite]);
        $this->granularitySite->setNavigability(true);
        $this->granularitySite->setCellsWithACL(true);

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
        $entityManager->flush();

        // Ajout d'utilisateurs.

        // Ajout d'un utilisateur administrateur de l'administration.
        $this->organizationAdministrator= $this->userService->createUser('organizationAdministrator', 'organizationAdministrator');
        $aclManagerService->addOrganizationAdministrator($this->organization, $this->organizationAdministrator, false);

        // Ajout d'un administrateur de cellule globale.
        $this->globaleCellAdministrator = $this->userService->createUser('globalAdministrator', 'globalAdministrator');
        $aclManagerService->addCellAdministrator(
            $this->granularityGlobale->getCellByMembers([]), $this->globaleCellAdministrator, false
        );

        // Ajout d'un contributeur de cellule zone marque.
        $this->europeaCellContributor = $this->userService->createUser('europeaContributor', 'europeaContributor');
        $aclManagerService->addCellContributor(
            $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]), $this->europeaCellContributor, 'contributor'
        );

        // Ajout d'un observatur de cellule zone marque.
        $this->sudameriquebCellObserver = $this->userService->createUser('sudameriquebObserver', 'sudameriquebObserver');
        $aclManagerService->addCellObserver(
            $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]), $this->sudameriquebCellObserver, 'observer'
        );

        // Ajout d'un administrateur de cellule site.
        $this->annecyCellAdministrator = $this->userService->createUser('annecyAdministrator', 'annecyAdministrator');
        $aclManagerService->addCellAdministrator(
            $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]), $this->annecyCellAdministrator, 'administrator'
        );

        // Ajout d'un contributeur de cellule site.
        $this->limaCellContributor = $this->userService->createUser('limaContributor', 'limaContributor');
        $aclManagerService->addCellContributor(
            $this->granularitySite->getCellByMembers([$this->memberSiteLima]), $this->limaCellContributor, 'contributor'
        );

        // Ajout d'un observateur de cellule site.
        $this->berlinCellObserver = $this->userService->createUser('berlinObserver', 'berlinObserver');
        $aclManagerService->addCellObserver(
            $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]), $this->berlinCellObserver, 'observer'
        );

        $entityManager->flush();

        // Ajout des rapports préconfigurés.

        $this->granularityGlobale->setCellsGenerateDWCubes(true);
        $this->granularityZoneMarque->setCellsGenerateDWCubes(true);
        $this->granularitySite->setCellsGenerateDWCubes(true);

        $this->organization->save();

        $entityManager->flush();

        $reportGlobale = new DW_Model_Report($this->granularityGlobale->getDWCube());
        $reportGlobale->setLabel('Test Globale');
        $reportGlobale->save();

        $reportZoneMarque = new DW_Model_Report($this->granularityZoneMarque->getDWCube());
        $reportZoneMarque->setLabel('Test Zone Marque');
        $reportZoneMarque->save();

        $reportSite = new DW_Model_Report($this->granularitySite->getDWCube());
        $reportSite->setLabel('Test Site');
        $reportSite->save();

        $entityManager->flush();
    }

    /**
     * Test les points du vue formel (IsAllow) des utilisateurs.
     *  Désactivé pour soulager le test.
     */
    public function testUsersIsAllowed()
    {
        $this->tIsAllowOrganizationAdministrator();
        $this->tIsAllowGlobaleCellAdministrator();
        $this->tIsAllowEuropeACellContributor();
        $this->tIsAllowSudameriqueBCellObserver();
        $this->tIsAllowAnnecyCellAdministrator();
        $this->tIsAllowBerlinCellObserver();
        $this->tIsAllowLimaCellContributor();
    }

    /**
     * Test le point du vue (formel) de l'administrateur de l'organisation.
     */
    public function tIsAllowOrganizationAdministrator()
    {
        $user = $this->organizationAdministrator;

        // Test toutes les ressources.

        // Organisation.
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $this->organization));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $this->organization));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::DELETE(), $this->organization));

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell0));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell0));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell0));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell0));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell0));

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeA));
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeB));
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueA));
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueB));

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellAnnecy));
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellChambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellChambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellChambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellChambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellChambery));
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellBerlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellBerlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellBerlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellBerlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellBerlin));
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellLima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellLima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellLima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellLima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellLima));

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012));
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013));

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Energie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Energie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Energie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Energie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Energie));
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Transport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Transport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Transport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Transport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Transport));
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Energie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Energie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Energie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Energie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Energie));
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Transport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Transport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Transport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Transport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Transport));

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeA));
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeB));
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueA));
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueB));
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeA));
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeB));
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueA));
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueB));

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Annecy));
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Chambery));
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Berlin));
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Lima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Lima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Lima));
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Annecy));
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Chambery));
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Berlin));
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Lima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Lima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Lima));

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyEnergie));
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyEnergie));
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinEnergie));
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaEnergie));
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyEnergie));
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyEnergie));
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinEnergie));
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaEnergie));
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyTransport));
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyTransport));
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinTransport));
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaTransport));
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyTransport));
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyTransport));
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinTransport));
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaTransport));

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportGlobale));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportGlobale));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportGlobale));

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportCellGlobale));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportCellGlobale));

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportZoneMarque));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportZoneMarque));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportZoneMarque));

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeA));
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeB));
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueA));
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueB));

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSite));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSite));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSite));

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportAnnecy));
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportChambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportChambery));
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportBerlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportBerlin));
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportLima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportLima));
    }

    /**
     * Test le point du vue (formel) de l'administrateur de la cellule globale.
     */
    public function tIsAllowGlobaleCellAdministrator()
    {
        $user = $this->globaleCellAdministrator;

        // Test toutes les ressources.

        // Organisation.
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $this->organization));

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell0));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell0));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell0));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell0));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell0));

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeA));
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeB));
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueA));
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueB));

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellAnnecy));
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellChambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellChambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellChambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellChambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellChambery));
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellBerlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellBerlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellBerlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellBerlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellBerlin));
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellLima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellLima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellLima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellLima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellLima));

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012));
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013));

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Energie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Energie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Energie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Energie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Energie));
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Transport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Transport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Transport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Transport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Transport));
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Energie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Energie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Energie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Energie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Energie));
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Transport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Transport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Transport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Transport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Transport));

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeA));
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeB));
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueA));
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueB));
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeA));
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeB));
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueA));
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueB));

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Annecy));
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Chambery));
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Berlin));
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Lima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Lima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Lima));
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Annecy));
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Chambery));
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Berlin));
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Lima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Lima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Lima));

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyEnergie));
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyEnergie));
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinEnergie));
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaEnergie));
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyEnergie));
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyEnergie));
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinEnergie));
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaEnergie));
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyTransport));
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyTransport));
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinTransport));
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaTransport));
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyTransport));
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyTransport));
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinTransport));
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaTransport));

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportGlobale));

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportCellGlobale));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportCellGlobale));

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportZoneMarque));

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeA));
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeB));
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueA));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueA));
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueB));

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSite));

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportAnnecy));
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportChambery));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportChambery));
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportBerlin));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportBerlin));
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportLima));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportLima));
    }

    /**
     * Test le point du vue (formel) du contributeur de la cellule europe a.
     */
    public function tIsAllowEuropeACellContributor()
    {
        $user = $this->europeaCellContributor;

        // Test toutes les ressources.

        // Organisation.
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $this->organization));

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell0));

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeA));
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeB));
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueA));
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueB));

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellAnnecy));
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellChambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellChambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellChambery));
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellBerlin));
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellLima));

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012));
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013));

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Energie));
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Transport));
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Energie));
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Transport));

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeA));
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeB));
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueA));
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueB));
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeA));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeA));
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeB));
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueA));
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueB));

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Annecy));
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Chambery));
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Berlin));
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Lima));
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Annecy));
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Chambery));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Chambery));
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Berlin));
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Lima));

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyEnergie));
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyEnergie));
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinEnergie));
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaEnergie));
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyEnergie));
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyEnergie));
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinEnergie));
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaEnergie));
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyTransport));
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyTransport));
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinTransport));
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaTransport));
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyTransport));
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyTransport));
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinTransport));
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaTransport));

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportGlobale));

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportCellGlobale));

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportZoneMarque));

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeA));
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeB));
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueA));
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueB));

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSite));

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportAnnecy));
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportChambery));
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportBerlin));
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportLima));
    }

    /**
     * Test le point du vue (formel) de l'observateur de la cellule sudamerique b.
     */
    public function tIsAllowSudameriqueBCellObserver()
    {
        $user = $this->sudameriquebCellObserver;

        // Test toutes les ressources.

        // Organisation.
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $this->organization));

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell0));

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeA));
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeB));
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueA));
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueB));

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellAnnecy));
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellChambery));
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellBerlin));
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellLima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellLima));

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012));
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013));

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Energie));
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Transport));
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Energie));
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Transport));

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeA));
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeB));
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueA));
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueB));
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeA));
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeB));
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueA));
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueB));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueB));

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Annecy));
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Chambery));
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Berlin));
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Lima));
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Annecy));
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Chambery));
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Berlin));
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Lima));

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyEnergie));
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyEnergie));
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinEnergie));
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaEnergie));
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyEnergie));
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyEnergie));
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinEnergie));
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaEnergie));
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyTransport));
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyTransport));
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinTransport));
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaTransport));
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyTransport));
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyTransport));
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinTransport));
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaTransport));

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportGlobale));

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportCellGlobale));

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportZoneMarque));

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeA));
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeB));
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueA));
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueB));

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSite));

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportAnnecy));
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportChambery));
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportBerlin));
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportLima));
    }

    /**
     * Test le point du vue (formel) de l'administrateur de la cellule annecy.
     */
    public function tIsAllowAnnecyCellAdministrator()
    {
        $user = $this->annecyCellAdministrator;

        // Test toutes les ressources.

        // Organisation.
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $this->organization));

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell0));

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeA));
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeB));
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueA));
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueB));

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellAnnecy));
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellChambery));
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellBerlin));
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellLima));

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012));
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013));

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Energie));
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Transport));
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Energie));
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Transport));

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeA));
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeB));
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueA));
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueB));
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeA));
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeB));
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueA));
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueB));

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Annecy));
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Chambery));
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Berlin));
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Lima));
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Annecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Annecy));
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Chambery));
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Berlin));
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Lima));

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyEnergie));
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyEnergie));
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinEnergie));
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaEnergie));
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyEnergie));
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyEnergie));
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinEnergie));
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaEnergie));
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyTransport));
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyTransport));
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinTransport));
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaTransport));
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyTransport));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyTransport));
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyTransport));
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinTransport));
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaTransport));

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportGlobale));

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportCellGlobale));

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportZoneMarque));

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeA));
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeB));
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueA));
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueB));

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSite));

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportAnnecy));
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportAnnecy));
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportChambery));
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportBerlin));
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportLima));
    }

    /**
     * Test le point du vue (formel) de l'observateur de la cellule berlin.
     */
    public function tIsAllowBerlinCellObserver()
    {
        $user = $this->berlinCellObserver;

        // Test toutes les ressources.

        // Organisation.
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $this->organization));

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell0));

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeA));
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeB));
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueA));
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueB));

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellAnnecy));
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellChambery));
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellBerlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellBerlin));
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellLima));

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012));
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013));

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Energie));
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Transport));
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Energie));
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Transport));

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeA));
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeB));
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueA));
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueB));
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeA));
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeB));
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueA));
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueB));

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Annecy));
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Chambery));
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Berlin));
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Lima));
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Annecy));
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Chambery));
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Berlin));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Berlin));
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Lima));

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyEnergie));
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyEnergie));
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinEnergie));
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaEnergie));
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyEnergie));
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyEnergie));
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinEnergie));
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaEnergie));
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyTransport));
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyTransport));
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinTransport));
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaTransport));
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyTransport));
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyTransport));
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinTransport));
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaTransport));

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportGlobale));

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportCellGlobale));

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportZoneMarque));

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeA));
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeB));
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueA));
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueB));

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSite));

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportAnnecy));
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportChambery));
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportBerlin));
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportLima));
    }

    /**
     * Test le point du vue (formel) du contributeur de la cellule lima.
     */
    public function tIsAllowLimaCellContributor()
    {
        $user = $this->limaCellContributor;

        // Test toutes les ressources.

        // Organisation.
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $this->organization));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $this->organization));

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell0));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell0));

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeA));
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellEuropeB));
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueA));
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellSudameriqueB));

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellAnnecy));
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellChambery));
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellBerlin));
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cellLima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cellLima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cellLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cellLima));

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012));
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013));

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Energie));
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Transport));
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Energie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Energie));
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Transport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Transport));

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeA));
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012EuropeB));
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueA));
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012SudameriqueB));
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeA));
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013EuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013EuropeB));
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueA));
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013SudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013SudameriqueB));

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Annecy));
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Chambery));
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Berlin));
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012Lima));
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Annecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Annecy));
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Chambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Chambery));
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Berlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Berlin));
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013Lima));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013Lima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013Lima));

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyEnergie));
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyEnergie));
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinEnergie));
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaEnergie));
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyEnergie));
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyEnergie));
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinEnergie));
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaEnergie));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaEnergie));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaEnergie));
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012AnnecyTransport));
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012ChamberyTransport));
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012BerlinTransport));
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2012LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2012LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2012LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2012LimaTransport));
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013AnnecyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013AnnecyTransport));
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013ChamberyTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013ChamberyTransport));
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013BerlinTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013BerlinTransport));
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $cell2013LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::COMMENT(), $cell2013LimaTransport));
        $this->assertTrue($this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $cell2013LimaTransport));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::ALLOW(), $cell2013LimaTransport));

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportGlobale));

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportCellGlobale));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportCellGlobale));

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportZoneMarque));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportZoneMarque));

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeA));
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportEuropeB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportEuropeB));
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueA));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueA));
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSudameriqueB));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSudameriqueB));

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportSite));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportSite));

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportAnnecy));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportAnnecy));
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportChambery));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportChambery));
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportBerlin));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportBerlin));
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertTrue($this->aclService->isAllowed($user, DefaultAction::VIEW(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, Orga_Action_Report::EDIT(), $reportLima));
        $this->assertFalse($this->aclService->isAllowed($user, DefaultAction::DELETE(), $reportLima));
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
        $queryView->aclFilter->action = DefaultAction::VIEW();
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = DefaultAction::EDIT();
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = DefaultAction::DELETE();
        $queryComment = new Core_Model_Query();
        $queryComment->aclFilter->enabled = true;
        $queryComment->aclFilter->user = $user;
        $queryComment->aclFilter->action = Orga_Action_Cell::COMMENT();
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = Orga_Action_Cell::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = DefaultAction::ALLOW();
        $queryReport = new Core_Model_Query();
        $queryReport->aclFilter->enabled = true;
        $queryReport->aclFilter->user = $user;
        $queryReport->aclFilter->action = Orga_Action_Report::EDIT();

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
        $cellsComment = Orga_Model_Cell::loadList($queryComment);
        $this->assertCount(47, $cellsComment);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(47, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(47, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(47, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertContains($cell0, $cellsView);
        $this->assertContains($cell0, $cellsComment);
        $this->assertContains($cell0, $cellsInput);
        $this->assertContains($cell0, $cellsEdit);
        $this->assertContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cellEuropeA, $cellsView);
        $this->assertContains($cellEuropeA, $cellsComment);
        $this->assertContains($cellEuropeA, $cellsInput);
        $this->assertContains($cellEuropeA, $cellsEdit);
        $this->assertContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertContains($cellEuropeB, $cellsView);
        $this->assertContains($cellEuropeB, $cellsComment);
        $this->assertContains($cellEuropeB, $cellsInput);
        $this->assertContains($cellEuropeB, $cellsEdit);
        $this->assertContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertContains($cellSudameriqueA, $cellsView);
        $this->assertContains($cellSudameriqueA, $cellsComment);
        $this->assertContains($cellSudameriqueA, $cellsInput);
        $this->assertContains($cellSudameriqueA, $cellsEdit);
        $this->assertContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cellSudameriqueB, $cellsView);
        $this->assertContains($cellSudameriqueB, $cellsComment);
        $this->assertContains($cellSudameriqueB, $cellsInput);
        $this->assertContains($cellSudameriqueB, $cellsEdit);
        $this->assertContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertContains($cellAnnecy, $cellsView);
        $this->assertContains($cellAnnecy, $cellsComment);
        $this->assertContains($cellAnnecy, $cellsInput);
        $this->assertContains($cellAnnecy, $cellsEdit);
        $this->assertContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertContains($cellChambery, $cellsView);
        $this->assertContains($cellChambery, $cellsComment);
        $this->assertContains($cellChambery, $cellsInput);
        $this->assertContains($cellChambery, $cellsEdit);
        $this->assertContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertContains($cellBerlin, $cellsView);
        $this->assertContains($cellBerlin, $cellsComment);
        $this->assertContains($cellBerlin, $cellsInput);
        $this->assertContains($cellBerlin, $cellsEdit);
        $this->assertContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertContains($cellLima, $cellsView);
        $this->assertContains($cellLima, $cellsComment);
        $this->assertContains($cellLima, $cellsInput);
        $this->assertContains($cellLima, $cellsEdit);
        $this->assertContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertContains($cell2012, $cellsView);
        $this->assertContains($cell2012, $cellsComment);
        $this->assertContains($cell2012, $cellsInput);
        $this->assertContains($cell2012, $cellsEdit);
        $this->assertContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertContains($cell2013, $cellsView);
        $this->assertContains($cell2013, $cellsComment);
        $this->assertContains($cell2013, $cellsInput);
        $this->assertContains($cell2013, $cellsEdit);
        $this->assertContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012Energie, $cellsView);
        $this->assertContains($cell2012Energie, $cellsComment);
        $this->assertContains($cell2012Energie, $cellsInput);
        $this->assertContains($cell2012Energie, $cellsEdit);
        $this->assertContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertContains($cell2012Transport, $cellsView);
        $this->assertContains($cell2012Transport, $cellsComment);
        $this->assertContains($cell2012Transport, $cellsInput);
        $this->assertContains($cell2012Transport, $cellsEdit);
        $this->assertContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013Energie, $cellsView);
        $this->assertContains($cell2013Energie, $cellsComment);
        $this->assertContains($cell2013Energie, $cellsInput);
        $this->assertContains($cell2013Energie, $cellsEdit);
        $this->assertContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertContains($cell2013Transport, $cellsView);
        $this->assertContains($cell2013Transport, $cellsComment);
        $this->assertContains($cell2013Transport, $cellsInput);
        $this->assertContains($cell2013Transport, $cellsEdit);
        $this->assertContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cell2012EuropeA, $cellsView);
        $this->assertContains($cell2012EuropeA, $cellsComment);
        $this->assertContains($cell2012EuropeA, $cellsInput);
        $this->assertContains($cell2012EuropeA, $cellsEdit);
        $this->assertContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertContains($cell2012EuropeB, $cellsView);
        $this->assertContains($cell2012EuropeB, $cellsComment);
        $this->assertContains($cell2012EuropeB, $cellsInput);
        $this->assertContains($cell2012EuropeB, $cellsEdit);
        $this->assertContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertContains($cell2012SudameriqueA, $cellsView);
        $this->assertContains($cell2012SudameriqueA, $cellsComment);
        $this->assertContains($cell2012SudameriqueA, $cellsInput);
        $this->assertContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cell2012SudameriqueB, $cellsView);
        $this->assertContains($cell2012SudameriqueB, $cellsComment);
        $this->assertContains($cell2012SudameriqueB, $cellsInput);
        $this->assertContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cell2013EuropeA, $cellsView);
        $this->assertContains($cell2013EuropeA, $cellsComment);
        $this->assertContains($cell2013EuropeA, $cellsInput);
        $this->assertContains($cell2013EuropeA, $cellsEdit);
        $this->assertContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertContains($cell2013EuropeB, $cellsView);
        $this->assertContains($cell2013EuropeB, $cellsComment);
        $this->assertContains($cell2013EuropeB, $cellsInput);
        $this->assertContains($cell2013EuropeB, $cellsEdit);
        $this->assertContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertContains($cell2013SudameriqueA, $cellsView);
        $this->assertContains($cell2013SudameriqueA, $cellsComment);
        $this->assertContains($cell2013SudameriqueA, $cellsInput);
        $this->assertContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cell2013SudameriqueB, $cellsView);
        $this->assertContains($cell2013SudameriqueB, $cellsComment);
        $this->assertContains($cell2013SudameriqueB, $cellsInput);
        $this->assertContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertContains($cell2012Annecy, $cellsView);
        $this->assertContains($cell2012Annecy, $cellsComment);
        $this->assertContains($cell2012Annecy, $cellsInput);
        $this->assertContains($cell2012Annecy, $cellsEdit);
        $this->assertContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertContains($cell2012Chambery, $cellsView);
        $this->assertContains($cell2012Chambery, $cellsComment);
        $this->assertContains($cell2012Chambery, $cellsInput);
        $this->assertContains($cell2012Chambery, $cellsEdit);
        $this->assertContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertContains($cell2012Berlin, $cellsView);
        $this->assertContains($cell2012Berlin, $cellsComment);
        $this->assertContains($cell2012Berlin, $cellsInput);
        $this->assertContains($cell2012Berlin, $cellsEdit);
        $this->assertContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertContains($cell2012Lima, $cellsView);
        $this->assertContains($cell2012Lima, $cellsComment);
        $this->assertContains($cell2012Lima, $cellsInput);
        $this->assertContains($cell2012Lima, $cellsEdit);
        $this->assertContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertContains($cell2013Annecy, $cellsView);
        $this->assertContains($cell2013Annecy, $cellsComment);
        $this->assertContains($cell2013Annecy, $cellsInput);
        $this->assertContains($cell2013Annecy, $cellsEdit);
        $this->assertContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertContains($cell2013Chambery, $cellsView);
        $this->assertContains($cell2013Chambery, $cellsComment);
        $this->assertContains($cell2013Chambery, $cellsInput);
        $this->assertContains($cell2013Chambery, $cellsEdit);
        $this->assertContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertContains($cell2013Berlin, $cellsView);
        $this->assertContains($cell2013Berlin, $cellsComment);
        $this->assertContains($cell2013Berlin, $cellsInput);
        $this->assertContains($cell2013Berlin, $cellsEdit);
        $this->assertContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertContains($cell2013Lima, $cellsView);
        $this->assertContains($cell2013Lima, $cellsComment);
        $this->assertContains($cell2013Lima, $cellsInput);
        $this->assertContains($cell2013Lima, $cellsEdit);
        $this->assertContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertContains($cell2012AnnecyEnergie, $cellsComment);
        $this->assertContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertContains($cell2012ChamberyEnergie, $cellsComment);
        $this->assertContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012BerlinEnergie, $cellsView);
        $this->assertContains($cell2012BerlinEnergie, $cellsComment);
        $this->assertContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012LimaEnergie, $cellsView);
        $this->assertContains($cell2012LimaEnergie, $cellsComment);
        $this->assertContains($cell2012LimaEnergie, $cellsInput);
        $this->assertContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertContains($cell2013AnnecyEnergie, $cellsComment);
        $this->assertContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertContains($cell2013ChamberyEnergie, $cellsComment);
        $this->assertContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013BerlinEnergie, $cellsView);
        $this->assertContains($cell2013BerlinEnergie, $cellsComment);
        $this->assertContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013LimaEnergie, $cellsView);
        $this->assertContains($cell2013LimaEnergie, $cellsComment);
        $this->assertContains($cell2013LimaEnergie, $cellsInput);
        $this->assertContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2012AnnecyTransport, $cellsView);
        $this->assertContains($cell2012AnnecyTransport, $cellsComment);
        $this->assertContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertContains($cell2012ChamberyTransport, $cellsView);
        $this->assertContains($cell2012ChamberyTransport, $cellsComment);
        $this->assertContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertContains($cell2012BerlinTransport, $cellsView);
        $this->assertContains($cell2012BerlinTransport, $cellsComment);
        $this->assertContains($cell2012BerlinTransport, $cellsInput);
        $this->assertContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2012LimaTransport, $cellsView);
        $this->assertContains($cell2012LimaTransport, $cellsComment);
        $this->assertContains($cell2012LimaTransport, $cellsInput);
        $this->assertContains($cell2012LimaTransport, $cellsEdit);
        $this->assertContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2013AnnecyTransport, $cellsView);
        $this->assertContains($cell2013AnnecyTransport, $cellsComment);
        $this->assertContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertContains($cell2013ChamberyTransport, $cellsView);
        $this->assertContains($cell2013ChamberyTransport, $cellsComment);
        $this->assertContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertContains($cell2013BerlinTransport, $cellsView);
        $this->assertContains($cell2013BerlinTransport, $cellsComment);
        $this->assertContains($cell2013BerlinTransport, $cellsInput);
        $this->assertContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2013LimaTransport, $cellsView);
        $this->assertContains($cell2013LimaTransport, $cellsComment);
        $this->assertContains($cellLima, $cellsInput);
        $this->assertContains($cell2013LimaTransport, $cellsEdit);
        $this->assertContains($cell2013LimaTransport, $cellsAllow);

        $reportsView = DW_Model_Report::loadList($queryView);
        $this->assertCount(12, $reportsView);
        $reportsEdit = DW_Model_Report::loadList($queryEdit);
        $this->assertCount(9, $reportsEdit);
        $reportsReport = DW_Model_Report::loadList($queryReport);
        $this->assertCount(3, $reportsReport);
        $reportsDelete = DW_Model_Report::loadList($queryDelete);
        $this->assertCount(3, $reportsDelete);

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertContains($reportGlobale, $reportsView);
        $this->assertNotContains($reportGlobale, $reportsEdit);
        $this->assertContains($reportGlobale, $reportsReport);
        $this->assertContains($reportGlobale, $reportsDelete);

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertContains($reportCellGlobale, $reportsView);
        $this->assertContains($reportCellGlobale, $reportsEdit);
        $this->assertNotContains($reportCellGlobale, $reportsReport);
        $this->assertNotContains($reportCellGlobale, $reportsDelete);

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertContains($reportZoneMarque, $reportsView);
        $this->assertNotContains($reportZoneMarque, $reportsEdit);
        $this->assertContains($reportZoneMarque, $reportsReport);
        $this->assertContains($reportZoneMarque, $reportsDelete);

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertContains($reportEuropeA, $reportsView);
        $this->assertContains($reportEuropeA, $reportsEdit);
        $this->assertNotContains($reportEuropeA, $reportsReport);
        $this->assertNotContains($reportEuropeA, $reportsDelete);
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertContains($reportEuropeB, $reportsView);
        $this->assertContains($reportEuropeB, $reportsEdit);
        $this->assertNotContains($reportEuropeB, $reportsReport);
        $this->assertNotContains($reportEuropeB, $reportsDelete);
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertContains($reportSudameriqueA, $reportsView);
        $this->assertContains($reportSudameriqueA, $reportsEdit);
        $this->assertNotContains($reportSudameriqueA, $reportsReport);
        $this->assertNotContains($reportSudameriqueA, $reportsDelete);
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertContains($reportSudameriqueB, $reportsView);
        $this->assertContains($reportSudameriqueB, $reportsEdit);
        $this->assertNotContains($reportSudameriqueB, $reportsReport);
        $this->assertNotContains($reportSudameriqueB, $reportsDelete);

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertContains($reportSite, $reportsView);
        $this->assertNotContains($reportSite, $reportsEdit);
        $this->assertContains($reportSite, $reportsReport);
        $this->assertContains($reportSite, $reportsDelete);

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertContains($reportAnnecy, $reportsView);
        $this->assertContains($reportAnnecy, $reportsEdit);
        $this->assertNotContains($reportAnnecy, $reportsReport);
        $this->assertNotContains($reportAnnecy, $reportsDelete);
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertContains($reportChambery, $reportsView);
        $this->assertContains($reportChambery, $reportsEdit);
        $this->assertNotContains($reportChambery, $reportsReport);
        $this->assertNotContains($reportChambery, $reportsDelete);
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertContains($reportBerlin, $reportsView);
        $this->assertContains($reportBerlin, $reportsEdit);
        $this->assertNotContains($reportBerlin, $reportsReport);
        $this->assertNotContains($reportBerlin, $reportsDelete);
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertContains($reportLima, $reportsView);
        $this->assertContains($reportLima, $reportsEdit);
        $this->assertNotContains($reportLima, $reportsReport);
        $this->assertNotContains($reportLima, $reportsDelete);
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
        $queryView->aclFilter->action = DefaultAction::VIEW();
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = DefaultAction::EDIT();
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = DefaultAction::DELETE();
        $queryComment = new Core_Model_Query();
        $queryComment->aclFilter->enabled = true;
        $queryComment->aclFilter->user = $user;
        $queryComment->aclFilter->action = Orga_Action_Cell::COMMENT();
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = Orga_Action_Cell::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = DefaultAction::ALLOW();
        $queryReport = new Core_Model_Query();
        $queryReport->aclFilter->enabled = true;
        $queryReport->aclFilter->user = $user;
        $queryReport->aclFilter->action = Orga_Action_Report::EDIT();

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
        $cellsComment = Orga_Model_Cell::loadList($queryComment);
        $this->assertCount(47, $cellsComment);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(47, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(47, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(47, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertContains($cell0, $cellsView);
        $this->assertContains($cell0, $cellsComment);
        $this->assertContains($cell0, $cellsInput);
        $this->assertContains($cell0, $cellsEdit);
        $this->assertContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cellEuropeA, $cellsView);
        $this->assertContains($cellEuropeA, $cellsComment);
        $this->assertContains($cellEuropeA, $cellsInput);
        $this->assertContains($cellEuropeA, $cellsEdit);
        $this->assertContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertContains($cellEuropeB, $cellsView);
        $this->assertContains($cellEuropeB, $cellsComment);
        $this->assertContains($cellEuropeB, $cellsInput);
        $this->assertContains($cellEuropeB, $cellsEdit);
        $this->assertContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertContains($cellSudameriqueA, $cellsView);
        $this->assertContains($cellSudameriqueA, $cellsComment);
        $this->assertContains($cellSudameriqueA, $cellsInput);
        $this->assertContains($cellSudameriqueA, $cellsEdit);
        $this->assertContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cellSudameriqueB, $cellsView);
        $this->assertContains($cellSudameriqueB, $cellsComment);
        $this->assertContains($cellSudameriqueB, $cellsInput);
        $this->assertContains($cellSudameriqueB, $cellsEdit);
        $this->assertContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertContains($cellAnnecy, $cellsView);
        $this->assertContains($cellAnnecy, $cellsComment);
        $this->assertContains($cellAnnecy, $cellsInput);
        $this->assertContains($cellAnnecy, $cellsEdit);
        $this->assertContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertContains($cellChambery, $cellsView);
        $this->assertContains($cellChambery, $cellsComment);
        $this->assertContains($cellChambery, $cellsInput);
        $this->assertContains($cellChambery, $cellsEdit);
        $this->assertContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertContains($cellBerlin, $cellsView);
        $this->assertContains($cellBerlin, $cellsComment);
        $this->assertContains($cellBerlin, $cellsInput);
        $this->assertContains($cellBerlin, $cellsEdit);
        $this->assertContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertContains($cellLima, $cellsView);
        $this->assertContains($cellLima, $cellsComment);
        $this->assertContains($cellLima, $cellsInput);
        $this->assertContains($cellLima, $cellsEdit);
        $this->assertContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertContains($cell2012, $cellsView);
        $this->assertContains($cell2012, $cellsComment);
        $this->assertContains($cell2012, $cellsInput);
        $this->assertContains($cell2012, $cellsEdit);
        $this->assertContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertContains($cell2013, $cellsView);
        $this->assertContains($cell2013, $cellsComment);
        $this->assertContains($cell2013, $cellsInput);
        $this->assertContains($cell2013, $cellsEdit);
        $this->assertContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012Energie, $cellsView);
        $this->assertContains($cell2012Energie, $cellsComment);
        $this->assertContains($cell2012Energie, $cellsInput);
        $this->assertContains($cell2012Energie, $cellsEdit);
        $this->assertContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertContains($cell2012Transport, $cellsView);
        $this->assertContains($cell2012Transport, $cellsComment);
        $this->assertContains($cell2012Transport, $cellsInput);
        $this->assertContains($cell2012Transport, $cellsEdit);
        $this->assertContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013Energie, $cellsView);
        $this->assertContains($cell2013Energie, $cellsComment);
        $this->assertContains($cell2013Energie, $cellsInput);
        $this->assertContains($cell2013Energie, $cellsEdit);
        $this->assertContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertContains($cell2013Transport, $cellsView);
        $this->assertContains($cell2013Transport, $cellsComment);
        $this->assertContains($cell2013Transport, $cellsInput);
        $this->assertContains($cell2013Transport, $cellsEdit);
        $this->assertContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cell2012EuropeA, $cellsView);
        $this->assertContains($cell2012EuropeA, $cellsComment);
        $this->assertContains($cell2012EuropeA, $cellsInput);
        $this->assertContains($cell2012EuropeA, $cellsEdit);
        $this->assertContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertContains($cell2012EuropeB, $cellsView);
        $this->assertContains($cell2012EuropeB, $cellsComment);
        $this->assertContains($cell2012EuropeB, $cellsInput);
        $this->assertContains($cell2012EuropeB, $cellsEdit);
        $this->assertContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertContains($cell2012SudameriqueA, $cellsView);
        $this->assertContains($cell2012SudameriqueA, $cellsComment);
        $this->assertContains($cell2012SudameriqueA, $cellsInput);
        $this->assertContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cell2012SudameriqueB, $cellsView);
        $this->assertContains($cell2012SudameriqueB, $cellsComment);
        $this->assertContains($cell2012SudameriqueB, $cellsInput);
        $this->assertContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cell2013EuropeA, $cellsView);
        $this->assertContains($cell2013EuropeA, $cellsComment);
        $this->assertContains($cell2013EuropeA, $cellsInput);
        $this->assertContains($cell2013EuropeA, $cellsEdit);
        $this->assertContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertContains($cell2013EuropeB, $cellsView);
        $this->assertContains($cell2013EuropeB, $cellsComment);
        $this->assertContains($cell2013EuropeB, $cellsInput);
        $this->assertContains($cell2013EuropeB, $cellsEdit);
        $this->assertContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertContains($cell2013SudameriqueA, $cellsView);
        $this->assertContains($cell2013SudameriqueA, $cellsComment);
        $this->assertContains($cell2013SudameriqueA, $cellsInput);
        $this->assertContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cell2013SudameriqueB, $cellsView);
        $this->assertContains($cell2013SudameriqueB, $cellsComment);
        $this->assertContains($cell2013SudameriqueB, $cellsInput);
        $this->assertContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertContains($cell2012Annecy, $cellsView);
        $this->assertContains($cell2012Annecy, $cellsComment);
        $this->assertContains($cell2012Annecy, $cellsInput);
        $this->assertContains($cell2012Annecy, $cellsEdit);
        $this->assertContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertContains($cell2012Chambery, $cellsView);
        $this->assertContains($cell2012Chambery, $cellsComment);
        $this->assertContains($cell2012Chambery, $cellsInput);
        $this->assertContains($cell2012Chambery, $cellsEdit);
        $this->assertContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertContains($cell2012Berlin, $cellsView);
        $this->assertContains($cell2012Berlin, $cellsComment);
        $this->assertContains($cell2012Berlin, $cellsInput);
        $this->assertContains($cell2012Berlin, $cellsEdit);
        $this->assertContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertContains($cell2012Lima, $cellsView);
        $this->assertContains($cell2012Lima, $cellsComment);
        $this->assertContains($cell2012Lima, $cellsInput);
        $this->assertContains($cell2012Lima, $cellsEdit);
        $this->assertContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertContains($cell2013Annecy, $cellsView);
        $this->assertContains($cell2013Annecy, $cellsComment);
        $this->assertContains($cell2013Annecy, $cellsInput);
        $this->assertContains($cell2013Annecy, $cellsEdit);
        $this->assertContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertContains($cell2013Chambery, $cellsView);
        $this->assertContains($cell2013Chambery, $cellsComment);
        $this->assertContains($cell2013Chambery, $cellsInput);
        $this->assertContains($cell2013Chambery, $cellsEdit);
        $this->assertContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertContains($cell2013Berlin, $cellsView);
        $this->assertContains($cell2013Berlin, $cellsComment);
        $this->assertContains($cell2013Berlin, $cellsInput);
        $this->assertContains($cell2013Berlin, $cellsEdit);
        $this->assertContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertContains($cell2013Lima, $cellsView);
        $this->assertContains($cell2013Lima, $cellsComment);
        $this->assertContains($cell2013Lima, $cellsInput);
        $this->assertContains($cell2013Lima, $cellsEdit);
        $this->assertContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertContains($cell2012AnnecyEnergie, $cellsComment);
        $this->assertContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertContains($cell2012ChamberyEnergie, $cellsComment);
        $this->assertContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012BerlinEnergie, $cellsView);
        $this->assertContains($cell2012BerlinEnergie, $cellsComment);
        $this->assertContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012LimaEnergie, $cellsView);
        $this->assertContains($cell2012LimaEnergie, $cellsComment);
        $this->assertContains($cell2012LimaEnergie, $cellsInput);
        $this->assertContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertContains($cell2013AnnecyEnergie, $cellsComment);
        $this->assertContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertContains($cell2013ChamberyEnergie, $cellsComment);
        $this->assertContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013BerlinEnergie, $cellsView);
        $this->assertContains($cell2013BerlinEnergie, $cellsComment);
        $this->assertContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013LimaEnergie, $cellsView);
        $this->assertContains($cell2013LimaEnergie, $cellsComment);
        $this->assertContains($cell2013LimaEnergie, $cellsInput);
        $this->assertContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2012AnnecyTransport, $cellsView);
        $this->assertContains($cell2012AnnecyTransport, $cellsComment);
        $this->assertContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertContains($cell2012ChamberyTransport, $cellsView);
        $this->assertContains($cell2012ChamberyTransport, $cellsComment);
        $this->assertContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertContains($cell2012BerlinTransport, $cellsView);
        $this->assertContains($cell2012BerlinTransport, $cellsComment);
        $this->assertContains($cell2012BerlinTransport, $cellsInput);
        $this->assertContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2012LimaTransport, $cellsView);
        $this->assertContains($cell2012LimaTransport, $cellsComment);
        $this->assertContains($cell2012LimaTransport, $cellsInput);
        $this->assertContains($cell2012LimaTransport, $cellsEdit);
        $this->assertContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2013AnnecyTransport, $cellsView);
        $this->assertContains($cell2013AnnecyTransport, $cellsComment);
        $this->assertContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertContains($cell2013ChamberyTransport, $cellsView);
        $this->assertContains($cell2013ChamberyTransport, $cellsComment);
        $this->assertContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertContains($cell2013BerlinTransport, $cellsView);
        $this->assertContains($cell2013BerlinTransport, $cellsComment);
        $this->assertContains($cell2013BerlinTransport, $cellsInput);
        $this->assertContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2013LimaTransport, $cellsView);
        $this->assertContains($cell2013LimaTransport, $cellsComment);
        $this->assertContains($cellLima, $cellsInput);
        $this->assertContains($cell2013LimaTransport, $cellsEdit);
        $this->assertContains($cell2013LimaTransport, $cellsAllow);

        $reportsView = DW_Model_Report::loadList($queryView);
        $this->assertCount(9, $reportsView);
        $reportsEdit = DW_Model_Report::loadList($queryEdit);
        $this->assertCount(9, $reportsEdit);
        $reportsReport = DW_Model_Report::loadList($queryReport);
        $this->assertCount(0, $reportsReport);
        $reportsDelete = DW_Model_Report::loadList($queryDelete);
        $this->assertCount(0, $reportsDelete);

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertNotContains($reportGlobale, $reportsView);
        $this->assertNotContains($reportGlobale, $reportsEdit);
        $this->assertNotContains($reportGlobale, $reportsReport);
        $this->assertNotContains($reportGlobale, $reportsDelete);

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertContains($reportCellGlobale, $reportsView);
        $this->assertContains($reportCellGlobale, $reportsEdit);
        $this->assertNotContains($reportCellGlobale, $reportsReport);
        $this->assertNotContains($reportCellGlobale, $reportsDelete);

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertNotContains($reportZoneMarque, $reportsView);
        $this->assertNotContains($reportZoneMarque, $reportsEdit);
        $this->assertNotContains($reportZoneMarque, $reportsReport);
        $this->assertNotContains($reportZoneMarque, $reportsDelete);

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertContains($reportEuropeA, $reportsView);
        $this->assertContains($reportEuropeA, $reportsEdit);
        $this->assertNotContains($reportEuropeA, $reportsReport);
        $this->assertNotContains($reportEuropeA, $reportsDelete);
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertContains($reportEuropeB, $reportsView);
        $this->assertContains($reportEuropeB, $reportsEdit);
        $this->assertNotContains($reportEuropeB, $reportsReport);
        $this->assertNotContains($reportEuropeB, $reportsDelete);
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertContains($reportSudameriqueA, $reportsView);
        $this->assertContains($reportSudameriqueA, $reportsEdit);
        $this->assertNotContains($reportSudameriqueA, $reportsReport);
        $this->assertNotContains($reportSudameriqueA, $reportsDelete);
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertContains($reportSudameriqueB, $reportsView);
        $this->assertContains($reportSudameriqueB, $reportsEdit);
        $this->assertNotContains($reportSudameriqueB, $reportsReport);
        $this->assertNotContains($reportSudameriqueB, $reportsDelete);

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSite, $reportsView);
        $this->assertNotContains($reportSite, $reportsEdit);
        $this->assertNotContains($reportSite, $reportsReport);
        $this->assertNotContains($reportSite, $reportsDelete);

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertContains($reportAnnecy, $reportsView);
        $this->assertContains($reportAnnecy, $reportsEdit);
        $this->assertNotContains($reportAnnecy, $reportsReport);
        $this->assertNotContains($reportAnnecy, $reportsDelete);
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertContains($reportChambery, $reportsView);
        $this->assertContains($reportChambery, $reportsEdit);
        $this->assertNotContains($reportChambery, $reportsReport);
        $this->assertNotContains($reportChambery, $reportsDelete);
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertContains($reportBerlin, $reportsView);
        $this->assertContains($reportBerlin, $reportsEdit);
        $this->assertNotContains($reportBerlin, $reportsReport);
        $this->assertNotContains($reportBerlin, $reportsDelete);
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertContains($reportLima, $reportsView);
        $this->assertContains($reportLima, $reportsEdit);
        $this->assertNotContains($reportLima, $reportsReport);
        $this->assertNotContains($reportLima, $reportsDelete);
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
        $queryView->aclFilter->action = DefaultAction::VIEW();
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = DefaultAction::EDIT();
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = DefaultAction::DELETE();
        $queryComment = new Core_Model_Query();
        $queryComment->aclFilter->enabled = true;
        $queryComment->aclFilter->user = $user;
        $queryComment->aclFilter->action = Orga_Action_Cell::COMMENT();
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = Orga_Action_Cell::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = DefaultAction::ALLOW();
        $queryReport = new Core_Model_Query();
        $queryReport->aclFilter->enabled = true;
        $queryReport->aclFilter->user = $user;
        $queryReport->aclFilter->action = Orga_Action_Report::EDIT();

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
        $cellsComment = Orga_Model_Cell::loadList($queryComment);
        $this->assertCount(17, $cellsComment);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(17, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(0, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(0, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertNotContains($cell0, $cellsView);
        $this->assertNotContains($cell0, $cellsComment);
        $this->assertNotContains($cell0, $cellsInput);
        $this->assertNotContains($cell0, $cellsEdit);
        $this->assertNotContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cellEuropeA, $cellsView);
        $this->assertContains($cellEuropeA, $cellsComment);
        $this->assertContains($cellEuropeA, $cellsInput);
        $this->assertNotContains($cellEuropeA, $cellsEdit);
        $this->assertNotContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cellEuropeB, $cellsView);
        $this->assertNotContains($cellEuropeB, $cellsComment);
        $this->assertNotContains($cellEuropeB, $cellsInput);
        $this->assertNotContains($cellEuropeB, $cellsEdit);
        $this->assertNotContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cellSudameriqueA, $cellsView);
        $this->assertNotContains($cellSudameriqueA, $cellsComment);
        $this->assertNotContains($cellSudameriqueA, $cellsInput);
        $this->assertNotContains($cellSudameriqueA, $cellsEdit);
        $this->assertNotContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cellSudameriqueB, $cellsView);
        $this->assertNotContains($cellSudameriqueB, $cellsComment);
        $this->assertNotContains($cellSudameriqueB, $cellsInput);
        $this->assertNotContains($cellSudameriqueB, $cellsEdit);
        $this->assertNotContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertContains($cellAnnecy, $cellsView);
        $this->assertContains($cellAnnecy, $cellsComment);
        $this->assertContains($cellAnnecy, $cellsInput);
        $this->assertNotContains($cellAnnecy, $cellsEdit);
        $this->assertNotContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertContains($cellChambery, $cellsView);
        $this->assertContains($cellChambery, $cellsComment);
        $this->assertContains($cellChambery, $cellsInput);
        $this->assertNotContains($cellChambery, $cellsEdit);
        $this->assertNotContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertNotContains($cellBerlin, $cellsView);
        $this->assertNotContains($cellBerlin, $cellsComment);
        $this->assertNotContains($cellBerlin, $cellsInput);
        $this->assertNotContains($cellBerlin, $cellsEdit);
        $this->assertNotContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertNotContains($cellLima, $cellsView);
        $this->assertNotContains($cellLima, $cellsComment);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cellLima, $cellsEdit);
        $this->assertNotContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertNotContains($cell2012, $cellsView);
        $this->assertNotContains($cell2012, $cellsComment);
        $this->assertNotContains($cell2012, $cellsInput);
        $this->assertNotContains($cell2012, $cellsEdit);
        $this->assertNotContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertNotContains($cell2013, $cellsView);
        $this->assertNotContains($cell2013, $cellsComment);
        $this->assertNotContains($cell2013, $cellsInput);
        $this->assertNotContains($cell2013, $cellsEdit);
        $this->assertNotContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012Energie, $cellsView);
        $this->assertNotContains($cell2012Energie, $cellsComment);
        $this->assertNotContains($cell2012Energie, $cellsInput);
        $this->assertNotContains($cell2012Energie, $cellsEdit);
        $this->assertNotContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012Transport, $cellsView);
        $this->assertNotContains($cell2012Transport, $cellsComment);
        $this->assertNotContains($cell2012Transport, $cellsInput);
        $this->assertNotContains($cell2012Transport, $cellsEdit);
        $this->assertNotContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013Energie, $cellsView);
        $this->assertNotContains($cell2013Energie, $cellsComment);
        $this->assertNotContains($cell2013Energie, $cellsInput);
        $this->assertNotContains($cell2013Energie, $cellsEdit);
        $this->assertNotContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013Transport, $cellsView);
        $this->assertNotContains($cell2013Transport, $cellsComment);
        $this->assertNotContains($cell2013Transport, $cellsInput);
        $this->assertNotContains($cell2013Transport, $cellsEdit);
        $this->assertNotContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cell2012EuropeA, $cellsView);
        $this->assertContains($cell2012EuropeA, $cellsComment);
        $this->assertContains($cell2012EuropeA, $cellsInput);
        $this->assertNotContains($cell2012EuropeA, $cellsEdit);
        $this->assertNotContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2012EuropeB, $cellsView);
        $this->assertNotContains($cell2012EuropeB, $cellsComment);
        $this->assertNotContains($cell2012EuropeB, $cellsInput);
        $this->assertNotContains($cell2012EuropeB, $cellsEdit);
        $this->assertNotContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2012SudameriqueA, $cellsView);
        $this->assertNotContains($cell2012SudameriqueA, $cellsComment);
        $this->assertNotContains($cell2012SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2012SudameriqueB, $cellsView);
        $this->assertNotContains($cell2012SudameriqueB, $cellsComment);
        $this->assertNotContains($cell2012SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertContains($cell2013EuropeA, $cellsView);
        $this->assertContains($cell2013EuropeA, $cellsComment);
        $this->assertContains($cell2013EuropeA, $cellsInput);
        $this->assertNotContains($cell2013EuropeA, $cellsEdit);
        $this->assertNotContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2013EuropeB, $cellsView);
        $this->assertNotContains($cell2013EuropeB, $cellsComment);
        $this->assertNotContains($cell2013EuropeB, $cellsInput);
        $this->assertNotContains($cell2013EuropeB, $cellsEdit);
        $this->assertNotContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2013SudameriqueA, $cellsView);
        $this->assertNotContains($cell2013SudameriqueA, $cellsComment);
        $this->assertNotContains($cell2013SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2013SudameriqueB, $cellsView);
        $this->assertNotContains($cell2013SudameriqueB, $cellsComment);
        $this->assertNotContains($cell2013SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertContains($cell2012Annecy, $cellsView);
        $this->assertContains($cell2012Annecy, $cellsComment);
        $this->assertContains($cell2012Annecy, $cellsInput);
        $this->assertNotContains($cell2012Annecy, $cellsEdit);
        $this->assertNotContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertContains($cell2012Chambery, $cellsView);
        $this->assertContains($cell2012Chambery, $cellsComment);
        $this->assertContains($cell2012Chambery, $cellsInput);
        $this->assertNotContains($cell2012Chambery, $cellsEdit);
        $this->assertNotContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2012Berlin, $cellsView);
        $this->assertNotContains($cell2012Berlin, $cellsComment);
        $this->assertNotContains($cell2012Berlin, $cellsInput);
        $this->assertNotContains($cell2012Berlin, $cellsEdit);
        $this->assertNotContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertNotContains($cell2012Lima, $cellsView);
        $this->assertNotContains($cell2012Lima, $cellsComment);
        $this->assertNotContains($cell2012Lima, $cellsInput);
        $this->assertNotContains($cell2012Lima, $cellsEdit);
        $this->assertNotContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertContains($cell2013Annecy, $cellsView);
        $this->assertContains($cell2013Annecy, $cellsComment);
        $this->assertContains($cell2013Annecy, $cellsInput);
        $this->assertNotContains($cell2013Annecy, $cellsEdit);
        $this->assertNotContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertContains($cell2013Chambery, $cellsView);
        $this->assertContains($cell2013Chambery, $cellsComment);
        $this->assertContains($cell2013Chambery, $cellsInput);
        $this->assertNotContains($cell2013Chambery, $cellsEdit);
        $this->assertNotContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2013Berlin, $cellsView);
        $this->assertNotContains($cell2013Berlin, $cellsComment);
        $this->assertNotContains($cell2013Berlin, $cellsInput);
        $this->assertNotContains($cell2013Berlin, $cellsEdit);
        $this->assertNotContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertNotContains($cell2013Lima, $cellsView);
        $this->assertNotContains($cell2013Lima, $cellsComment);
        $this->assertNotContains($cell2013Lima, $cellsInput);
        $this->assertNotContains($cell2013Lima, $cellsEdit);
        $this->assertNotContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertContains($cell2012AnnecyEnergie, $cellsComment);
        $this->assertContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertContains($cell2012ChamberyEnergie, $cellsComment);
        $this->assertContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsComment);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012LimaEnergie, $cellsView);
        $this->assertNotContains($cell2012LimaEnergie, $cellsComment);
        $this->assertNotContains($cell2012LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertContains($cell2013AnnecyEnergie, $cellsComment);
        $this->assertContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertContains($cell2013ChamberyEnergie, $cellsComment);
        $this->assertContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsComment);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013LimaEnergie, $cellsView);
        $this->assertNotContains($cell2013LimaEnergie, $cellsComment);
        $this->assertNotContains($cell2013LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2012AnnecyTransport, $cellsView);
        $this->assertContains($cell2012AnnecyTransport, $cellsComment);
        $this->assertContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertContains($cell2012ChamberyTransport, $cellsView);
        $this->assertContains($cell2012ChamberyTransport, $cellsComment);
        $this->assertContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012BerlinTransport, $cellsView);
        $this->assertNotContains($cell2012BerlinTransport, $cellsComment);
        $this->assertNotContains($cell2012BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012LimaTransport, $cellsView);
        $this->assertNotContains($cell2012LimaTransport, $cellsComment);
        $this->assertNotContains($cell2012LimaTransport, $cellsInput);
        $this->assertNotContains($cell2012LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2013AnnecyTransport, $cellsView);
        $this->assertContains($cell2013AnnecyTransport, $cellsComment);
        $this->assertContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertContains($cell2013ChamberyTransport, $cellsView);
        $this->assertContains($cell2013ChamberyTransport, $cellsComment);
        $this->assertContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013BerlinTransport, $cellsView);
        $this->assertNotContains($cell2013BerlinTransport, $cellsComment);
        $this->assertNotContains($cell2013BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013LimaTransport, $cellsView);
        $this->assertNotContains($cell2013LimaTransport, $cellsComment);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cell2013LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2013LimaTransport, $cellsAllow);

        $reportsView = DW_Model_Report::loadList($queryView);
        $this->assertCount(3, $reportsView);
        $reportsEdit = DW_Model_Report::loadList($queryEdit);
        $this->assertCount(0, $reportsEdit);
        $reportsReport = DW_Model_Report::loadList($queryReport);
        $this->assertCount(0, $reportsReport);
        $reportsDelete = DW_Model_Report::loadList($queryDelete);
        $this->assertCount(0, $reportsDelete);

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertNotContains($reportGlobale, $reportsView);
        $this->assertNotContains($reportGlobale, $reportsEdit);
        $this->assertNotContains($reportGlobale, $reportsReport);
        $this->assertNotContains($reportGlobale, $reportsDelete);

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertNotContains($reportCellGlobale, $reportsView);
        $this->assertNotContains($reportCellGlobale, $reportsEdit);
        $this->assertNotContains($reportCellGlobale, $reportsReport);
        $this->assertNotContains($reportCellGlobale, $reportsDelete);

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertNotContains($reportZoneMarque, $reportsView);
        $this->assertNotContains($reportZoneMarque, $reportsEdit);
        $this->assertNotContains($reportZoneMarque, $reportsReport);
        $this->assertNotContains($reportZoneMarque, $reportsDelete);

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertContains($reportEuropeA, $reportsView);
        $this->assertNotContains($reportEuropeA, $reportsEdit);
        $this->assertNotContains($reportEuropeA, $reportsReport);
        $this->assertNotContains($reportEuropeA, $reportsDelete);
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertNotContains($reportEuropeB, $reportsView);
        $this->assertNotContains($reportEuropeB, $reportsEdit);
        $this->assertNotContains($reportEuropeB, $reportsReport);
        $this->assertNotContains($reportEuropeB, $reportsDelete);
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSudameriqueA, $reportsView);
        $this->assertNotContains($reportSudameriqueA, $reportsEdit);
        $this->assertNotContains($reportSudameriqueA, $reportsReport);
        $this->assertNotContains($reportSudameriqueA, $reportsDelete);
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSudameriqueB, $reportsView);
        $this->assertNotContains($reportSudameriqueB, $reportsEdit);
        $this->assertNotContains($reportSudameriqueB, $reportsReport);
        $this->assertNotContains($reportSudameriqueB, $reportsDelete);

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSite, $reportsView);
        $this->assertNotContains($reportSite, $reportsEdit);
        $this->assertNotContains($reportSite, $reportsReport);
        $this->assertNotContains($reportSite, $reportsDelete);

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertContains($reportAnnecy, $reportsView);
        $this->assertNotContains($reportAnnecy, $reportsEdit);
        $this->assertNotContains($reportAnnecy, $reportsReport);
        $this->assertNotContains($reportAnnecy, $reportsDelete);
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertContains($reportChambery, $reportsView);
        $this->assertNotContains($reportChambery, $reportsEdit);
        $this->assertNotContains($reportChambery, $reportsReport);
        $this->assertNotContains($reportChambery, $reportsDelete);
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertNotContains($reportBerlin, $reportsView);
        $this->assertNotContains($reportBerlin, $reportsEdit);
        $this->assertNotContains($reportBerlin, $reportsReport);
        $this->assertNotContains($reportBerlin, $reportsDelete);
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertNotContains($reportLima, $reportsView);
        $this->assertNotContains($reportLima, $reportsEdit);
        $this->assertNotContains($reportLima, $reportsReport);
        $this->assertNotContains($reportLima, $reportsDelete);
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
        $queryView->aclFilter->action = DefaultAction::VIEW();
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = DefaultAction::EDIT();
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = DefaultAction::DELETE();
        $queryComment = new Core_Model_Query();
        $queryComment->aclFilter->enabled = true;
        $queryComment->aclFilter->user = $user;
        $queryComment->aclFilter->action = Orga_Action_Cell::COMMENT();
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = Orga_Action_Cell::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = DefaultAction::ALLOW();
        $queryReport = new Core_Model_Query();
        $queryReport->aclFilter->enabled = true;
        $queryReport->aclFilter->user = $user;
        $queryReport->aclFilter->action = Orga_Action_Report::EDIT();

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
        $cellsComment = Orga_Model_Cell::loadList($queryComment);
        $this->assertCount(10, $cellsComment);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(0, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(0, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(0, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertNotContains($cell0, $cellsView);
        $this->assertNotContains($cell0, $cellsComment);
        $this->assertNotContains($cell0, $cellsInput);
        $this->assertNotContains($cell0, $cellsEdit);
        $this->assertNotContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cellEuropeA, $cellsView);
        $this->assertNotContains($cellEuropeA, $cellsComment);
        $this->assertNotContains($cellEuropeA, $cellsInput);
        $this->assertNotContains($cellEuropeA, $cellsEdit);
        $this->assertNotContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cellEuropeB, $cellsView);
        $this->assertNotContains($cellEuropeB, $cellsComment);
        $this->assertNotContains($cellEuropeB, $cellsInput);
        $this->assertNotContains($cellEuropeB, $cellsEdit);
        $this->assertNotContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cellSudameriqueA, $cellsView);
        $this->assertNotContains($cellSudameriqueA, $cellsComment);
        $this->assertNotContains($cellSudameriqueA, $cellsInput);
        $this->assertNotContains($cellSudameriqueA, $cellsEdit);
        $this->assertNotContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cellSudameriqueB, $cellsView);
        $this->assertContains($cellSudameriqueB, $cellsComment);
        $this->assertNotContains($cellSudameriqueB, $cellsInput);
        $this->assertNotContains($cellSudameriqueB, $cellsEdit);
        $this->assertNotContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertNotContains($cellAnnecy, $cellsView);
        $this->assertNotContains($cellAnnecy, $cellsComment);
        $this->assertNotContains($cellAnnecy, $cellsInput);
        $this->assertNotContains($cellAnnecy, $cellsEdit);
        $this->assertNotContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertNotContains($cellChambery, $cellsView);
        $this->assertNotContains($cellChambery, $cellsComment);
        $this->assertNotContains($cellChambery, $cellsInput);
        $this->assertNotContains($cellChambery, $cellsEdit);
        $this->assertNotContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertNotContains($cellBerlin, $cellsView);
        $this->assertNotContains($cellBerlin, $cellsComment);
        $this->assertNotContains($cellBerlin, $cellsInput);
        $this->assertNotContains($cellBerlin, $cellsEdit);
        $this->assertNotContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertContains($cellLima, $cellsView);
        $this->assertContains($cellLima, $cellsComment);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cellLima, $cellsEdit);
        $this->assertNotContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertNotContains($cell2012, $cellsView);
        $this->assertNotContains($cell2012, $cellsComment);
        $this->assertNotContains($cell2012, $cellsInput);
        $this->assertNotContains($cell2012, $cellsEdit);
        $this->assertNotContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertNotContains($cell2013, $cellsView);
        $this->assertNotContains($cell2013, $cellsComment);
        $this->assertNotContains($cell2013, $cellsInput);
        $this->assertNotContains($cell2013, $cellsEdit);
        $this->assertNotContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012Energie, $cellsView);
        $this->assertNotContains($cell2012Energie, $cellsComment);
        $this->assertNotContains($cell2012Energie, $cellsInput);
        $this->assertNotContains($cell2012Energie, $cellsEdit);
        $this->assertNotContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012Transport, $cellsView);
        $this->assertNotContains($cell2012Transport, $cellsComment);
        $this->assertNotContains($cell2012Transport, $cellsInput);
        $this->assertNotContains($cell2012Transport, $cellsEdit);
        $this->assertNotContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013Energie, $cellsView);
        $this->assertNotContains($cell2013Energie, $cellsComment);
        $this->assertNotContains($cell2013Energie, $cellsInput);
        $this->assertNotContains($cell2013Energie, $cellsEdit);
        $this->assertNotContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013Transport, $cellsView);
        $this->assertNotContains($cell2013Transport, $cellsComment);
        $this->assertNotContains($cell2013Transport, $cellsInput);
        $this->assertNotContains($cell2013Transport, $cellsEdit);
        $this->assertNotContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2012EuropeA, $cellsView);
        $this->assertNotContains($cell2012EuropeA, $cellsComment);
        $this->assertNotContains($cell2012EuropeA, $cellsInput);
        $this->assertNotContains($cell2012EuropeA, $cellsEdit);
        $this->assertNotContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2012EuropeB, $cellsView);
        $this->assertNotContains($cell2012EuropeB, $cellsComment);
        $this->assertNotContains($cell2012EuropeB, $cellsInput);
        $this->assertNotContains($cell2012EuropeB, $cellsEdit);
        $this->assertNotContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2012SudameriqueA, $cellsView);
        $this->assertNotContains($cell2012SudameriqueA, $cellsComment);
        $this->assertNotContains($cell2012SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cell2012SudameriqueB, $cellsView);
        $this->assertContains($cell2012SudameriqueB, $cellsComment);
        $this->assertNotContains($cell2012SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2013EuropeA, $cellsView);
        $this->assertNotContains($cell2013EuropeA, $cellsComment);
        $this->assertNotContains($cell2013EuropeA, $cellsInput);
        $this->assertNotContains($cell2013EuropeA, $cellsEdit);
        $this->assertNotContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2013EuropeB, $cellsView);
        $this->assertNotContains($cell2013EuropeB, $cellsComment);
        $this->assertNotContains($cell2013EuropeB, $cellsInput);
        $this->assertNotContains($cell2013EuropeB, $cellsEdit);
        $this->assertNotContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2013SudameriqueA, $cellsView);
        $this->assertNotContains($cell2013SudameriqueA, $cellsComment);
        $this->assertNotContains($cell2013SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertContains($cell2013SudameriqueB, $cellsView);
        $this->assertContains($cell2013SudameriqueB, $cellsComment);
        $this->assertNotContains($cell2013SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertNotContains($cell2012Annecy, $cellsView);
        $this->assertNotContains($cell2012Annecy, $cellsComment);
        $this->assertNotContains($cell2012Annecy, $cellsInput);
        $this->assertNotContains($cell2012Annecy, $cellsEdit);
        $this->assertNotContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertNotContains($cell2012Chambery, $cellsView);
        $this->assertNotContains($cell2012Chambery, $cellsComment);
        $this->assertNotContains($cell2012Chambery, $cellsInput);
        $this->assertNotContains($cell2012Chambery, $cellsEdit);
        $this->assertNotContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2012Berlin, $cellsView);
        $this->assertNotContains($cell2012Berlin, $cellsComment);
        $this->assertNotContains($cell2012Berlin, $cellsInput);
        $this->assertNotContains($cell2012Berlin, $cellsEdit);
        $this->assertNotContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertContains($cell2012Lima, $cellsView);
        $this->assertContains($cell2012Lima, $cellsComment);
        $this->assertNotContains($cell2012Lima, $cellsInput);
        $this->assertNotContains($cell2012Lima, $cellsEdit);
        $this->assertNotContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertNotContains($cell2013Annecy, $cellsView);
        $this->assertNotContains($cell2013Annecy, $cellsComment);
        $this->assertNotContains($cell2013Annecy, $cellsInput);
        $this->assertNotContains($cell2013Annecy, $cellsEdit);
        $this->assertNotContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertNotContains($cell2013Chambery, $cellsView);
        $this->assertNotContains($cell2013Chambery, $cellsComment);
        $this->assertNotContains($cell2013Chambery, $cellsInput);
        $this->assertNotContains($cell2013Chambery, $cellsEdit);
        $this->assertNotContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2013Berlin, $cellsView);
        $this->assertNotContains($cell2013Berlin, $cellsComment);
        $this->assertNotContains($cell2013Berlin, $cellsInput);
        $this->assertNotContains($cell2013Berlin, $cellsEdit);
        $this->assertNotContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertContains($cell2013Lima, $cellsView);
        $this->assertContains($cell2013Lima, $cellsComment);
        $this->assertNotContains($cell2013Lima, $cellsInput);
        $this->assertNotContains($cell2013Lima, $cellsEdit);
        $this->assertNotContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsComment);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsComment);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsComment);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012LimaEnergie, $cellsView);
        $this->assertContains($cell2012LimaEnergie, $cellsComment);
        $this->assertNotContains($cell2012LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsComment);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsComment);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsComment);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013LimaEnergie, $cellsView);
        $this->assertContains($cell2013LimaEnergie, $cellsComment);
        $this->assertNotContains($cell2013LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsView);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsComment);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsComment);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012BerlinTransport, $cellsView);
        $this->assertNotContains($cell2012BerlinTransport, $cellsComment);
        $this->assertNotContains($cell2012BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2012LimaTransport, $cellsView);
        $this->assertContains($cell2012LimaTransport, $cellsComment);
        $this->assertNotContains($cell2012LimaTransport, $cellsInput);
        $this->assertNotContains($cell2012LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsView);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsComment);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsComment);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013BerlinTransport, $cellsView);
        $this->assertNotContains($cell2013BerlinTransport, $cellsComment);
        $this->assertNotContains($cell2013BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2013LimaTransport, $cellsView);
        $this->assertContains($cell2013LimaTransport, $cellsComment);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cell2013LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2013LimaTransport, $cellsAllow);

        $reportsView = DW_Model_Report::loadList($queryView);
        $this->assertCount(2, $reportsView);
        $reportsEdit = DW_Model_Report::loadList($queryEdit);
        $this->assertCount(0, $reportsEdit);
        $reportsReport = DW_Model_Report::loadList($queryReport);
        $this->assertCount(0, $reportsReport);
        $reportsDelete = DW_Model_Report::loadList($queryDelete);
        $this->assertCount(0, $reportsDelete);

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertNotContains($reportGlobale, $reportsView);
        $this->assertNotContains($reportGlobale, $reportsEdit);
        $this->assertNotContains($reportGlobale, $reportsReport);
        $this->assertNotContains($reportGlobale, $reportsDelete);

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertNotContains($reportCellGlobale, $reportsView);
        $this->assertNotContains($reportCellGlobale, $reportsEdit);
        $this->assertNotContains($reportCellGlobale, $reportsReport);
        $this->assertNotContains($reportCellGlobale, $reportsDelete);

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertNotContains($reportZoneMarque, $reportsView);
        $this->assertNotContains($reportZoneMarque, $reportsEdit);
        $this->assertNotContains($reportZoneMarque, $reportsReport);
        $this->assertNotContains($reportZoneMarque, $reportsDelete);

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertNotContains($reportEuropeA, $reportsView);
        $this->assertNotContains($reportEuropeA, $reportsEdit);
        $this->assertNotContains($reportEuropeA, $reportsReport);
        $this->assertNotContains($reportEuropeA, $reportsDelete);
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertNotContains($reportEuropeB, $reportsView);
        $this->assertNotContains($reportEuropeB, $reportsEdit);
        $this->assertNotContains($reportEuropeB, $reportsReport);
        $this->assertNotContains($reportEuropeB, $reportsDelete);
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSudameriqueA, $reportsView);
        $this->assertNotContains($reportSudameriqueA, $reportsEdit);
        $this->assertNotContains($reportSudameriqueA, $reportsReport);
        $this->assertNotContains($reportSudameriqueA, $reportsDelete);
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertContains($reportSudameriqueB, $reportsView);
        $this->assertNotContains($reportSudameriqueB, $reportsEdit);
        $this->assertNotContains($reportSudameriqueB, $reportsReport);
        $this->assertNotContains($reportSudameriqueB, $reportsDelete);

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSite, $reportsView);
        $this->assertNotContains($reportSite, $reportsEdit);
        $this->assertNotContains($reportSite, $reportsReport);
        $this->assertNotContains($reportSite, $reportsDelete);

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertNotContains($reportAnnecy, $reportsView);
        $this->assertNotContains($reportAnnecy, $reportsEdit);
        $this->assertNotContains($reportAnnecy, $reportsReport);
        $this->assertNotContains($reportAnnecy, $reportsDelete);
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertNotContains($reportChambery, $reportsView);
        $this->assertNotContains($reportChambery, $reportsEdit);
        $this->assertNotContains($reportChambery, $reportsReport);
        $this->assertNotContains($reportChambery, $reportsDelete);
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertNotContains($reportBerlin, $reportsView);
        $this->assertNotContains($reportBerlin, $reportsEdit);
        $this->assertNotContains($reportBerlin, $reportsReport);
        $this->assertNotContains($reportBerlin, $reportsDelete);
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertContains($reportLima, $reportsView);
        $this->assertNotContains($reportLima, $reportsEdit);
        $this->assertNotContains($reportLima, $reportsReport);
        $this->assertNotContains($reportLima, $reportsDelete);
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
        $queryView->aclFilter->action = DefaultAction::VIEW();
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = DefaultAction::EDIT();
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = DefaultAction::DELETE();
        $queryComment = new Core_Model_Query();
        $queryComment->aclFilter->enabled = true;
        $queryComment->aclFilter->user = $user;
        $queryComment->aclFilter->action = Orga_Action_Cell::COMMENT();
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = Orga_Action_Cell::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = DefaultAction::ALLOW();
        $queryReport = new Core_Model_Query();
        $queryReport->aclFilter->enabled = true;
        $queryReport->aclFilter->user = $user;
        $queryReport->aclFilter->action = Orga_Action_Report::EDIT();

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
        $cellsComment = Orga_Model_Cell::loadList($queryComment);
        $this->assertCount(7, $cellsComment);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(7, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(7, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(7, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertNotContains($cell0, $cellsView);
        $this->assertNotContains($cell0, $cellsComment);
        $this->assertNotContains($cell0, $cellsInput);
        $this->assertNotContains($cell0, $cellsEdit);
        $this->assertNotContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cellEuropeA, $cellsView);
        $this->assertNotContains($cellEuropeA, $cellsComment);
        $this->assertNotContains($cellEuropeA, $cellsInput);
        $this->assertNotContains($cellEuropeA, $cellsEdit);
        $this->assertNotContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cellEuropeB, $cellsView);
        $this->assertNotContains($cellEuropeB, $cellsComment);
        $this->assertNotContains($cellEuropeB, $cellsInput);
        $this->assertNotContains($cellEuropeB, $cellsEdit);
        $this->assertNotContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cellSudameriqueA, $cellsView);
        $this->assertNotContains($cellSudameriqueA, $cellsComment);
        $this->assertNotContains($cellSudameriqueA, $cellsInput);
        $this->assertNotContains($cellSudameriqueA, $cellsEdit);
        $this->assertNotContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cellSudameriqueB, $cellsView);
        $this->assertNotContains($cellSudameriqueB, $cellsComment);
        $this->assertNotContains($cellSudameriqueB, $cellsInput);
        $this->assertNotContains($cellSudameriqueB, $cellsEdit);
        $this->assertNotContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertContains($cellAnnecy, $cellsView);
        $this->assertContains($cellAnnecy, $cellsComment);
        $this->assertContains($cellAnnecy, $cellsInput);
        $this->assertContains($cellAnnecy, $cellsEdit);
        $this->assertContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertNotContains($cellChambery, $cellsView);
        $this->assertNotContains($cellChambery, $cellsComment);
        $this->assertNotContains($cellChambery, $cellsInput);
        $this->assertNotContains($cellChambery, $cellsEdit);
        $this->assertNotContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertNotContains($cellBerlin, $cellsView);
        $this->assertNotContains($cellBerlin, $cellsComment);
        $this->assertNotContains($cellBerlin, $cellsInput);
        $this->assertNotContains($cellBerlin, $cellsEdit);
        $this->assertNotContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertNotContains($cellLima, $cellsView);
        $this->assertNotContains($cellLima, $cellsComment);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cellLima, $cellsEdit);
        $this->assertNotContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertNotContains($cell2012, $cellsView);
        $this->assertNotContains($cell2012, $cellsComment);
        $this->assertNotContains($cell2012, $cellsInput);
        $this->assertNotContains($cell2012, $cellsEdit);
        $this->assertNotContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertNotContains($cell2013, $cellsView);
        $this->assertNotContains($cell2013, $cellsComment);
        $this->assertNotContains($cell2013, $cellsInput);
        $this->assertNotContains($cell2013, $cellsEdit);
        $this->assertNotContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012Energie, $cellsView);
        $this->assertNotContains($cell2012Energie, $cellsComment);
        $this->assertNotContains($cell2012Energie, $cellsInput);
        $this->assertNotContains($cell2012Energie, $cellsEdit);
        $this->assertNotContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012Transport, $cellsView);
        $this->assertNotContains($cell2012Transport, $cellsComment);
        $this->assertNotContains($cell2012Transport, $cellsInput);
        $this->assertNotContains($cell2012Transport, $cellsEdit);
        $this->assertNotContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013Energie, $cellsView);
        $this->assertNotContains($cell2013Energie, $cellsComment);
        $this->assertNotContains($cell2013Energie, $cellsInput);
        $this->assertNotContains($cell2013Energie, $cellsEdit);
        $this->assertNotContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013Transport, $cellsView);
        $this->assertNotContains($cell2013Transport, $cellsComment);
        $this->assertNotContains($cell2013Transport, $cellsInput);
        $this->assertNotContains($cell2013Transport, $cellsEdit);
        $this->assertNotContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2012EuropeA, $cellsView);
        $this->assertNotContains($cell2012EuropeA, $cellsComment);
        $this->assertNotContains($cell2012EuropeA, $cellsInput);
        $this->assertNotContains($cell2012EuropeA, $cellsEdit);
        $this->assertNotContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2012EuropeB, $cellsView);
        $this->assertNotContains($cell2012EuropeB, $cellsComment);
        $this->assertNotContains($cell2012EuropeB, $cellsInput);
        $this->assertNotContains($cell2012EuropeB, $cellsEdit);
        $this->assertNotContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2012SudameriqueA, $cellsView);
        $this->assertNotContains($cell2012SudameriqueA, $cellsComment);
        $this->assertNotContains($cell2012SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2012SudameriqueB, $cellsView);
        $this->assertNotContains($cell2012SudameriqueB, $cellsComment);
        $this->assertNotContains($cell2012SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2013EuropeA, $cellsView);
        $this->assertNotContains($cell2013EuropeA, $cellsComment);
        $this->assertNotContains($cell2013EuropeA, $cellsInput);
        $this->assertNotContains($cell2013EuropeA, $cellsEdit);
        $this->assertNotContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2013EuropeB, $cellsView);
        $this->assertNotContains($cell2013EuropeB, $cellsComment);
        $this->assertNotContains($cell2013EuropeB, $cellsInput);
        $this->assertNotContains($cell2013EuropeB, $cellsEdit);
        $this->assertNotContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2013SudameriqueA, $cellsView);
        $this->assertNotContains($cell2013SudameriqueA, $cellsComment);
        $this->assertNotContains($cell2013SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2013SudameriqueB, $cellsView);
        $this->assertNotContains($cell2013SudameriqueB, $cellsComment);
        $this->assertNotContains($cell2013SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertContains($cell2012Annecy, $cellsView);
        $this->assertContains($cell2012Annecy, $cellsComment);
        $this->assertContains($cell2012Annecy, $cellsInput);
        $this->assertContains($cell2012Annecy, $cellsEdit);
        $this->assertContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertNotContains($cell2012Chambery, $cellsView);
        $this->assertNotContains($cell2012Chambery, $cellsComment);
        $this->assertNotContains($cell2012Chambery, $cellsInput);
        $this->assertNotContains($cell2012Chambery, $cellsEdit);
        $this->assertNotContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2012Berlin, $cellsView);
        $this->assertNotContains($cell2012Berlin, $cellsComment);
        $this->assertNotContains($cell2012Berlin, $cellsInput);
        $this->assertNotContains($cell2012Berlin, $cellsEdit);
        $this->assertNotContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertNotContains($cell2012Lima, $cellsView);
        $this->assertNotContains($cell2012Lima, $cellsComment);
        $this->assertNotContains($cell2012Lima, $cellsInput);
        $this->assertNotContains($cell2012Lima, $cellsEdit);
        $this->assertNotContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertContains($cell2013Annecy, $cellsView);
        $this->assertContains($cell2013Annecy, $cellsComment);
        $this->assertContains($cell2013Annecy, $cellsInput);
        $this->assertContains($cell2013Annecy, $cellsEdit);
        $this->assertContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertNotContains($cell2013Chambery, $cellsView);
        $this->assertNotContains($cell2013Chambery, $cellsComment);
        $this->assertNotContains($cell2013Chambery, $cellsInput);
        $this->assertNotContains($cell2013Chambery, $cellsEdit);
        $this->assertNotContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2013Berlin, $cellsView);
        $this->assertNotContains($cell2013Berlin, $cellsComment);
        $this->assertNotContains($cell2013Berlin, $cellsInput);
        $this->assertNotContains($cell2013Berlin, $cellsEdit);
        $this->assertNotContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertNotContains($cell2013Lima, $cellsView);
        $this->assertNotContains($cell2013Lima, $cellsComment);
        $this->assertNotContains($cell2013Lima, $cellsInput);
        $this->assertNotContains($cell2013Lima, $cellsEdit);
        $this->assertNotContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertContains($cell2012AnnecyEnergie, $cellsComment);
        $this->assertContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsComment);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsComment);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012LimaEnergie, $cellsView);
        $this->assertNotContains($cell2012LimaEnergie, $cellsComment);
        $this->assertNotContains($cell2012LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertContains($cell2013AnnecyEnergie, $cellsComment);
        $this->assertContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsComment);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsComment);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013LimaEnergie, $cellsView);
        $this->assertNotContains($cell2013LimaEnergie, $cellsComment);
        $this->assertNotContains($cell2013LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2012AnnecyTransport, $cellsView);
        $this->assertContains($cell2012AnnecyTransport, $cellsComment);
        $this->assertContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsComment);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012BerlinTransport, $cellsView);
        $this->assertNotContains($cell2012BerlinTransport, $cellsComment);
        $this->assertNotContains($cell2012BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012LimaTransport, $cellsView);
        $this->assertNotContains($cell2012LimaTransport, $cellsComment);
        $this->assertNotContains($cell2012LimaTransport, $cellsInput);
        $this->assertNotContains($cell2012LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertContains($cell2013AnnecyTransport, $cellsView);
        $this->assertContains($cell2013AnnecyTransport, $cellsComment);
        $this->assertContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsComment);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013BerlinTransport, $cellsView);
        $this->assertNotContains($cell2013BerlinTransport, $cellsComment);
        $this->assertNotContains($cell2013BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013LimaTransport, $cellsView);
        $this->assertNotContains($cell2013LimaTransport, $cellsComment);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cell2013LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2013LimaTransport, $cellsAllow);

        $reportsView = DW_Model_Report::loadList($queryView);
        $this->assertCount(1, $reportsView);
        $reportsEdit = DW_Model_Report::loadList($queryEdit);
        $this->assertCount(1, $reportsEdit);
        $reportsReport = DW_Model_Report::loadList($queryReport);
        $this->assertCount(0, $reportsReport);
        $reportsDelete = DW_Model_Report::loadList($queryDelete);
        $this->assertCount(0, $reportsDelete);

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertNotContains($reportGlobale, $reportsView);
        $this->assertNotContains($reportGlobale, $reportsEdit);
        $this->assertNotContains($reportGlobale, $reportsReport);
        $this->assertNotContains($reportGlobale, $reportsDelete);

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertNotContains($reportCellGlobale, $reportsView);
        $this->assertNotContains($reportCellGlobale, $reportsEdit);
        $this->assertNotContains($reportCellGlobale, $reportsReport);
        $this->assertNotContains($reportCellGlobale, $reportsDelete);

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertNotContains($reportZoneMarque, $reportsView);
        $this->assertNotContains($reportZoneMarque, $reportsEdit);
        $this->assertNotContains($reportZoneMarque, $reportsReport);
        $this->assertNotContains($reportZoneMarque, $reportsDelete);

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertNotContains($reportEuropeA, $reportsView);
        $this->assertNotContains($reportEuropeA, $reportsEdit);
        $this->assertNotContains($reportEuropeA, $reportsReport);
        $this->assertNotContains($reportEuropeA, $reportsDelete);
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertNotContains($reportEuropeB, $reportsView);
        $this->assertNotContains($reportEuropeB, $reportsEdit);
        $this->assertNotContains($reportEuropeB, $reportsReport);
        $this->assertNotContains($reportEuropeB, $reportsDelete);
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSudameriqueA, $reportsView);
        $this->assertNotContains($reportSudameriqueA, $reportsEdit);
        $this->assertNotContains($reportSudameriqueA, $reportsReport);
        $this->assertNotContains($reportSudameriqueA, $reportsDelete);
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSudameriqueB, $reportsView);
        $this->assertNotContains($reportSudameriqueB, $reportsEdit);
        $this->assertNotContains($reportSudameriqueB, $reportsReport);
        $this->assertNotContains($reportSudameriqueB, $reportsDelete);

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSite, $reportsView);
        $this->assertNotContains($reportSite, $reportsEdit);
        $this->assertNotContains($reportSite, $reportsReport);
        $this->assertNotContains($reportSite, $reportsDelete);

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertContains($reportAnnecy, $reportsView);
        $this->assertContains($reportAnnecy, $reportsEdit);
        $this->assertNotContains($reportAnnecy, $reportsReport);
        $this->assertNotContains($reportAnnecy, $reportsDelete);
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertNotContains($reportChambery, $reportsView);
        $this->assertNotContains($reportChambery, $reportsEdit);
        $this->assertNotContains($reportChambery, $reportsReport);
        $this->assertNotContains($reportChambery, $reportsDelete);
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertNotContains($reportBerlin, $reportsView);
        $this->assertNotContains($reportBerlin, $reportsEdit);
        $this->assertNotContains($reportBerlin, $reportsReport);
        $this->assertNotContains($reportBerlin, $reportsDelete);
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertNotContains($reportLima, $reportsView);
        $this->assertNotContains($reportLima, $reportsEdit);
        $this->assertNotContains($reportLima, $reportsReport);
        $this->assertNotContains($reportLima, $reportsDelete);
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
        $queryView->aclFilter->action = DefaultAction::VIEW();
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = DefaultAction::EDIT();
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = DefaultAction::DELETE();
        $queryComment = new Core_Model_Query();
        $queryComment->aclFilter->enabled = true;
        $queryComment->aclFilter->user = $user;
        $queryComment->aclFilter->action = Orga_Action_Cell::COMMENT();
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = Orga_Action_Cell::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = DefaultAction::ALLOW();
        $queryReport = new Core_Model_Query();
        $queryReport->aclFilter->enabled = true;
        $queryReport->aclFilter->user = $user;
        $queryReport->aclFilter->action = Orga_Action_Report::EDIT();

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
        $cellsComment = Orga_Model_Cell::loadList($queryComment);
        $this->assertCount(7, $cellsComment);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(0, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(0, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(0, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertNotContains($cell0, $cellsView);
        $this->assertNotContains($cell0, $cellsComment);
        $this->assertNotContains($cell0, $cellsInput);
        $this->assertNotContains($cell0, $cellsEdit);
        $this->assertNotContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cellEuropeA, $cellsView);
        $this->assertNotContains($cellEuropeA, $cellsComment);
        $this->assertNotContains($cellEuropeA, $cellsInput);
        $this->assertNotContains($cellEuropeA, $cellsEdit);
        $this->assertNotContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cellEuropeB, $cellsView);
        $this->assertNotContains($cellEuropeB, $cellsComment);
        $this->assertNotContains($cellEuropeB, $cellsInput);
        $this->assertNotContains($cellEuropeB, $cellsEdit);
        $this->assertNotContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cellSudameriqueA, $cellsView);
        $this->assertNotContains($cellSudameriqueA, $cellsComment);
        $this->assertNotContains($cellSudameriqueA, $cellsInput);
        $this->assertNotContains($cellSudameriqueA, $cellsEdit);
        $this->assertNotContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cellSudameriqueB, $cellsView);
        $this->assertNotContains($cellSudameriqueB, $cellsComment);
        $this->assertNotContains($cellSudameriqueB, $cellsInput);
        $this->assertNotContains($cellSudameriqueB, $cellsEdit);
        $this->assertNotContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertNotContains($cellAnnecy, $cellsView);
        $this->assertNotContains($cellAnnecy, $cellsComment);
        $this->assertNotContains($cellAnnecy, $cellsInput);
        $this->assertNotContains($cellAnnecy, $cellsEdit);
        $this->assertNotContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertNotContains($cellChambery, $cellsView);
        $this->assertNotContains($cellChambery, $cellsComment);
        $this->assertNotContains($cellChambery, $cellsInput);
        $this->assertNotContains($cellChambery, $cellsEdit);
        $this->assertNotContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertContains($cellBerlin, $cellsView);
        $this->assertContains($cellBerlin, $cellsComment);
        $this->assertNotContains($cellBerlin, $cellsInput);
        $this->assertNotContains($cellBerlin, $cellsEdit);
        $this->assertNotContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertNotContains($cellLima, $cellsView);
        $this->assertNotContains($cellLima, $cellsComment);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cellLima, $cellsEdit);
        $this->assertNotContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertNotContains($cell2012, $cellsView);
        $this->assertNotContains($cell2012, $cellsComment);
        $this->assertNotContains($cell2012, $cellsInput);
        $this->assertNotContains($cell2012, $cellsEdit);
        $this->assertNotContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertNotContains($cell2013, $cellsView);
        $this->assertNotContains($cell2013, $cellsComment);
        $this->assertNotContains($cell2013, $cellsInput);
        $this->assertNotContains($cell2013, $cellsEdit);
        $this->assertNotContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012Energie, $cellsView);
        $this->assertNotContains($cell2012Energie, $cellsComment);
        $this->assertNotContains($cell2012Energie, $cellsInput);
        $this->assertNotContains($cell2012Energie, $cellsEdit);
        $this->assertNotContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012Transport, $cellsView);
        $this->assertNotContains($cell2012Transport, $cellsComment);
        $this->assertNotContains($cell2012Transport, $cellsInput);
        $this->assertNotContains($cell2012Transport, $cellsEdit);
        $this->assertNotContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013Energie, $cellsView);
        $this->assertNotContains($cell2013Energie, $cellsComment);
        $this->assertNotContains($cell2013Energie, $cellsInput);
        $this->assertNotContains($cell2013Energie, $cellsEdit);
        $this->assertNotContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013Transport, $cellsView);
        $this->assertNotContains($cell2013Transport, $cellsComment);
        $this->assertNotContains($cell2013Transport, $cellsInput);
        $this->assertNotContains($cell2013Transport, $cellsEdit);
        $this->assertNotContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2012EuropeA, $cellsView);
        $this->assertNotContains($cell2012EuropeA, $cellsComment);
        $this->assertNotContains($cell2012EuropeA, $cellsInput);
        $this->assertNotContains($cell2012EuropeA, $cellsEdit);
        $this->assertNotContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2012EuropeB, $cellsView);
        $this->assertNotContains($cell2012EuropeB, $cellsComment);
        $this->assertNotContains($cell2012EuropeB, $cellsInput);
        $this->assertNotContains($cell2012EuropeB, $cellsEdit);
        $this->assertNotContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2012SudameriqueA, $cellsView);
        $this->assertNotContains($cell2012SudameriqueA, $cellsComment);
        $this->assertNotContains($cell2012SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2012SudameriqueB, $cellsView);
        $this->assertNotContains($cell2012SudameriqueB, $cellsComment);
        $this->assertNotContains($cell2012SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2013EuropeA, $cellsView);
        $this->assertNotContains($cell2013EuropeA, $cellsComment);
        $this->assertNotContains($cell2013EuropeA, $cellsInput);
        $this->assertNotContains($cell2013EuropeA, $cellsEdit);
        $this->assertNotContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2013EuropeB, $cellsView);
        $this->assertNotContains($cell2013EuropeB, $cellsComment);
        $this->assertNotContains($cell2013EuropeB, $cellsInput);
        $this->assertNotContains($cell2013EuropeB, $cellsEdit);
        $this->assertNotContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2013SudameriqueA, $cellsView);
        $this->assertNotContains($cell2013SudameriqueA, $cellsComment);
        $this->assertNotContains($cell2013SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2013SudameriqueB, $cellsView);
        $this->assertNotContains($cell2013SudameriqueB, $cellsComment);
        $this->assertNotContains($cell2013SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertNotContains($cell2012Annecy, $cellsView);
        $this->assertNotContains($cell2012Annecy, $cellsComment);
        $this->assertNotContains($cell2012Annecy, $cellsInput);
        $this->assertNotContains($cell2012Annecy, $cellsEdit);
        $this->assertNotContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertNotContains($cell2012Chambery, $cellsView);
        $this->assertNotContains($cell2012Chambery, $cellsComment);
        $this->assertNotContains($cell2012Chambery, $cellsInput);
        $this->assertNotContains($cell2012Chambery, $cellsEdit);
        $this->assertNotContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertContains($cell2012Berlin, $cellsView);
        $this->assertContains($cell2012Berlin, $cellsComment);
        $this->assertNotContains($cell2012Berlin, $cellsInput);
        $this->assertNotContains($cell2012Berlin, $cellsEdit);
        $this->assertNotContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertNotContains($cell2012Lima, $cellsView);
        $this->assertNotContains($cell2012Lima, $cellsComment);
        $this->assertNotContains($cell2012Lima, $cellsInput);
        $this->assertNotContains($cell2012Lima, $cellsEdit);
        $this->assertNotContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertNotContains($cell2013Annecy, $cellsView);
        $this->assertNotContains($cell2013Annecy, $cellsComment);
        $this->assertNotContains($cell2013Annecy, $cellsInput);
        $this->assertNotContains($cell2013Annecy, $cellsEdit);
        $this->assertNotContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertNotContains($cell2013Chambery, $cellsView);
        $this->assertNotContains($cell2013Chambery, $cellsComment);
        $this->assertNotContains($cell2013Chambery, $cellsInput);
        $this->assertNotContains($cell2013Chambery, $cellsEdit);
        $this->assertNotContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertContains($cell2013Berlin, $cellsView);
        $this->assertContains($cell2013Berlin, $cellsComment);
        $this->assertNotContains($cell2013Berlin, $cellsInput);
        $this->assertNotContains($cell2013Berlin, $cellsEdit);
        $this->assertNotContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertNotContains($cell2013Lima, $cellsView);
        $this->assertNotContains($cell2013Lima, $cellsComment);
        $this->assertNotContains($cell2013Lima, $cellsInput);
        $this->assertNotContains($cell2013Lima, $cellsEdit);
        $this->assertNotContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsComment);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsComment);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012BerlinEnergie, $cellsView);
        $this->assertContains($cell2012BerlinEnergie, $cellsComment);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012LimaEnergie, $cellsView);
        $this->assertNotContains($cell2012LimaEnergie, $cellsComment);
        $this->assertNotContains($cell2012LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsComment);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsComment);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013BerlinEnergie, $cellsView);
        $this->assertContains($cell2013BerlinEnergie, $cellsComment);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013LimaEnergie, $cellsView);
        $this->assertNotContains($cell2013LimaEnergie, $cellsComment);
        $this->assertNotContains($cell2013LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsView);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsComment);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsComment);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertContains($cell2012BerlinTransport, $cellsView);
        $this->assertContains($cell2012BerlinTransport, $cellsComment);
        $this->assertNotContains($cell2012BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012LimaTransport, $cellsView);
        $this->assertNotContains($cell2012LimaTransport, $cellsComment);
        $this->assertNotContains($cell2012LimaTransport, $cellsInput);
        $this->assertNotContains($cell2012LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsView);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsComment);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsComment);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertContains($cell2013BerlinTransport, $cellsView);
        $this->assertContains($cell2013BerlinTransport, $cellsComment);
        $this->assertNotContains($cell2013BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013LimaTransport, $cellsView);
        $this->assertNotContains($cell2013LimaTransport, $cellsComment);
        $this->assertNotContains($cellLima, $cellsInput);
        $this->assertNotContains($cell2013LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2013LimaTransport, $cellsAllow);

        $reportsView = DW_Model_Report::loadList($queryView);
        $this->assertCount(1, $reportsView);
        $reportsEdit = DW_Model_Report::loadList($queryEdit);
        $this->assertCount(0, $reportsEdit);
        $reportsReport = DW_Model_Report::loadList($queryReport);
        $this->assertCount(0, $reportsReport);
        $reportsDelete = DW_Model_Report::loadList($queryDelete);
        $this->assertCount(0, $reportsDelete);

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertNotContains($reportGlobale, $reportsView);
        $this->assertNotContains($reportGlobale, $reportsEdit);
        $this->assertNotContains($reportGlobale, $reportsReport);
        $this->assertNotContains($reportGlobale, $reportsDelete);

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertNotContains($reportCellGlobale, $reportsView);
        $this->assertNotContains($reportCellGlobale, $reportsEdit);
        $this->assertNotContains($reportCellGlobale, $reportsReport);
        $this->assertNotContains($reportCellGlobale, $reportsDelete);

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertNotContains($reportZoneMarque, $reportsView);
        $this->assertNotContains($reportZoneMarque, $reportsEdit);
        $this->assertNotContains($reportZoneMarque, $reportsReport);
        $this->assertNotContains($reportZoneMarque, $reportsDelete);

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertNotContains($reportEuropeA, $reportsView);
        $this->assertNotContains($reportEuropeA, $reportsEdit);
        $this->assertNotContains($reportEuropeA, $reportsReport);
        $this->assertNotContains($reportEuropeA, $reportsDelete);
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertNotContains($reportEuropeB, $reportsView);
        $this->assertNotContains($reportEuropeB, $reportsEdit);
        $this->assertNotContains($reportEuropeB, $reportsReport);
        $this->assertNotContains($reportEuropeB, $reportsDelete);
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSudameriqueA, $reportsView);
        $this->assertNotContains($reportSudameriqueA, $reportsEdit);
        $this->assertNotContains($reportSudameriqueA, $reportsReport);
        $this->assertNotContains($reportSudameriqueA, $reportsDelete);
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSudameriqueB, $reportsView);
        $this->assertNotContains($reportSudameriqueB, $reportsEdit);
        $this->assertNotContains($reportSudameriqueB, $reportsReport);
        $this->assertNotContains($reportSudameriqueB, $reportsDelete);

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSite, $reportsView);
        $this->assertNotContains($reportSite, $reportsEdit);
        $this->assertNotContains($reportSite, $reportsReport);
        $this->assertNotContains($reportSite, $reportsDelete);

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertNotContains($reportAnnecy, $reportsView);
        $this->assertNotContains($reportAnnecy, $reportsEdit);
        $this->assertNotContains($reportAnnecy, $reportsReport);
        $this->assertNotContains($reportAnnecy, $reportsDelete);
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertNotContains($reportChambery, $reportsView);
        $this->assertNotContains($reportChambery, $reportsEdit);
        $this->assertNotContains($reportChambery, $reportsReport);
        $this->assertNotContains($reportChambery, $reportsDelete);
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertContains($reportBerlin, $reportsView);
        $this->assertNotContains($reportBerlin, $reportsEdit);
        $this->assertNotContains($reportBerlin, $reportsReport);
        $this->assertNotContains($reportBerlin, $reportsDelete);
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertNotContains($reportLima, $reportsView);
        $this->assertNotContains($reportLima, $reportsEdit);
        $this->assertNotContains($reportLima, $reportsReport);
        $this->assertNotContains($reportLima, $reportsDelete);
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
        $queryView->aclFilter->action = DefaultAction::VIEW();
        $queryEdit = new Core_Model_Query();
        $queryEdit->aclFilter->enabled = true;
        $queryEdit->aclFilter->user = $user;
        $queryEdit->aclFilter->action = DefaultAction::EDIT();
        $queryDelete = new Core_Model_Query();
        $queryDelete->aclFilter->enabled = true;
        $queryDelete->aclFilter->user = $user;
        $queryDelete->aclFilter->action = DefaultAction::DELETE();
        $queryComment = new Core_Model_Query();
        $queryComment->aclFilter->enabled = true;
        $queryComment->aclFilter->user = $user;
        $queryComment->aclFilter->action = Orga_Action_Cell::COMMENT();
        $queryInput = new Core_Model_Query();
        $queryInput->aclFilter->enabled = true;
        $queryInput->aclFilter->user = $user;
        $queryInput->aclFilter->action = Orga_Action_Cell::INPUT();
        $queryAllow = new Core_Model_Query();
        $queryAllow->aclFilter->enabled = true;
        $queryAllow->aclFilter->user = $user;
        $queryAllow->aclFilter->action = DefaultAction::ALLOW();
        $queryReport = new Core_Model_Query();
        $queryReport->aclFilter->enabled = true;
        $queryReport->aclFilter->user = $user;
        $queryReport->aclFilter->action = Orga_Action_Report::EDIT();

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
        $cellsComment = Orga_Model_Cell::loadList($queryComment);
        $this->assertCount(7, $cellsComment);
        $cellsInput = Orga_Model_Cell::loadList($queryInput);
        $this->assertCount(7, $cellsInput);
        $cellsEdit = Orga_Model_Cell::loadList($queryEdit);
        $this->assertCount(0, $cellsEdit);
        $cellsAllow = Orga_Model_Cell::loadList($queryAllow);
        $this->assertCount(0, $cellsAllow);

        // Cellules de la granularité global.
        $cell0 = $this->granularityGlobale->getCellByMembers([]);
        $this->assertNotContains($cell0, $cellsView);
        $this->assertNotContains($cell0, $cellsComment);
        $this->assertNotContains($cell0, $cellsInput);
        $this->assertNotContains($cell0, $cellsEdit);
        $this->assertNotContains($cell0, $cellsAllow);

        // Cellules de la granularité zonne marque.
        $cellEuropeA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cellEuropeA, $cellsView);
        $this->assertNotContains($cellEuropeA, $cellsComment);
        $this->assertNotContains($cellEuropeA, $cellsInput);
        $this->assertNotContains($cellEuropeA, $cellsEdit);
        $this->assertNotContains($cellEuropeA, $cellsAllow);
        $cellEuropeB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cellEuropeB, $cellsView);
        $this->assertNotContains($cellEuropeB, $cellsComment);
        $this->assertNotContains($cellEuropeB, $cellsInput);
        $this->assertNotContains($cellEuropeB, $cellsEdit);
        $this->assertNotContains($cellEuropeB, $cellsAllow);
        $cellSudameriqueA = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cellSudameriqueA, $cellsView);
        $this->assertNotContains($cellSudameriqueA, $cellsComment);
        $this->assertNotContains($cellSudameriqueA, $cellsInput);
        $this->assertNotContains($cellSudameriqueA, $cellsEdit);
        $this->assertNotContains($cellSudameriqueA, $cellsAllow);
        $cellSudameriqueB = $this->granularityZoneMarque->getCellByMembers([$this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cellSudameriqueB, $cellsView);
        $this->assertNotContains($cellSudameriqueB, $cellsComment);
        $this->assertNotContains($cellSudameriqueB, $cellsInput);
        $this->assertNotContains($cellSudameriqueB, $cellsEdit);
        $this->assertNotContains($cellSudameriqueB, $cellsAllow);

        // Cellules de la granularité site.
        $cellAnnecy = $this->granularitySite->getCellByMembers([$this->memberSiteAnnecy]);
        $this->assertNotContains($cellAnnecy, $cellsView);
        $this->assertNotContains($cellAnnecy, $cellsComment);
        $this->assertNotContains($cellAnnecy, $cellsInput);
        $this->assertNotContains($cellAnnecy, $cellsEdit);
        $this->assertNotContains($cellAnnecy, $cellsAllow);
        $cellChambery = $this->granularitySite->getCellByMembers([$this->memberSiteChambery]);
        $this->assertNotContains($cellChambery, $cellsView);
        $this->assertNotContains($cellChambery, $cellsComment);
        $this->assertNotContains($cellChambery, $cellsInput);
        $this->assertNotContains($cellChambery, $cellsEdit);
        $this->assertNotContains($cellChambery, $cellsAllow);
        $cellBerlin = $this->granularitySite->getCellByMembers([$this->memberSiteBerlin]);
        $this->assertNotContains($cellBerlin, $cellsView);
        $this->assertNotContains($cellBerlin, $cellsComment);
        $this->assertNotContains($cellBerlin, $cellsInput);
        $this->assertNotContains($cellBerlin, $cellsEdit);
        $this->assertNotContains($cellBerlin, $cellsAllow);
        $cellLima = $this->granularitySite->getCellByMembers([$this->memberSiteLima]);
        $this->assertContains($cellLima, $cellsView);
        $this->assertContains($cellLima, $cellsComment);
        $this->assertContains($cellLima, $cellsInput);
        $this->assertNotContains($cellLima, $cellsEdit);
        $this->assertNotContains($cellLima, $cellsAllow);

        // Cellules de la granularité année.
        $cell2012 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2012]);
        $this->assertNotContains($cell2012, $cellsView);
        $this->assertNotContains($cell2012, $cellsComment);
        $this->assertNotContains($cell2012, $cellsInput);
        $this->assertNotContains($cell2012, $cellsEdit);
        $this->assertNotContains($cell2012, $cellsAllow);
        $cell2013 = $this->granularityAnnee->getCellByMembers([$this->memberAnnee2013]);
        $this->assertNotContains($cell2013, $cellsView);
        $this->assertNotContains($cell2013, $cellsComment);
        $this->assertNotContains($cell2013, $cellsInput);
        $this->assertNotContains($cell2013, $cellsEdit);
        $this->assertNotContains($cell2013, $cellsAllow);

        // Cellules de la granularité année categorie.
        $cell2012Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012Energie, $cellsView);
        $this->assertNotContains($cell2012Energie, $cellsComment);
        $this->assertNotContains($cell2012Energie, $cellsInput);
        $this->assertNotContains($cell2012Energie, $cellsEdit);
        $this->assertNotContains($cell2012Energie, $cellsAllow);
        $cell2012Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012Transport, $cellsView);
        $this->assertNotContains($cell2012Transport, $cellsComment);
        $this->assertNotContains($cell2012Transport, $cellsInput);
        $this->assertNotContains($cell2012Transport, $cellsEdit);
        $this->assertNotContains($cell2012Transport, $cellsAllow);
        $cell2013Energie = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013Energie, $cellsView);
        $this->assertNotContains($cell2013Energie, $cellsComment);
        $this->assertNotContains($cell2013Energie, $cellsInput);
        $this->assertNotContains($cell2013Energie, $cellsEdit);
        $this->assertNotContains($cell2013Energie, $cellsAllow);
        $cell2013Transport = $this->granularityAnneeCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013Transport, $cellsView);
        $this->assertNotContains($cell2013Transport, $cellsComment);
        $this->assertNotContains($cell2013Transport, $cellsInput);
        $this->assertNotContains($cell2013Transport, $cellsEdit);
        $this->assertNotContains($cell2013Transport, $cellsAllow);

        // Cellules de la granularité année zonne marque.
        $cell2012EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2012EuropeA, $cellsView);
        $this->assertNotContains($cell2012EuropeA, $cellsComment);
        $this->assertNotContains($cell2012EuropeA, $cellsInput);
        $this->assertNotContains($cell2012EuropeA, $cellsEdit);
        $this->assertNotContains($cell2012EuropeA, $cellsAllow);
        $cell2012EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2012EuropeB, $cellsView);
        $this->assertNotContains($cell2012EuropeB, $cellsComment);
        $this->assertNotContains($cell2012EuropeB, $cellsInput);
        $this->assertNotContains($cell2012EuropeB, $cellsEdit);
        $this->assertNotContains($cell2012EuropeB, $cellsAllow);
        $cell2012SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2012SudameriqueA, $cellsView);
        $this->assertNotContains($cell2012SudameriqueA, $cellsComment);
        $this->assertNotContains($cell2012SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueA, $cellsAllow);
        $cell2012SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2012, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2012SudameriqueB, $cellsView);
        $this->assertNotContains($cell2012SudameriqueB, $cellsComment);
        $this->assertNotContains($cell2012SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2012SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2012SudameriqueB, $cellsAllow);
        $cell2013EuropeA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueA]);
        $this->assertNotContains($cell2013EuropeA, $cellsView);
        $this->assertNotContains($cell2013EuropeA, $cellsComment);
        $this->assertNotContains($cell2013EuropeA, $cellsInput);
        $this->assertNotContains($cell2013EuropeA, $cellsEdit);
        $this->assertNotContains($cell2013EuropeA, $cellsAllow);
        $cell2013EuropeB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneEurope, $this->memberMarqueB]);
        $this->assertNotContains($cell2013EuropeB, $cellsView);
        $this->assertNotContains($cell2013EuropeB, $cellsComment);
        $this->assertNotContains($cell2013EuropeB, $cellsInput);
        $this->assertNotContains($cell2013EuropeB, $cellsEdit);
        $this->assertNotContains($cell2013EuropeB, $cellsAllow);
        $cell2013SudameriqueA = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueA]);
        $this->assertNotContains($cell2013SudameriqueA, $cellsView);
        $this->assertNotContains($cell2013SudameriqueA, $cellsComment);
        $this->assertNotContains($cell2013SudameriqueA, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueA, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueA, $cellsAllow);
        $cell2013SudameriqueB = $this->granularityAnneeZoneMarque->getCellByMembers([$this->memberAnnee2013, $this->memberZoneSudamerique, $this->memberMarqueB]);
        $this->assertNotContains($cell2013SudameriqueB, $cellsView);
        $this->assertNotContains($cell2013SudameriqueB, $cellsComment);
        $this->assertNotContains($cell2013SudameriqueB, $cellsInput);
        $this->assertNotContains($cell2013SudameriqueB, $cellsEdit);
        $this->assertNotContains($cell2013SudameriqueB, $cellsAllow);

        // Cellules de la granularité année site.
        $cell2012Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy]);
        $this->assertNotContains($cell2012Annecy, $cellsView);
        $this->assertNotContains($cell2012Annecy, $cellsComment);
        $this->assertNotContains($cell2012Annecy, $cellsInput);
        $this->assertNotContains($cell2012Annecy, $cellsEdit);
        $this->assertNotContains($cell2012Annecy, $cellsAllow);
        $cell2012Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery]);
        $this->assertNotContains($cell2012Chambery, $cellsView);
        $this->assertNotContains($cell2012Chambery, $cellsComment);
        $this->assertNotContains($cell2012Chambery, $cellsInput);
        $this->assertNotContains($cell2012Chambery, $cellsEdit);
        $this->assertNotContains($cell2012Chambery, $cellsAllow);
        $cell2012Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2012Berlin, $cellsView);
        $this->assertNotContains($cell2012Berlin, $cellsComment);
        $this->assertNotContains($cell2012Berlin, $cellsInput);
        $this->assertNotContains($cell2012Berlin, $cellsEdit);
        $this->assertNotContains($cell2012Berlin, $cellsAllow);
        $cell2012Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima]);
        $this->assertContains($cell2012Lima, $cellsView);
        $this->assertContains($cell2012Lima, $cellsComment);
        $this->assertContains($cell2012Lima, $cellsInput);
        $this->assertNotContains($cell2012Lima, $cellsEdit);
        $this->assertNotContains($cell2012Lima, $cellsAllow);
        $cell2013Annecy = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy]);
        $this->assertNotContains($cell2013Annecy, $cellsView);
        $this->assertNotContains($cell2013Annecy, $cellsComment);
        $this->assertNotContains($cell2013Annecy, $cellsInput);
        $this->assertNotContains($cell2013Annecy, $cellsEdit);
        $this->assertNotContains($cell2013Annecy, $cellsAllow);
        $cell2013Chambery = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery]);
        $this->assertNotContains($cell2013Chambery, $cellsView);
        $this->assertNotContains($cell2013Chambery, $cellsComment);
        $this->assertNotContains($cell2013Chambery, $cellsInput);
        $this->assertNotContains($cell2013Chambery, $cellsEdit);
        $this->assertNotContains($cell2013Chambery, $cellsAllow);
        $cell2013Berlin = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin]);
        $this->assertNotContains($cell2013Berlin, $cellsView);
        $this->assertNotContains($cell2013Berlin, $cellsComment);
        $this->assertNotContains($cell2013Berlin, $cellsInput);
        $this->assertNotContains($cell2013Berlin, $cellsEdit);
        $this->assertNotContains($cell2013Berlin, $cellsAllow);
        $cell2013Lima = $this->granularityAnneeSite->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima]);
        $this->assertContains($cell2013Lima, $cellsView);
        $this->assertContains($cell2013Lima, $cellsComment);
        $this->assertContains($cell2013Lima, $cellsInput);
        $this->assertNotContains($cell2013Lima, $cellsEdit);
        $this->assertNotContains($cell2013Lima, $cellsAllow);

        // Cellules de la granularité année site categorie.
        $cell2012AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsView);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsComment);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyEnergie, $cellsAllow);
        $cell2012ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsComment);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyEnergie, $cellsAllow);
        $cell2012BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsComment);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2012BerlinEnergie, $cellsAllow);
        $cell2012LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2012LimaEnergie, $cellsView);
        $this->assertContains($cell2012LimaEnergie, $cellsComment);
        $this->assertContains($cell2012LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2012LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2012LimaEnergie, $cellsAllow);
        $cell2013AnnecyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsView);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsComment);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsInput);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyEnergie, $cellsAllow);
        $cell2013ChamberyEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsView);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsComment);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsInput);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyEnergie, $cellsAllow);
        $cell2013BerlinEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieEnergie]);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsView);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsComment);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsInput);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsEdit);
        $this->assertNotContains($cell2013BerlinEnergie, $cellsAllow);
        $cell2013LimaEnergie = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieEnergie]);
        $this->assertContains($cell2013LimaEnergie, $cellsView);
        $this->assertContains($cell2013LimaEnergie, $cellsComment);
        $this->assertContains($cell2013LimaEnergie, $cellsInput);
        $this->assertNotContains($cell2013LimaEnergie, $cellsEdit);
        $this->assertNotContains($cell2013LimaEnergie, $cellsAllow);
        $cell2012AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsView);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsComment);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2012AnnecyTransport, $cellsAllow);
        $cell2012ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsComment);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2012ChamberyTransport, $cellsAllow);
        $cell2012BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2012BerlinTransport, $cellsView);
        $this->assertNotContains($cell2012BerlinTransport, $cellsComment);
        $this->assertNotContains($cell2012BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2012BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2012BerlinTransport, $cellsAllow);
        $cell2012LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2012, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2012LimaTransport, $cellsView);
        $this->assertContains($cell2012LimaTransport, $cellsComment);
        $this->assertContains($cell2012LimaTransport, $cellsInput);
        $this->assertNotContains($cell2012LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2012LimaTransport, $cellsAllow);
        $cell2013AnnecyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteAnnecy, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsView);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsComment);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsInput);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsEdit);
        $this->assertNotContains($cell2013AnnecyTransport, $cellsAllow);
        $cell2013ChamberyTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteChambery, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsView);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsComment);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsInput);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsEdit);
        $this->assertNotContains($cell2013ChamberyTransport, $cellsAllow);
        $cell2013BerlinTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteBerlin, $this->memberCategorieTransport]);
        $this->assertNotContains($cell2013BerlinTransport, $cellsView);
        $this->assertNotContains($cell2013BerlinTransport, $cellsComment);
        $this->assertNotContains($cell2013BerlinTransport, $cellsInput);
        $this->assertNotContains($cell2013BerlinTransport, $cellsEdit);
        $this->assertNotContains($cell2013BerlinTransport, $cellsAllow);
        $cell2013LimaTransport = $this->granularityAnneeSiteCategorie->getCellByMembers([$this->memberAnnee2013, $this->memberSiteLima, $this->memberCategorieTransport]);
        $this->assertContains($cell2013LimaTransport, $cellsView);
        $this->assertContains($cell2013LimaTransport, $cellsComment);
        $this->assertContains($cellLima, $cellsInput);
        $this->assertNotContains($cell2013LimaTransport, $cellsEdit);
        $this->assertNotContains($cell2013LimaTransport, $cellsAllow);

        $reportsView = DW_Model_Report::loadList($queryView);
        $this->assertCount(1, $reportsView);
        $reportsEdit = DW_Model_Report::loadList($queryEdit);
        $this->assertCount(0, $reportsEdit);
        $reportsReport = DW_Model_Report::loadList($queryReport);
        $this->assertCount(0, $reportsReport);
        $reportsDelete = DW_Model_Report::loadList($queryDelete);
        $this->assertCount(0, $reportsDelete);

        // Report granularité globale.
        $reportGlobale = $this->granularityGlobale->getDWCube()->getReports()[0];
        $this->assertNotContains($reportGlobale, $reportsView);
        $this->assertNotContains($reportGlobale, $reportsEdit);
        $this->assertNotContains($reportGlobale, $reportsReport);
        $this->assertNotContains($reportGlobale, $reportsDelete);

        $reportCellGlobale = $cell0->getDWCube()->getReports()[0];
        $this->assertNotContains($reportCellGlobale, $reportsView);
        $this->assertNotContains($reportCellGlobale, $reportsEdit);
        $this->assertNotContains($reportCellGlobale, $reportsReport);
        $this->assertNotContains($reportCellGlobale, $reportsDelete);

        // Report granularité zone marque.
        $reportZoneMarque = $this->granularityZoneMarque->getDWCube()->getReports()[0];
        $this->assertNotContains($reportZoneMarque, $reportsView);
        $this->assertNotContains($reportZoneMarque, $reportsEdit);
        $this->assertNotContains($reportZoneMarque, $reportsReport);
        $this->assertNotContains($reportZoneMarque, $reportsDelete);

        $reportEuropeA = $cellEuropeA->getDWCube()->getReports()[0];
        $this->assertNotContains($reportEuropeA, $reportsView);
        $this->assertNotContains($reportEuropeA, $reportsEdit);
        $this->assertNotContains($reportEuropeA, $reportsReport);
        $this->assertNotContains($reportEuropeA, $reportsDelete);
        $reportEuropeB = $cellEuropeB->getDWCube()->getReports()[0];
        $this->assertNotContains($reportEuropeB, $reportsView);
        $this->assertNotContains($reportEuropeB, $reportsEdit);
        $this->assertNotContains($reportEuropeB, $reportsReport);
        $this->assertNotContains($reportEuropeB, $reportsDelete);
        $reportSudameriqueA = $cellSudameriqueA->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSudameriqueA, $reportsView);
        $this->assertNotContains($reportSudameriqueA, $reportsEdit);
        $this->assertNotContains($reportSudameriqueA, $reportsReport);
        $this->assertNotContains($reportSudameriqueA, $reportsDelete);
        $reportSudameriqueB = $cellSudameriqueB->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSudameriqueB, $reportsView);
        $this->assertNotContains($reportSudameriqueB, $reportsEdit);
        $this->assertNotContains($reportSudameriqueB, $reportsReport);
        $this->assertNotContains($reportSudameriqueB, $reportsDelete);

        // Report granularité site.
        $reportSite = $this->granularitySite->getDWCube()->getReports()[0];
        $this->assertNotContains($reportSite, $reportsView);
        $this->assertNotContains($reportSite, $reportsEdit);
        $this->assertNotContains($reportSite, $reportsReport);
        $this->assertNotContains($reportSite, $reportsDelete);

        $reportAnnecy = $cellAnnecy->getDWCube()->getReports()[0];
        $this->assertNotContains($reportAnnecy, $reportsView);
        $this->assertNotContains($reportAnnecy, $reportsEdit);
        $this->assertNotContains($reportAnnecy, $reportsReport);
        $this->assertNotContains($reportAnnecy, $reportsDelete);
        $reportChambery = $cellChambery->getDWCube()->getReports()[0];
        $this->assertNotContains($reportChambery, $reportsView);
        $this->assertNotContains($reportChambery, $reportsEdit);
        $this->assertNotContains($reportChambery, $reportsReport);
        $this->assertNotContains($reportChambery, $reportsDelete);
        $reportBerlin = $cellBerlin->getDWCube()->getReports()[0];
        $this->assertNotContains($reportBerlin, $reportsView);
        $this->assertNotContains($reportBerlin, $reportsEdit);
        $this->assertNotContains($reportBerlin, $reportsReport);
        $this->assertNotContains($reportBerlin, $reportsDelete);
        $reportLima = $cellLima->getDWCube()->getReports()[0];
        $this->assertContains($reportLima, $reportsView);
        $this->assertNotContains($reportLima, $reportsEdit);
        $this->assertNotContains($reportLima, $reportsReport);
        $this->assertNotContains($reportLima, $reportsDelete);
    }

    /**
     *
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->userService->deleteUser($this->organizationAdministrator);
        $this->userService->deleteUser($this->globaleCellAdministrator);
        $this->userService->deleteUser($this->europeaCellContributor);
        $this->userService->deleteUser($this->sudameriquebCellObserver);
        $this->userService->deleteUser($this->annecyCellAdministrator);
        $this->userService->deleteUser($this->berlinCellObserver);
        $this->userService->deleteUser($this->limaCellContributor);

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->organization = Orga_Model_Organization::load($this->organization->getId());
        foreach ($this->organization->getGranularities() as $granularity) {
            $granularity->delete();
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->organization = Orga_Model_Organization::load($this->organization->getId());
        $this->organization->delete();

        $this->entityManager->flush();
    }

    /**
     * Fonction appelee une fois, apres tous les tests
     */
    public static function tearDownAfterClass()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $entityManagers['default'];

        /** @var \DI\Container $container */
        $container = Zend_Registry::get('container');

        /** @var Orga_Service_ACLManager $aclManagerService */
        $aclManagerService = $container->get('Orga_Service_ACLManager');
        $entityManager->getEventManager()->removeEventListener(
            [Doctrine\ORM\Events::onFlush, Doctrine\ORM\Events::postFlush],
            $aclManagerService
        );

        // Vérification qu'il ne reste aucun User en base, sinon suppression !
        if (User::countTotal() > 0) {
            echo PHP_EOL . 'Des User_User restants ont été trouvé après les tests, suppression en cours !';
            foreach (User::loadList() as $user) {
                $user->delete();
            }
            $entityManager->flush();
        }

        // Vérification qu'il ne reste aucun Orga_Model_Cell en base, sinon suppression !
        if (Orga_Model_Cell::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Cell restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Cell::loadList() as $cell) {
                $cell->delete();
            }
            $entityManager->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Granularity en base, sinon suppression !
        if (Orga_Model_Granularity::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Granularity restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Granularity::loadList() as $granularity) {
                $granularity->delete();
            }
            $entityManager->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Member en base, sinon suppression !
        if (Orga_Model_Member::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Member restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Member::loadList() as $member) {
                $member->delete();
            }
            $entityManager->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Axis en base, sinon suppression !
        if (Orga_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Axis restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            $entityManager->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Organization en base, sinon suppression !
        if (Orga_Model_Organization::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Organization restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Organization::loadList() as $organization) {
                $organization->delete();
            }
            $entityManager->flush();
        }
    }

}