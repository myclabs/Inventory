<?php
/**
 * @author     matthieu.napoli
 * @author     joseph.rouffet
 * @package    Social
 * @subpackage Test
 */

/**
 * Creation of the Test Suite
 * @package    Social
 * @subpackage Test
 */
class Social_Model_MessageTest
{

    /**
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Social_Model_MessageUnitTest');
        $suite->addTestSuite('Social_Model_MessageSetUpTest');
        $suite->addTestSuite('Social_Model_MessageOtherTest');
        return $suite;
    }

    /**
     * Generation of a test object
     * @return Social_Model_Message
     */
    public static function generateObject()
    {
        $o = new Social_Model_Message();
        $o->save();
        return $o;
    }

    /**
     * Deletion of an object created with generateObject
     * @param Social_Model_Message $o
     */
    public static function deleteObject(Social_Model_Message $o)
    {
        $o->delete();
    }

}


/**
 * @package    Social
 * @subpackage Test
 */
class Social_Model_MessageUnitTest extends PHPUnit_Framework_TestCase
{

    /**
     * Envoi du message
     */
    public function testSend()
    {
        $message = new Social_Model_Message();
        $this->assertFalse($message->isSent());
        $message->send();
        $this->assertTrue($message->isSent());
    }

}


/**
 * Test of the creation/modification/deletion of the entity
 * @package    Social
 * @subpackage Test
 */
