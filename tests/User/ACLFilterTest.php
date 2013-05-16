<?php
/**
 * @package User
 */

/**
 * @package User
 */
class ACLFilterTest extends Core_Test_TestCase
{

    /**
     * @var User_Service_ACL
     */
    protected $aclService;

    /**
     * @var User_Service_ACLFilter
     */
    protected $cacheService;

    /**
     * @var User_Model_User
     */
    protected $testUser;

    /**
     * @var User_Model_User
     */
    protected $testAdmin;
    /**
     * @var User_Model_Resource
     */
    protected $allUsersResource;
    /**
     * @var User_Model_Resource
     */
    protected $invitedUsersResource;
    /**
     * @var User_Model_Resource
     */
    protected $basicUsersResource;
    /**
     * @var User_Model_Resource
     */
    protected $adminUsersResource;
    /**
     * @var User_Model_Resource
     */
    protected $testUserResource;
    /**
     * @var User_Model_Resource
     */
    protected $testAdminResource;

    /**
     * Invité n'a accès à rien
     * @var User_Model_Role
     */
    protected $roleInvited;
    /**
     * Utilisateur a accès à ses préférences seulement
     * @var User_Model_Role
     */
    protected $roleBasic;
    /**
     * Admin a accès à toute les préférences
     * @var User_Model_Role
     */
    protected $roleAdmin;

    /**
     * @var User_Model_Authorization
     */
    protected $adminAuthorization;


    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        User_Service_ACLFilter::getInstance()->clean();
        User_Service_ACLFilter::getInstance()->enabled = false;
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (User_Model_Authorization::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_Resource::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_SecurityIdentity::loadList() as $o) {
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
        $this->aclService = User_Service_ACL::getInstance();
        $this->cacheService = User_Service_ACLFilter::getInstance();
        $this->cacheService->enabled = true;
        try {
            // Création du role invité
            $this->roleInvited = new User_Model_Role();
            $this->roleInvited->setRef('anonymous');
            $this->roleInvited->setName('Anonymous');
            $this->roleInvited->save();
            // Création du role utilisateur bsique
            $this->roleBasic = new User_Model_Role();
            $this->roleBasic->setRef('basic');
            $this->roleBasic->setName('Basic');
            $this->roleBasic->save();
            // Création du role admin
            $this->roleAdmin = new User_Model_Role();
            $this->roleAdmin->setRef('admin');
            $this->roleAdmin->setName('Admin');
            $this->roleAdmin->save();

            $this->entityManager->flush();

            // Création d'un utilisateur
            $this->testUser = new User_Model_User();
            $this->testUser->setEmail('test');
            $this->testUser->setPassword('test');
            $this->testUser->addRole($this->roleBasic);
            $this->testUser->save();

            // Création d'un admin
            $this->testAdmin = new User_Model_User();
            $this->testAdmin->setEmail('admin');
            $this->testAdmin->setPassword('admin');
            $this->testAdmin->addRole($this->roleAdmin);
            $this->testAdmin->save();

            $this->entityManager->flush();

            // Crée les ressources
            $this->testUserResource = new User_Model_Resource_Entity();
            $this->testUserResource->setEntity($this->testUser);
            $this->testUserResource->save();
            $this->testAdminResource = new User_Model_Resource_Entity();
            $this->testAdminResource->setEntity($this->testAdmin);
            $this->testAdminResource->save();
            $this->allUsersResource = new User_Model_Resource_Entity();
            $this->allUsersResource->setEntityName('User_Model_User');
            $this->allUsersResource->save();
            $this->invitedUsersResource = new User_Model_Resource_Entity();
            $this->invitedUsersResource->setEntity($this->roleInvited);
            $this->invitedUsersResource->save();
            $this->basicUsersResource = new User_Model_Resource_Entity();
            $this->basicUsersResource->setEntity($this->roleBasic);
            $this->basicUsersResource->save();
            $this->adminUsersResource = new User_Model_Resource_Entity();
            $this->adminUsersResource->setEntity($this->roleAdmin);
            $this->adminUsersResource->save();

            $this->entityManager->flush();

            // Création du privilège donnant l'accès à admin sur les préférences de tous les utilisateurs
            $this->aclService->allow($this->roleAdmin, User_Model_Action_Default::VIEW(), $this->basicUsersResource);

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
        $this->assertEquals(2, count(User_Model_User::loadList()));
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
        $query->aclFilter->action = User_Model_Action_Default::VIEW();
        $this->assertCount(1, User_Model_User::loadList($query));
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
        $query->aclFilter->action = User_Model_Action_Default::VIEW();
        $this->assertCount(0, User_Model_User::loadList($query));
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
            $this->aclService->disallow($this->roleAdmin, User_Model_Action_Default::VIEW(), $this->basicUsersResource);
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
