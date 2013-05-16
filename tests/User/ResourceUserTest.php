<?php
/**
 * @package    User
 * @subpackage Test
 */

/**
 * Test de Ressource_Utilisateur
 * @package    User
 * @subpackage Test
 */
class ResourceUserTest extends Core_Test_TestCase
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
    // Invité n'a accès à rien
    protected $roleInvited;
    // Utilisateur a accès à ses préférences seulement
    protected $roleBasic;
    // Admin a accès à toute les préférences
    protected $roleAdmin;

    /**
     * @var User_Model_Authorization
     */
    protected $adminAuthorization;


    public static function setUpBeforeClass()
    {
        User_Service_ACLFilter::getInstance()->enabled = false;
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        $entityManagers = Zend_Registry::get('EntityManagers');
        foreach (User_Model_Authorization::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_Resource::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_Role::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_User::loadList() as $o) {
            $o->delete();
        }
        $entityManagers['default']->flush();
    }

    public function setUp()
    {
        parent::setUp();

        // Service des ACL
        $this->aclService = User_Service_ACL::getInstance();
        // Service de cache
        $this->cacheService = User_Service_ACLFilter::getInstance();
        $this->cacheService->enabled = false;

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
            $this->testUser->setPassword('test');
            $this->testUser->setEmail('test');
            $this->testUser->enable();
            $this->testUser->setLastName('Utilisateur de test');
            $this->testUser->addRole($this->roleBasic);
            $this->testUser->save();

            $this->entityManager->flush();

            // Crée les ressources
            $this->testUserResource = new User_Model_Resource_Entity();
            $this->testUserResource->setEntity($this->testUser);
            $this->testUserResource->save();
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
            $this->aclService->allow($this->roleAdmin, User_Model_Action_Default::VIEW(), $this->allUsersResource);

            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    /**
     * Test de loadByEntity avec un utilisateur
     */
    public function testLoadByEntity()
    {
        $resource = User_Model_Resource_Entity::loadByEntity($this->testUser);
        $this->assertSame($this->testUserResource, $resource);
        $this->assertSame($this->testUser, $resource->getEntity());
    }

    /**
     * Test de loadByUser sans utilisateur : doit renvoyer
     * la ressource "Tous les utilisateurs"
     */
    public function testLoadByEntityName()
    {
        $resource = User_Model_Resource_Entity::loadByEntityName('User_Model_User');
        $this->assertSame($this->allUsersResource, $resource);
        $this->assertEquals('User_Model_User', $resource->getEntityName());
    }

    /**
     * Test de loadByRole
     */
    public function testLoadByRole()
    {
        $resource = User_Model_Resource_Entity::loadByEntity($this->roleInvited);
        $this->assertTrue($resource instanceof User_Model_Resource);
        $this->assertEquals($this->invitedUsersResource->getId(), $resource->getId());
        $this->assertSame($this->roleInvited, $resource->getEntity());
    }

    /**
     * Teste l'accès refusé pour un invité
     */
    public function testAnonymous()
    {
        $access = $this->aclService->isAllowed($this->roleInvited,
                                               User_Model_Action_Default::VIEW(),
                                               $this->allUsersResource);
        $this->assertFalse($access);
    }

    /**
     * Test d'accès aux préférences d'un autre
     */
    public function testDifferentUser()
    {
        $user2 = UserTest::generateObject();
        $user2->addRole($this->roleBasic);
        $user2->save();
        $this->entityManager->flush();

        $access = $this->aclService->isAllowed($user2, User_Model_Action_Default::VIEW(), $this->testUserResource);
        $this->assertFalse($access);

        UserTest::deleteObject($user2);
    }

    /**
     * Test du privilege d'accès admin
     */
    public function testAdminOnAllUsers()
    {
        // Accès aux préférences de tous les utilisateurs
        $access = $this->aclService->isAllowed($this->roleAdmin,
                                               User_Model_Action_Default::VIEW(),
                                               $this->allUsersResource);
        $this->assertTrue($access);
    }

    /**
     * Test du privilege d'accès admin
     */
    public function testAdminOnSpecificUser()
    {
        // Accès aux préférences de l'utilisateur de test
        $access = $this->aclService->isAllowed($this->roleAdmin,
                                               User_Model_Action_Default::VIEW(),
                                               $this->testUserResource);
        $this->assertTrue($access);
    }

    /**
     * Test du privilege d'accès admin
     */
    public function testAdminOnAllBasicUsers()
    {
        // Accès aux préférences de l'utilisateur de test
        $access = $this->aclService->isAllowed($this->roleAdmin,
                                               User_Model_Action_Default::VIEW(),
                                               $this->basicUsersResource);
        $this->assertTrue($access);
    }

    /**
     * Teste les privilèges qui s'appliquent à tous les utilisateurs d'un role
     * Test simple : test direct du privilège
     */
    public function testDroitsUtilisateursDeRole1()
    {
        // Création du privilège donnant l'accès à utilisateur sur les préférences des invités
        $this->aclService->allow($this->roleBasic, User_Model_Action_Default::VIEW(), $this->invitedUsersResource);
        $this->entityManager->flush();
        // Accès aux préférences de tous les utilisateurs du role "Invité" pour un role "Utilisateur"
        $access1 = $this->aclService->isAllowed($this->roleBasic,
                                                User_Model_Action_Default::VIEW(),
                                                $this->invitedUsersResource);
        // L'accès ne marche pas pour les préférences des "Utilisateurs"
        $access2 = $this->aclService->isAllowed($this->roleBasic,
                                                User_Model_Action_Default::VIEW(),
                                                $this->basicUsersResource);
        // L'accès ne marche pas pour les préférences des admins
        $access3 = $this->aclService->isAllowed($this->roleBasic,
                                                User_Model_Action_Default::VIEW(),
                                                $this->adminUsersResource);
        // Supprime le privilège
        $this->aclService->disallow($this->roleBasic, User_Model_Action_Default::VIEW(), $this->invitedUsersResource);
        $this->entityManager->flush();
        $this->assertTrue($access1);
        $this->assertFalse($access2);
        $this->assertFalse($access3);
    }

    /**
     * Teste les privilèges qui s'appliquent à tous les utilisateurs d'un role
     * Test plus complexe : si on a accès à la ressource "Tous les utilisateurs du role X",
     * alors on a accès à l'utilisateur Y (qui est dans le role X)
     */
    public function testDroitsUtilisateursDeRole2()
    {
        // Création du privilège donnant l'accès à utilisateur sur les préférences des invités
        $this->aclService->allow($this->roleBasic, User_Model_Action_Default::VIEW(), $this->invitedUsersResource);

        // Crée un utilisateur "invité"
        $invitedUser = UserTest::generateObject();
        $invitedUser->addRole($this->roleInvited);
        $invitedUser->save();
        $this->entityManager->flush();
        // Crée une ressource représentant cet utilisateur invité
        $invitedUserResource = new User_Model_Resource_Entity();
        $invitedUserResource->setEntity($invitedUser);
        $invitedUserResource->save();
        $this->entityManager->flush();

        // Accès aux préférences de l'utilisateur de test
        $access = $this->aclService->isAllowed($this->roleBasic,
                                               User_Model_Action_Default::VIEW(),
                                               $invitedUserResource);

        $this->aclService->disallow($this->roleBasic, User_Model_Action_Default::VIEW(), $this->invitedUsersResource);
        $invitedUserResource->delete();
        $invitedUser->delete();
        $this->entityManager->flush();

        $this->assertTrue($access);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function tearDown()
    {
        parent::tearDown();
        try {
            // Désactive le cache des ACL
            $this->cacheService->enabled = false;

            //Suppression des objets crées pour les tests
            $this->aclService->disallow($this->roleAdmin, User_Model_Action_Default::VIEW(), $this->allUsersResource);
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
            if ($this->testUserResource) {
                $this->testUserResource->delete();
            }
            if ($this->testUser) {
                $this->testUser->delete();
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
