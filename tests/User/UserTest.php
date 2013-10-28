<?php

use Doctrine\ORM\UnitOfWork;
use User\Domain\ACL\Role;
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

    /**
     * Génere un objet pret à l'emploi pour les tests.
     * @param  string $email
     * @return User Objet généré
     */
    public static function generateObject($email = null)
    {
        // Création d'un nouvel objet.
        $o = new User();
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
     * @param User &$o l'objet de test a supprimer
     */
    public static function deleteObject(& $o)
    {
        // Suppression de l'objet.
        $o->delete();
    }
}

class UserSetUpTest extends Core_Test_TestCase
{
    protected $passwordDefaut = 'password-default';

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (User::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
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

class UserMetierTest extends Core_Test_TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (User::loadList() as $o) {
            $o->delete();
        }
        $this->entityManager->flush();
    }

    /**
     * Test de la méthode de login
     */
    public function testLogin()
    {
        $user = new User();
        $user->setEmail(Core_Tools::generateString(20));
        $user->setPassword('test');
        $user->save();
        $this->entityManager->flush();

        $o = User::login($user->getEmail(), 'test');
        $this->assertTrue($o instanceof User);
        $this->assertEquals($o->getId(), $user->getId());

        $user->delete();
        $this->entityManager->flush();
    }

    /**
     * Test de la méthode de login
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testWrongPassword()
    {
        $user = new User();
        $user->setEmail(Core_Tools::generateString(20));
        $user->setPassword('test');
        $user->save();
        $this->entityManager->flush();

        try {
            User::login($user->getEmail(), 'mauvais-password');
        } catch (Core_Exception_InvalidArgument $e) {
            $user->delete();
            $this->entityManager->flush();
            throw $e;
        }
    }

    /**
     * Test de la méthode de login
     * @expectedException Core_Exception_NotFound
     */
    public function testWrongLogin()
    {
        $user = new User();
        $user->setEmail(Core_Tools::generateString(20));
        $user->setPassword('test');
        $user->save();
        $this->entityManager->flush();

        try {
            User::login('foo', 'test');
        } catch (Core_Exception_NotFound $e) {
            $user->delete();
            $this->entityManager->flush();
            throw $e;
        }
    }

    /**
     * Test du mot de passe
     */
    public function testTestPassword()
    {
        $user = new User();

        $user->setPassword('password1');
        $this->assertTrue($user->testPassword('password1'));
        $this->assertFalse($user->testPassword('mauvais_password'));

        $user->setPassword('password2');
        $this->assertTrue($user->testPassword('password2'));
        $this->assertFalse($user->testPassword('password1'));
    }

    /**
     * Test du mail unique
     */
    public function testMailUtilise()
    {
        $user = new User();
        $user->setEmail(Core_Tools::generateString(20));
        $user->setPassword('test');
        $user->save();
        $this->entityManager->flush();

        $this->assertTrue(User::isEmailUsed($user->getEmail()));
        $this->assertFalse(User::isEmailUsed('emailNotUsed'));

        $user->delete();
        $this->entityManager->flush();
    }

    /**
     * Test du load par clé email
     */
    public function testLoadByEmailKey()
    {
        $user = new User();
        $user->setEmail(Core_Tools::generateString(20));
        $user->setPassword('test');
        $user->generateKeyEmail();
        $user->save();
        $this->entityManager->flush();

        $o = User::loadByEmailKey($user->getEmailKey());
        $this->assertEquals($user->getId(), $o->getId());

        $user->delete();
        $this->entityManager->flush();
    }

    /**
     * Test de inviteUser
     */
    public function testInviteUser()
    {
        /** @var $userService UserService */
        $userService = $this->get(UserService::class);
        $_SERVER['SERVER_NAME'] = 'http://127.0.0.1';
        $email = 'inviteUser@mail.fr';
        $o = $userService->inviteUser($email);
        $this->assertInstanceOf(User::class, $o);
        $this->assertEquals($email, $o->getEmail());
        $this->assertTrue($o->isEnabled());
        $this->assertNull($o->getLastName());
        $this->assertNull($o->getFirstName());
        $this->assertFalse($o->testPassword(''));
    }

    /**
     * Test de generateKeyEmail
     */
    public function testGenerateEmailKey()
    {
        $user = new User();
        // Supprime la clé mail
        $user->eraseEmailKey();
        // Vérifie que la clé mail est vide
        $this->assertNull($user->getEmailKey());
        // Génère la clé mail
        $user->generateKeyEmail();
        // Vérifie que la clé mail n'est plus vide
        $this->assertNotNull($user->getEmailKey());
        $this->assertEquals(10, mb_strlen($user->getEmailKey()));
    }

    /**
     * Test de locale défaut
     */
    public function testLocaleDefault()
    {
        $user = new User();
        $locale = $user->getLocale();
        $this->assertTrue($locale instanceof Core_Locale);
    }

    /**
     * Test de locale défaut
     * @depends testLocaleDefault
     */
    public function testSetLocale()
    {
        $locale = Core_Locale::load('fr_FR');

        $user = new User();
        $user->setEmail(Core_Tools::generateString(20));
        $user->setPassword('test');
        $user->setLocale($locale);
        $user->save();
        $this->entityManager->flush();

        $this->entityManager->clear();

        /** @var $user User */
        $user = User::load($user->getId());
        $locale2 = $user->getLocale();
        $this->assertEquals($locale, $locale2);

        $user->delete();
        $this->entityManager->flush();
    }

    /**
     * Test du lien utilisateur - role
     */
    public function testUtilisateurRole()
    {
        $user = new User();
        $user->setEmail(Core_Tools::generateString(20));
        $user->setPassword('test');
        $user->save();
        $this->entityManager->flush();

        // L'utilisateur n'a pas de rôle
        $this->assertCount(0, $user->getRoles());

        // Ajoute le role à l'utilisateur
        $role = new Role\UserRole($user);
        $user->addRole($role);
        // Sauvegarde et recharge
        $user->save();
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var $user User */
        $user = User::load($user->getId());
        /** @var Role $role */
        $role = Role::load($role->getId());
        // Vérifie le lien
        $roles = $user->getRoles();
        $this->assertCount(1, $roles);
        $this->assertEquals($role->getId(), $roles[0]->getId());
        // Retire le lien
        $user->removeRole($role);
        $this->assertCount(0, $user->getRoles());

        $user->delete();
        $this->entityManager->flush();
    }
}
