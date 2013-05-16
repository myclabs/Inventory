<?php
/**
 * @package    User
 * @subpackage Test
 */

/**
 * @package    User
 * @subpackage Test
 */
class ResourceTest extends Core_Test_TestCase
{

    /**
     * @var User_Service_ACL
     */
    protected $aclService;

    public static function setUpBeforeClass()
    {
        User_Service_ACLFilter::getInstance()->enabled = false;
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Default_Model_SimpleExample::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_Resource::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    public function setUp()
    {
        parent::setUp();
        // Service des ACL
        $this->aclService = User_Service_ACL::getInstance();
    }


    public function testDirectAuthorizations()
    {
        $resource = ResourceNamedTest::generateObject();
        $role = RoleTest::generateObject();

        $authorization = $this->aclService->allow($role, User_Model_Action_Default::VIEW(), $resource);
        $this->entityManager->flush();

        $authorizations = $resource->getDirectAuthorizations();

        $this->assertCount(1, $authorizations);
        foreach ($authorizations as $a) {
            $this->assertInstanceOf('User_Model_Authorization', $authorization);
            $this->assertEquals($authorization, $a);
        }

        ResourceNamedTest::deleteObject($resource);
        RoleTest::deleteObject($role);
    }

    public function testGetLinkedSecurityIdentities()
    {
        $resource = ResourceNamedTest::generateObject();
        $role = RoleTest::generateObject();

        $this->aclService->allow($role, User_Model_Action_Default::VIEW(), $resource);
        $this->entityManager->flush();

        $identities = $resource->getLinkedSecurityIdentities();

        $this->assertCount(1, $identities);

        ResourceNamedTest::deleteObject($resource);
        RoleTest::deleteObject($role);
    }

    /**
     * Test qu'il n'y a pas de doublons renvoyés
     */
    public function testGetLinkedSecurityIdentitiesNoDuplicates()
    {
        $resource = ResourceNamedTest::generateObject();
        $role = RoleTest::generateObject();

        $this->aclService->allow($role, User_Model_Action_Default::VIEW(), $resource);
        $this->aclService->allow($role, User_Model_Action_Default::EDIT(), $resource);
        $this->entityManager->flush();

        $identities = $resource->getLinkedSecurityIdentities();

        $this->assertCount(1, $identities);

        ResourceNamedTest::deleteObject($resource);
        RoleTest::deleteObject($role);
    }

}
