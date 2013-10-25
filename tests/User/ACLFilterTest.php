<?php

use User\Domain\ACL\Action\DefaultAction;
use User\Domain\ACL\Authorization;
use User\Domain\ACL\Resource;
use User\Domain\ACL\Resource\EntityResource;
use User\Domain\ACL\Role;
use User\Domain\ACL\SecurityIdentity;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\ACLFilterService;
use User\Domain\User;

class ACLFilterTest extends Core_Test_TestCase
{
    /**
     * @var ACLService
     */
    protected $aclService;

    /**
     * @var ACLFilterService
     */
    protected $cacheService;

    /**
     * @var User
     */
    protected $testUser;

    /**
     * @var User
     */
    protected $testAdmin;
    /**
     * @var Resource
     */
    protected $allUsersResource;
    /**
     * @var Resource
     */
    protected $invitedUsersResource;
    /**
     * @var Resource
     */
    protected $basicUsersResource;
    /**
     * @var Resource
     */
    protected $adminUsersResource;
    /**
     * @var Resource
     */
    protected $testUserResource;
    /**
     * @var Resource
     */
    protected $testAdminResource;

    /**
     * Invité n'a accès à rien
     * @var Role
     */
    protected $roleInvited;
    /**
     * Utilisateur a accès à ses préférences seulement
     * @var Role
     */
    protected $roleBasic;
    /**
     * Admin a accès à toute les préférences
     * @var Role
     */
    protected $roleAdmin;

    /**
     * @var Authorization
     */
    protected $adminAuthorization;


    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        /** @var \DI\Container $container */
        $container = Zend_Registry::get('container');
        /** @var ACLFilterService $aclFilterService */
        $aclFilterService = $container->get(ACLFilterService::class);

        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        $aclFilterService->clean();
        $aclFilterService->enabled = false;
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Authorization::loadList() as $o) {
            $o->delete();
        }
        foreach (Resource::loadList() as $o) {
            $o->delete();
        }
        foreach (SecurityIdentity::loadList() as $o) {
            $o->delete();
        }
        $entityManager->flush();
    }

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public function setUp()
    {
        parent::setUp();
        // Service des ACL
        $this->aclService = $this->get(ACLService::class);
        $this->cacheService = $this->get(ACLFilterService::class);
        $this->cacheService->enabled = true;
        try {
            // Création du role invité
            $this->roleInvited = new Role();
            $this->roleInvited->setRef('anonymous');
            $this->roleInvited->setName('Anonymous');
            $this->roleInvited->save();
            // Création du role utilisateur bsique
            $this->roleBasic = new Role();
            $this->roleBasic->setRef('basic');
            $this->roleBasic->setName('Basic');
            $this->roleBasic->save();
            // Création du role admin
            $this->roleAdmin = new Role();
            $this->roleAdmin->setRef('admin');
            $this->roleAdmin->setName('Admin');
            $this->roleAdmin->save();

            $this->entityManager->flush();

            // Création d'un utilisateur
            $this->testUser = new User();
            $this->testUser->setEmail('test');
            $this->testUser->setPassword('test');
            $this->testUser->addRole($this->roleBasic);
            $this->testUser->save();

            // Création d'un admin
            $this->testAdmin = new User();
            $this->testAdmin->setEmail('admin');
            $this->testAdmin->setPassword('admin');
            $this->testAdmin->addRole($this->roleAdmin);
            $this->testAdmin->save();

            $this->entityManager->flush();

            // Crée les ressources
            $this->testUserResource = new EntityResource();
            $this->testUserResource->setEntity($this->testUser);
            $this->testUserResource->save();
            $this->testAdminResource = new EntityResource();
            $this->testAdminResource->setEntity($this->testAdmin);
            $this->testAdminResource->save();
            $this->allUsersResource = new EntityResource();
            $this->allUsersResource->setEntityName(User::class);
            $this->allUsersResource->save();
            $this->invitedUsersResource = new EntityResource();
            $this->invitedUsersResource->setEntity($this->roleInvited);
            $this->invitedUsersResource->save();
            $this->basicUsersResource = new EntityResource();
            $this->basicUsersResource->setEntity($this->roleBasic);
            $this->basicUsersResource->save();
            $this->adminUsersResource = new EntityResource();
            $this->adminUsersResource->setEntity($this->roleAdmin);
            $this->adminUsersResource->save();

            $this->entityManager->flush();

            // Création du privilège donnant l'accès à admin sur les préférences de tous les utilisateurs
            $this->aclService->allow($this->roleAdmin, DefaultAction::VIEW(), $this->basicUsersResource);

            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    /**
     * On trouve des utilisateurs sans le filtre
     */
    function testWithoutFilter()
    {
        $this->assertEquals(2, count(User::loadList()));
    }

    /**
     * Teste le filtre
     *
     * Admin voit des utilisateurs en filtrant avec les ACL
     */
    function testWithFilterAllowed()
    {
        $query = new Core_Model_Query();
        $query->aclFilter->enabled = true;
        $query->aclFilter->user = $this->testAdmin;
        $query->aclFilter->action = DefaultAction::VIEW();
        $this->assertCount(1, User::loadList($query));
    }

    /**
     * Teste le filtre
     *
     * Utilisateur ne voit pas d'utilisateurs en filtrant avec les ACL
     */
    function testWithFilterForbidden()
    {
        $query = new Core_Model_Query();
        $query->aclFilter->enabled = true;
        $query->aclFilter->user = $this->testUser;
        $query->aclFilter->action = DefaultAction::VIEW();
        $this->assertCount(0, User::loadList($query));
    }

    /**
     * Méthode appelée à la fin des test
     * Suppression de warning de complexité
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function tearDown()
    {
        parent::tearDown();
        try {
            //Suppression des objets crées pour les tests
            $this->aclService->disallow($this->roleAdmin, DefaultAction::VIEW(), $this->basicUsersResource);
            if ($this->adminUsersResource) {
                $this->adminUsersResource->delete();
            }
            if ($this->basicUsersResource) {
                $this->basicUsersResource->delete();
            }
            if ($this->invitedUsersResource) {
                $this->invitedUsersResource->delete();
            }
            if ($this->allUsersResource) {
                $this->allUsersResource->delete();
            }
            if ($this->testAdminResource) {
                $this->testAdminResource->delete();
            }
            if ($this->testUserResource) {
                $this->testUserResource->delete();
            }
            if ($this->testUser) {
                $this->testUser->delete();
            }
            if ($this->testAdmin) {
                $this->testAdmin->delete();
            }
            if ($this->roleAdmin) {
                $this->roleAdmin->delete();
            }
            if ($this->roleBasic) {
                $this->roleBasic->delete();
            }
            if ($this->roleInvited) {
                $this->roleInvited->delete();
            }

            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

}
