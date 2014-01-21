<?php

use Core\Test\TestCase;
use Doctrine\ORM\UnitOfWork;
use User\Domain\User;
use User\Domain\UserService;

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
}

class UserSetUpTest extends TestCase
{
    protected $passwordDefaut = 'password-default';

    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (User::loadList() as $o) {
            $o->delete();
        }
        self::getEntityManager()->flush();
    }

    /**
     * Test du constructeur
     * @return User
     */
    public function testConstruct()
    {
        $o = new User();

        // Applique les attributs
        $o->setFirstName('Chuck');
        $o->setLastName('Norris');
        $o->setEmail('cnorris@example.com');
        $o->setPassword($this->passwordDefaut);
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
        $this->assertTrue($o->testPassword($this->passwordDefaut));
        $this->assertFalse($o->isEmailValidated());
        return $o;
    }


    /**
     * @depends testConstruct
     * @param  User $o
     * @return User
     */
    public function testLoad(User $o)
    {
        $this->entityManager->clear();
        /** @var $user User */
        $user = User::load($o->getId());

        $this->assertTrue($o instanceof User);
        $this->assertNotSame($o, $user);
        $this->assertEquals($o->getId(), $user->getId());

        // Vérifie les attributs
        $this->assertEquals($user->getFirstName(), $o->getFirstName());
        $this->assertEquals($user->getLastName(), $o->getLastName());
        $this->assertEquals($user->getEmail(), $o->getEmail());
        $this->assertEquals($user->isEnabled(), $o->isEnabled());
        $this->assertTrue($user->testPassword($this->passwordDefaut));
        return $user;
    }

    /**
     * @depends testLoad
     * @param User $o
     */
    public function testDelete(User $o)
    {
        $o->delete();
        $this->assertEquals(
            UnitOfWork::STATE_REMOVED,
            $this->entityManager->getUnitOfWork()->getEntityState($o)
        );
        $this->entityManager->flush();
        $this->assertEquals(
            UnitOfWork::STATE_NEW,
            $this->entityManager->getUnitOfWork()->getEntityState($o)
        );
    }
}

class UserMetierTest extends TestCase
{
    /**
     * @Inject
     * @var UserService
     */
    private $userService;

    /**
     * Test du mot de passe
     */
    public function testTestPassword()
    {
        $user = $this->userService->createUser(Core_Tools::generateString(20) . '@example.com', 'test');
        $this->entityManager->flush();

        $user->setPassword('password1');
        $this->assertTrue($user->testPassword('password1'));
        $this->assertFalse($user->testPassword('mauvais_password'));

        $user->setPassword('password2');
        $this->assertTrue($user->testPassword('password2'));
        $this->assertFalse($user->testPassword('password1'));

        $this->userService->deleteUser($user);
        $this->entityManager->flush();
    }

    /**
     * Test du mail unique
     */
    public function testMailUtilise()
    {
        $user = $this->userService->createUser(Core_Tools::generateString(20) . '@example.com', 'test');
        $this->entityManager->flush();

        $this->assertTrue(User::isEmailUsed($user->getEmail()));
        $this->assertFalse(User::isEmailUsed('emailNotUsed'));

        $this->userService->deleteUser($user);
        $this->entityManager->flush();
    }

    /**
     * Test du load par clé email
     */
    public function testLoadByEmailKey()
    {
        $user = $this->userService->createUser(Core_Tools::generateString(20) . '@example.com', 'test');
        $user->generateKeyEmail();
        $this->entityManager->flush();

        $o = User::loadByEmailKey($user->getEmailKey());
        $this->assertEquals($user->getId(), $o->getId());

        $this->userService->deleteUser($user);
        $this->entityManager->flush();
    }

    /**
     * Test de inviteUser
     */
    public function testInviteUser()
    {
        $email = Core_Tools::generateString(20) . '@example.com';

        $user = $this->userService->inviteUser($email);
        $this->entityManager->flush();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($email, $user->getEmail());
        $this->assertTrue($user->isEnabled());
        $this->assertNull($user->getLastName());
        $this->assertNull($user->getFirstName());
        $this->assertFalse($user->testPassword(''));

        $this->userService->deleteUser($user);
        $this->entityManager->flush();
    }

    /**
     * Test de generateKeyEmail
     */
    public function testGenerateEmailKey()
    {
        $user = $this->userService->createUser(Core_Tools::generateString(20) . '@example.com', 'test');
        $this->entityManager->flush();

        // Supprime la clé mail
        $user->eraseEmailKey();
        // Vérifie que la clé mail est vide
        $this->assertNull($user->getEmailKey());
        // Génère la clé mail
        $user->generateKeyEmail();
        // Vérifie que la clé mail n'est plus vide
        $this->assertNotNull($user->getEmailKey());
        $this->assertEquals(10, mb_strlen($user->getEmailKey()));

        $this->userService->deleteUser($user);
        $this->entityManager->flush();
    }

    /**
     * Test de locale défaut
     */
    public function testLocaleDefault()
    {
        $user = $this->userService->createUser(Core_Tools::generateString(20) . '@example.com', 'test');
        $this->entityManager->flush();

        $locale = $user->getLocale();
        $this->assertTrue($locale instanceof Core_Locale);

        $this->userService->deleteUser($user);
        $this->entityManager->flush();
    }
}