class Social_Model_MessageSetUpTest extends Core_Test_TestCase
{

    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Social_Model_Message::loadList() as $o) {
            $o->delete();
        }
        foreach (Social_Model_UserGroup::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_User::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * @return Social_Model_Message
     */
    function testConstruct()
    {
        $o = new Social_Model_Message();

        $o->save();
        $this->entityManager->flush();

        $this->assertNotNull($o->getId());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param Social_Model_Message $o
     * @return Social_Model_Message
     */
    function testLoad(Social_Model_Message $o)
    {
        $oLoaded = Social_Model_Message::load($o->getId());
        $this->assertSame($o, $oLoaded);
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Social_Model_Message $o
     */
    function testDelete(Social_Model_Message $o)
    {
        $o->delete();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_REMOVED,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_NEW,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
    }

}


/**
 * Tests of Social_Model_Message class
 * @package    Social
 * @subpackage Test
 */
class Social_Model_MessageOtherTest extends Core_Test_TestCase
{

    /**
     * @var User_Service_User
     */
    private $userService;

    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Social_Model_Message::loadList() as $o) {
            $o->delete();
        }
        foreach (Social_Model_UserGroup::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_User::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();
        $this->userService = User_Service_User::getInstance();
    }

    /**
     * loadByAuthor
     */
    public function testLoadByAuthor()
    {
        $author = $this->userService->createUser('toto', 'toto');
        $o = Social_Model_MessageTest::generateObject();

        $o->setAuthor($author);
        $o->save();
        $this->entityManager->flush();

        $messages = Social_Model_Message::loadByAuthor($author);
        $this->assertCount(1, $messages);
        $this->assertEquals($o->getId(), $messages[0]->getId());
        $this->assertSame($o, $messages[0]);

        Social_Model_MessageTest::deleteObject($o);
        $this->userService->deleteUser($o->getAuthor());
    }

    /**
     * Test de Get / Set
     */
    public function testGetSet()
    {
        $this->markTestIncomplete("TODO");
        //test le retour null
        $null1 = $this->_message->getAuthor();
        $null2 = $this->_message->getDispatchDate();

        $this->assertNull($null1);
        $this->assertNull($null2);

        //Test
        $dispatchDate = Core_Date::now();
        $creationDate = Core_Date::now();

        $this->_message->setTitle("testGetSet");
        $this->_message->setStatus("STATUS_DISPATCHED");
        $this->_message->setDispatchDate($dispatchDate);
        $this->_message->setText("testGetSet");
        $this->_message->setCreationDate($creationDate);
        $this->_message->setAuthor($this->_author);

        $t1 = $this->_message->getTitle();
        $t2 = $this->_message->getStatus();
        $t3 = $this->_message->getDispatchDate();
        $t4 = $this->_message->getText();
        $t5 = $this->_message->getCreationDate();
        $t6 = $this->_message->getAuthor();

        $this->assertEquals("testGetSet", $t1);
        $this->assertEquals("STATUS_DISPATCHED", $t2);
        $this->assertEquals($dispatchDate, $t3);
        $this->assertEquals($t4, "testGetSet");
        $this->assertEquals($t5, $creationDate);
        $this->assertSame($t6, $this->_author);

    }

    /**
     * Test La récupération d'un destinataire
     */
    public function testGetUserRecipients()
    {
        $this->markTestIncomplete("TODO");
        $userRecipient = new User_Model_User();
        $userRecipient->login = 'user';
        $userRecipient->setPassword('user');
        $userRecipient->email = 'user@myc-sense.com';
        $userRecipient->save();

        $userRecipient1 = new User_Model_User();
        $userRecipient1->login = 'user1';
        $userRecipient1->setPassword('user1');
        $userRecipient1->email = 'user1@myc-sense.com';
        $userRecipient1->save();

        $this->_message->addUserRecipient($userRecipient);
        $this->_message->addUserRecipient($userRecipient1);

        $this->assertEquals(count($this->_message->getUserRecipients()), 2);

        foreach ($this->_message->getUserRecipients() as $tempUserRecipient) {
            $this->assertTrue($this->_message->hasUserRecipient($tempUserRecipient));
        }

    }

    /**
     * test l'ajout de UserRecipient
     */
    public function testAddUserRecipient()
    {
        $this->markTestIncomplete("TODO");
        $userRecipient = new User_Model_User();
        $userRecipient->login = 'user';
        $userRecipient->setPassword('user');
        $userRecipient->email = 'user@myc-sense.com';
        $userRecipient->save();

        $userRecipient1 = new User_Model_User();
        $userRecipient1->login = 'user1';
        $userRecipient1->setPassword('user1');
        $userRecipient1->email = 'user1@myc-sense.com';
        $userRecipient1->save();

        $userRecipient2 = new User_Model_User();
        $userRecipient2->login = 'user2';
        $userRecipient2->setPassword('user2');
        $userRecipient2->email = 'user2@myc-sense.com';
        $userRecipient2->save();

        //Test si le tableau de message est vide
        $this->assertEquals(count($this->_message->getUserRecipients()), 0);

        $this->_message->addUserRecipient($userRecipient);
        $this->_message->save();
        $this->_message->addUserRecipient($userRecipient1);
        $this->_message->addUserRecipient($userRecipient2);

        //Test l'ajout
        $this->assertEquals(count($this->_message->getUserRecipients()), 3);

        $this->assertTrue($this->_message->hasUserRecipient($userRecipient));
        $this->assertTrue($this->_message->hasUserRecipient($userRecipient1));
        $this->assertTrue($this->_message->hasUserRecipient($userRecipient2));

        //test de suprésion puis de réinsertion

        $this->_message->save();

        $this->_message->removeUserRecipient($userRecipient);
        $this->assertEquals(count($this->_message->getUserRecipients()), 2);
        $this->_message->addUserRecipient($userRecipient);
        $this->assertEquals(count($this->_message->getUserRecipients()), 3);

        // Test après chargement de l'objet
        $message = Social_Model_Message::load($this->_message->getKey());
        $this->assertEquals(count($message->getUserRecipients()), 3);

    }

    /**
     * test la récupération d'un groupe de destinataire
     */
    public function testGetUserGroups()
    {
        $this->markTestIncomplete("TODO");
        $userGroup = new Social_Model_UserGroup();
        $userGroup->setRef("testGetUserGroup");
        $userGroup->save();

        $userGroup1 = new Social_Model_UserGroup();
        $userGroup->setRef("testGetUserGroup");
        $userGroup1->save();

        $this->_message->addUserGroup($userGroup);
        $this->_message->addUserGroup($userGroup1);
        $this->_message->save();

        $this->assertEquals(count($this->_message->getUserGroups()), 2);

        foreach ($this->_message->getUserGroups() as $tempUserGroup) {
            $this->assertTrue($this->_message->hasUserGroup($tempUserGroup));
        }

    }

}


