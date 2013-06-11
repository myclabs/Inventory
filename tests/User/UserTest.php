<?php
/**
 * @package    User
 * @subpackage Test
 */

/**
 * Creation of the Test Suite.
 *
 * @package    User
 * @subpackage Test
 */
class UserTest
{

    /**
     * Déclaration de la suite de test à éffectuer.
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('UserSetUpTest');
        $suite->addTestSuite('UserMetierTest');
        return $suite;
    }

    /**
     * Génere un objet pret à l'emploi pour les tests.
     * @param  string $email
     * @return User_Model_User Objet généré
     */
    public static function generateObject($email = null)
    {
        // Création d'un nouvel objet.
        $o = new User_Model_User();
        if ($email !== null) {
            $o->setEmail($email);
        } else {
            $o->setEmail(Core_Tools::generateString(20));
        }
        $o->setPassword('test');
        $o->save();

        return $o;
    }

    /**
     * Supprime un objet de test généré avec generateObject().
     * @param User_Model_User &$o l'objet de test a supprimer
     */
    public static function deleteObject(& $o)
    {
        // Suppression de l'objet.
        $o->delete();
    }

}

/**
 * Test des méthodes métier de l'objet User_Model_User.
 *
 * @package    User
 * @subpackage Test
 */
class UserSetUpTest extends Core_Test_TestCase
{

    protected $_passwordDefaut = 'password-default';

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        if (User_Model_User::countTotal() > 0) {
            foreach (User_Model_User::loadList() as $o) {
                $o->delete();
            }
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }


    /**
     * Test du constructeur
     * @return User_Model_User
     */
    function testConstruct()
    {
        $o = new User_Model_User();

        // Applique les attributs
        $o->setFirstName('Chuck');
        $o->setLastName('Norris');
        $o->setEmail('cnorris@example.com');
        $o->setPassword($this->_passwordDefaut);
        $this->assertNull($o->getEmailKey());
        $this->assertTrue($o->isEnabled());

        $o->save();
        $this->entityManager->flush();

        $this->assertTrue($o->getId() > 0);
        // Vérifie les attributs
        $this->assertEquals('Chuck', $o->getFirstName());
        $this->assertEquals('Norris', $o->getLastName());
        $this->assertEquals('cnorris@example.com', $o->getEmail());
        $this->assertTrue($o->isEnabled());
        $this->assertTrue($o->testPassword($this->_passwordDefaut));
        $this->assertFalse($o->isEmailValidated());
        return $o;
    }


    /**
     * @depends testConstruct
     * @param  User_Model_User $o
     * @return User_Model_User
     */
    function testLoad(User_Model_User $o)
    {
        $this->entityManager->clear();
        /** @var $user User_Model_User */
        $user = User_Model_User::load($o->getId());

        $this->assertTrue($o instanceof User_Model_User);
        $this->assertNotSame($o, $user);
        $this->assertEquals($o->getId(), $user->getId());

        // Vérifie les attributs
        $this->assertEquals($user->getFirstName(), $o->getFirstName());
        $this->assertEquals($user->getLastName(), $o->getLastName());
        $this->assertEquals($user->getEmail(), $o->getEmail());
        $this->assertEquals($user->isEnabled(), $o->isEnabled());
        $this->assertTrue($user->testPassword($this->_passwordDefaut));
        return $user;
    }

    /**
     * @depends testLoad
     * @param User_Model_User $o
     */
    function testDelete(User_Model_User $o)
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
 * Test des méthodes métier de l'objet User_Model_User.
 *
 * @package    User
 * @subpackage Test
 */
class UserMetierTest extends Core_Test_TestCase
{

    private $defaultPassword = 'test';

