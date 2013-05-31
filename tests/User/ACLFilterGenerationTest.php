<?php
/**
 * @author matthieu.napoli
 * @package User
 * @subpackage Test
 */

/**
 * Teste que le filtre des ACL est identique entre une génération totale (from scratch)
 * et une génération incrémentale (partielle) à chaque modification des ACL
 *
 * @package User
 * @subpackage Test
 */
class ACLFilterGenerationTest extends Core_Test_TestCase
{

    /**
     * @var User_Service_User
     */
    protected $userService;

    /**
     * @var User_Service_ACL
     */
    protected $aclService;

    /**
     * @var User_Service_ACLFilter
     */
    protected $aclFilterService;


    public function setUp()
    {
        parent::setUp();
        $this->userService = User_Service_User::getInstance();
        $this->aclService = User_Service_ACL::getInstance();
        $this->aclFilterService = User_Service_ACLFilter::getInstance();
        $this->aclFilterService->enabled = false;
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
        foreach (Inventory_Model_SimpleExample::loadList() as $o) {
            $o->delete();
        }
        $this->entityManager->flush();
        $this->aclFilterService->enabled = true;
        $this->aclFilterService->clean();
    }

    public function testTotal1()
    {
        // Génération totale
        $this->aclFilterService->enabled = false;
        $this->fixtureGeneration1();
        $this->aclFilterService->enabled = true;
        $this->aclFilterService->generate();

        $nb = $this->aclFilterService->getEntriesCount();

        $this->aclFilterService->enabled = false;
        $this->fixtureDeletion1();
        $this->aclFilterService->enabled = true;
        $this->aclFilterService->generate();

        $this->assertEquals(2, $nb);

        return $nb;
    }

    /**
     * @depends testTotal1
     */
    public function testPartial1($nb1)
    {
        // Génération incrémentale
        $this->aclFilterService->enabled = true;
        $this->fixtureGeneration1();
        $nb2 = $this->aclFilterService->getEntriesCount();
        $this->fixtureDeletion1();

        // Test
        $this->assertEquals($nb1, $nb2);
    }

    /**
     * Fixtures
     */
    private function fixtureGeneration1()
    {
        $this->userService->createUser('user', 'user');
    }

    /**
     * Fixtures
     */
    private function fixtureDeletion1()
    {
        $user = User_Model_User::loadByEmail('user');
        $this->userService->deleteUser($user);
    }


    public function testTotal2()
    {
        // Génération totale
        $this->aclFilterService->enabled = false;
        $this->fixtureGeneration2();
        $this->aclFilterService->enabled = true;
        $this->aclFilterService->generate();

        $nb = $this->aclFilterService->getEntriesCount();

        $this->aclFilterService->enabled = false;
        $this->fixtureDeletion2();
        $this->aclFilterService->enabled = true;
        $this->aclFilterService->generate();

        $this->assertEquals(2, $nb);

        return $nb;
    }

    /**
     * @depends testTotal2
     */
    public function testPartial2($nb1)
    {
        // Génération incrémentale
        $this->aclFilterService->enabled = true;
        $this->fixtureGeneration2();
        $nb2 = $this->aclFilterService->getEntriesCount();
        $this->fixtureDeletion2();

        // Test
        $this->assertEquals($nb1, $nb2);
    }

    /**
     * Fixtures
     */
    private function fixtureGeneration2()
    {
        $user = $this->userService->createUser('user', 'user');
        $resource = new User_Model_Resource_Entity();
        $resource->setEntityName('User_Model_User');
        $resource->save();
        $this->entityManager->flush();
        $this->aclService->allow($user, User_Model_Action_Default::VIEW(), $resource);
        $this->entityManager->flush();
    }

    /**
     * Fixtures
     */
    private function fixtureDeletion2()
    {
        $user = User_Model_User::loadByEmail('user');
        $resource = User_Model_Resource_Entity::loadByEntityName('User_Model_User');
        $this->aclService->disallow($user, User_Model_Action_Default::VIEW(), $resource);
        $this->userService->deleteUser($user);
        $resource->delete();
        $this->entityManager->flush();
    }


    public function testTotal3()
    {
        // Génération totale
        $this->aclFilterService->enabled = false;
        $this->fixtureGeneration3();
        $this->aclFilterService->enabled = true;
        $this->aclFilterService->generate();

        $nb = $this->aclFilterService->getEntriesCount();

        $this->aclFilterService->enabled = false;
        $this->fixtureDeletion3();
        $this->aclFilterService->enabled = true;
        $this->aclFilterService->generate();

        $this->assertEquals(3, $nb);

        return $nb;
    }

    /**
     * @depends testTotal3
     */
    public function testPartial3($nb1)
    {
        // Génération incrémentale
        $this->aclFilterService->enabled = true;
        $this->fixtureGeneration3();

        $nb2 = $this->aclFilterService->getEntriesCount();

        $this->fixtureDeletion3();

        // Test
        $this->assertEquals($nb1, $nb2);
    }

    /**
     * Fixtures
     */
    private function fixtureGeneration3()
    {
        $user = $this->userService->createUser('user', 'user');

        $roleSuperAdmin = new User_Model_Role('superAdmin');
        $roleSuperAdmin->save();
        $roleAdmin = new User_Model_Role('projectAdmin');
        $roleAdmin->save();
        $this->entityManager->flush();

        $entity = new Inventory_Model_SimpleExample();
        $entity->save();
        $this->entityManager->flush();
        $resource = new User_Model_Resource_Entity();
        $resource->setEntity($entity);
        $resource->save();
        $this->entityManager->flush();

        $user->addRole($roleSuperAdmin);
        $this->entityManager->flush();
        $user->addRole($roleAdmin);
        $this->entityManager->flush();

        $this->aclService->allow($roleSuperAdmin, User_Model_Action_Default::VIEW(), $resource);
        $this->aclService->allow($roleAdmin, User_Model_Action_Default::VIEW(), $resource);
        $this->entityManager->flush();
    }