    /**
     * @var User_Model_User
     */
    protected $user;

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        if (User_Model_User::countTotal() > 0) {
            foreach (User_Model_User::loadList() as $o) {
                $o->delete();
            }
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public function setUp()
    {
        parent::setUp();
        try {
            // Création d'un utilisateur
            $this->user = new User_Model_User();
            // Applique les attributs
            $this->user->setFirstName('Georges');
            $this->user->setLastName('Moustaki');
            $this->user->setEmail(Core_Tools::generateString(20));
            $this->user->setPassword($this->defaultPassword);
            $this->user->save();
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    /**
     * Test de la méthode de login
     */
    function testLogin()
    {
        $o = User_Model_User::login($this->user->getEmail(), $this->defaultPassword);
        $this->assertNotNull($o);
        $this->assertTrue($o instanceof User_Model_User);
        $this->assertEquals($o->getId(), $this->user->getId());
    }

    /**
     * Test de la méthode de login
     * @expectedException Core_Exception_InvalidArgument
     */
    function testWrongPassword()
    {
        User_Model_User::login($this->user->getEmail(), 'mauvais-password');
    }

    /**
     * Test de la méthode de login
     * @expectedException Core_Exception_NotFound
     */
    function testWrongLogin()
    {
        User_Model_User::login('foo', $this->defaultPassword);
    }

    /**
     * Test du mot de passe
     */
    function testTestPassword()
    {
        $this->user->setPassword('password1');
        $this->assertTrue($this->user->testPassword('password1'));
        $this->assertFalse($this->user->testPassword('mauvais_password'));
        $this->user->setPassword('password2');
        $this->assertTrue($this->user->testPassword('password2'));
        $this->assertFalse($this->user->testPassword('password1'));
    }

    /**
     * Test du mail unique
     */
    function testMailUtilise()
    {
        $this->assertTrue(User_Model_User::isEmailUsed($this->user->getEmail()));
        $this->assertFalse(User_Model_User::isEmailUsed('emailNotUsed'));
    }

    /**
     * Test du load par clé email
     */
    function testLoadByEmailKey()
    {
        $this->user->generateKeyEmail();
        $this->user->save();
        $this->entityManager->flush();

        $user = $this->user->loadByEmailKey($this->user->getEmailKey());
        $this->assertEquals($user->getId(), $this->user->getId());
    }

    /**
     * Test du mail unique
     * @expectedException Core_ORM_DuplicateEntryException
     */
    function testNewUserMailUsed()
    {
        $o = new User_Model_User();
        $o->setEmail($this->user->getEmail());
        $o->setPassword($this->defaultPassword);
        $o->save();
        $this->entityManager->flush();
    }

    /**
     * Test de inviteUser
     */
    function testInviteUser()
    {
        /** @var $userService User_Service_User */
        $userService = $this->get('User_Service_User');
        $_SERVER['SERVER_NAME'] = 'http://127.0.0.1';
        $email = 'inviteUser@mail.fr';
        $o = $userService->inviteUser($email);
        $this->assertInstanceOf('User_Model_User', $o);
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_MANAGED,
                            $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->assertGreaterThan(0, $o->getId());
        $this->assertEquals($email, $o->getEmail());
        $this->assertTrue($o->isEnabled());
        $this->assertNull($o->getLastName());
        $this->assertNull($o->getFirstName());
        $this->assertFalse($o->testPassword(''));
        // Suppression
        $userService->deleteUser($o);
        $this->entityManager->flush();
    }

    /**
     * Test de inviteUser avec un utilisateur existant
     * @expectedException Core_Exception_Duplicate
     */
    function testInviteExistingUser()
    {
        /** @var $userService User_Service_User */
        $userService = $this->get('User_Service_User');
        $userService->inviteUser($this->user->getEmail());
    }

    /**
     * Test de generateKeyEmail
     */
    function testGenerateEmailKey()
    {
        $user = new User_Model_User();
        // Supprime la clé mail
        $user->eraseEmailKey();
        // Vérifie que la clé mail est vide
        $this->assertNull($user->getEmailKey());
        // Génère la clé mail
        $user->generateKeyEmail();
        // Vérifie que la clé mail n'est plus vide
        $this->assertNotNull($user->getEmailKey());
        $this->assertEquals(32, mb_strlen($user->getEmailKey()));
    }

    /**
     * Test de locale défaut
     */
    function testLocaleDefault()
    {
        $user = new User_Model_User();
        $locale = $user->getLocale();
        $this->assertTrue($locale instanceof Core_Locale);
    }

    /**
     * Test de locale défaut
     * @depends testLocaleDefault
     */
    function testSetLocale()
    {
        $locale = Core_Locale::load('fr_FR');
        $this->user->setLocale($locale);
        $this->user->save();
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var $user User_Model_User */
        $user = User_Model_User::load($this->user->getId());
        $locale2 = $user->getLocale();
        $this->assertEquals($locale, $locale2);
    }

    /**
     * Test du lien utilisateur - role
     */
    function testUtilisateurRole()
    {
        // L'utilisateur n'a pas de rôle
        $this->assertCount(0, $this->user->getRoles());
        // Ajoute le role à l'utilisateur
        $role = RoleTest::generateObject();
        $this->user->addRole($role);
        // Sauvegarde et recharge
        $this->user->save();
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var $user User_Model_User */
        $user = User_Model_User::load($this->user->getId());
        /** @var $role User_Model_Role */
        $role = User_Model_Role::load($role->getId());
        // Vérifie le lien
        $roles = $user->getRoles();
        $this->assertCount(1, $roles);
        $this->assertEquals($role->getId(), $roles[0]->getId());
        // Retire le lien
        $user->removeRole($role);
        $this->assertCount(0, $user->getRoles());

        RoleTest::deleteObject($role);
    }


    /**
     * Méthode appelée à la fin des test
     */
    public function tearDown()
    {
        parent::tearDown();
        try {
            // Au cas où une exception ait fermé l'entitymanager
            $this->user = $this->entityManager->merge($this->user);
            // Suppression de l'utilisateur
            $this->user->delete();
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

}