    /**
     * Fixtures
     */
    private function fixtureDeletion3()
    {
        $user = User_Model_User::loadByEmail('user');
        $entity = Inventory_Model_SimpleExample::loadList()[0];
        $resource = User_Model_Resource_Entity::loadByEntity($entity);
        foreach ($user->getRoles() as $role) {
            $this->aclService->disallow($role, User_Model_Action_Default::VIEW(), $resource);
            $user->removeRole($role);
            $role->delete();
        }
        $resource->delete();
        $this->entityManager->flush();
        $this->userService->deleteUser($user);
        $entity->delete();
        $this->entityManager->flush();
    }



    public function testTotal4()
    {
        // Génération totale
        $this->aclFilterService->enabled = false;
        $this->fixtureGeneration4();
        $this->aclFilterService->enabled = true;
        $this->aclFilterService->generate();

        $nb = $this->aclFilterService->getEntriesCount();

        $this->aclFilterService->enabled = false;
        $this->fixtureDeletion4();
        $this->aclFilterService->enabled = true;
        $this->aclFilterService->generate();

        $this->assertEquals(3, $nb);

        return $nb;
    }

    /**
     * @depends testTotal4
     */
    public function testPartial4($nb1)
    {
        // Génération incrémentale
        $this->aclFilterService->enabled = true;
        $this->fixtureGeneration4();

        $nb2 = $this->aclFilterService->getEntriesCount();

        $this->fixtureDeletion4();

        // Test
        $this->assertEquals($nb1, $nb2);
    }

    /**
     * Fixtures
     */
    private function fixtureGeneration4()
    {
        $user = $this->userService->createUser('user', 'user');

        $roleSuperAdmin = new User_Model_Role('superAdmin');
        $roleSuperAdmin->save();
        $this->entityManager->flush();

        $entity = new Inventory_Model_SimpleExample();
        $entity->save();
        $this->entityManager->flush();
        $resource = new User_Model_Resource_Entity();
        $resource->setEntity($entity);
        $resource->save();
        $this->entityManager->flush();

        $this->aclService->allow($roleSuperAdmin, User_Model_Action_Default::VIEW(), $resource);
        $this->entityManager->flush();

        $user->addRole($roleSuperAdmin);
        $this->entityManager->flush();
    }

    /**
     * Fixtures
     */
    private function fixtureDeletion4()
    {
        $user = User_Model_User::loadByEmail('user');
        $entity = Inventory_Model_SimpleExample::loadList()[0];
        $resource = User_Model_Resource_Entity::loadByEntity($entity);
        foreach ($user->getRoles() as $role) {
            $this->aclService->disallow($role, User_Model_Action_Default::VIEW(), $resource);
            $user->removeRole($role);
            $role->delete();
        }
        $resource->delete();
        $this->entityManager->flush();
        $this->userService->deleteUser($user);
        $entity->delete();
        $this->entityManager->flush();
    }

    public function testTotal5()
    {
        // Génération totale
        $this->aclFilterService->enabled = false;
        $this->fixtureGeneration5();
        $this->aclFilterService->enabled = true;
        $this->aclFilterService->generate();

        $nb = $this->aclFilterService->getEntriesCount();

        $this->aclFilterService->enabled = false;
        $this->fixtureDeletion5();
        $this->aclFilterService->enabled = true;
        $this->aclFilterService->generate();

        $this->assertEquals(2, $nb);

        return $nb;
    }

    /**
     * @depends testTotal5
     */
    public function testPartial5($nb1)
    {
        // Génération incrémentale
        $this->aclFilterService->enabled = true;
        $this->fixtureGeneration5();
        $nb2 = $this->aclFilterService->getEntriesCount();
        $this->fixtureDeletion5();

        // Test
        $this->assertEquals($nb1, $nb2);
    }

    /**
     * Fixtures
     */
    private function fixtureGeneration5()
    {
        $user = $this->userService->createUser('user', 'user');

        $roleSuperAdmin = new User_Model_Role('superAdmin');
        $roleSuperAdmin->save();
        $this->entityManager->flush();

        $resource = new User_Model_Resource_Entity();
        $resource->setEntityName('User_Model_User');
        $resource->save();

        $this->entityManager->flush();

        $this->aclService->allow($roleSuperAdmin, User_Model_Action_Default::VIEW(), $resource);
        $this->entityManager->flush();

        $user->addRole($roleSuperAdmin);
        $this->entityManager->flush();
    }

    /**
     * Fixtures
     */
    private function fixtureDeletion5()
    {
        $user = User_Model_User::loadByEmail('user');
        $role = User_Model_Role::loadByRef('superAdmin');
        $resource = User_Model_Resource_Entity::loadByEntityName('User_Model_User');
        $this->aclService->disallow($user, User_Model_Action_Default::VIEW(), $resource);
        $this->userService->deleteUser($user);
        $role->delete();
        $resource->delete();
        $this->entityManager->flush();
    }


    public function tearDown()
    {
        parent::tearDown();
        if ($this->aclFilterService->getEntriesCount() > 0) {
            $this->fail("ACL filter entries left in DB after the test execution");
        }
    }

}
