<?php

namespace User\Domain;

use Core_Exception;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Locale;
use Core_Model_Entity;
use Core_Model_Query;
use Core_Tools;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use User\Domain\ACL\Role;
use User\Domain\ACL\Authorization;

/**
 * User domain class.
 *
 * @author matthieu.napoli
 * @author valentin.claras
 */
class User extends Core_Model_Entity
{
    const QUERY_ID = 'id';
    const QUERY_PASSWORD = 'password';
    const QUERY_LASTNAME = 'lastName';
    const QUERY_FIRSTNAME = 'firstName';
    const QUERY_EMAIL = 'email';
    const QUERY_EMAIL_KEY = 'emailKey';
    const QUERY_CREATIONDATE = 'creationDate';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $email;

    /**
     * Email validé ou pas
     * @var bool Défaut à false
     */
    protected $emailValidated = false;

    /**
     * Clé texte (unique) générée pour créer un lien unique permettant à
     * un utilisateur de valider une action par courriel.
     * @var string
     */
    protected $emailKey;

    /**
     * Utilisateur actif ou non
     * @var bool Défaut à true
     */
    protected $enabled = true;

    /**
     * Mot de passe hashé
     * @var string
     */
    protected $password;

    /**
     * Date de création de l'utilisateur
     * @var DateTime
     */
    protected $creationDate;

    /**
     * Locale de l'utilisateur
     * @var Core_Locale
     */
    protected $locale;

    /**
     * Rôles de l'utilisateur
     * @var Role[]|Collection
     */
    protected $roles;

    /**
     * Authorizations related to this resource
     * @var Authorization[]|Collection
     */
    protected $authorizations;


    public function __construct()
    {
        $this->creationDate = new DateTime();
        $this->roles = new ArrayCollection();
        $this->authorizations = new ArrayCollection();
    }

    /**
     * Renvoie l'utilisateur correspondant au couple (email, password)
     * @param string $email    Email utilisateur
     * @param string $password Mot de passe de l'utilisateur
     * @throws Core_Exception_NotFound L'email ne correspond à aucun utilisateur
     * @throws Core_Exception_InvalidArgument Mauvais mot de passe
     * @return User
     */
    public static function login($email, $password)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_EMAIL, $email);
        $list = self::loadList($query);
        if (count($list) == 0) {
            throw new Core_Exception_NotFound("User not found");
        }
        /** @var $user User */
        $user = current($list);
        if (!$user->testPassword($password)) {
            throw new Core_Exception_InvalidArgument("Wrong password");
        }
        return $user;
    }

    /**
     * Renvoie l'utilisateur correspondant au mail
     * @param string $email
     * @return User
     * @throws Core_Exception_NotFound
     */
    public static function loadByEmail($email)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_EMAIL, $email);
        $list = self::loadList($query);
        if (count($list) == 0) {
            throw new Core_Exception_NotFound("User not found matching email $email");
        }
        return current($list);
    }

    /**
     * Renvoie une instance de la classe correspondant à la clé mail
     * @param string $mailKey
     * @return User
     * @throws Core_Exception_NotFound
     */
    public static function loadByEmailKey($mailKey)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_EMAIL_KEY, $mailKey);
        $list = self::loadList($query);
        if (count($list) == 0) {
            throw new Core_Exception_NotFound("User not found matching email key $mailKey");
        }
        return current($list);
    }

    /**
     * Teste si l'email est déjà utilisé
     * @param string $email
     * @return bool True si le mail est déjà utilisé
     * @throws Core_Exception_InvalidArgument The email is null
     */
    public static function isEmailUsed($email)
    {
        if ($email == null) {
            throw new Core_Exception_InvalidArgument('email is null');
        }
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_EMAIL, $email);
        $list = self::loadList($query);
        return (count($list) != 0);
    }

    /**
     * Teste si la clé mail est déjà utilisé
     * @param string $emailKey
     * @return bool True si le mail est déjà utilisé
     * @throws Core_Exception_InvalidArgument The email key is null
     */
    public static function isEmailKeyUsed($emailKey)
    {
        if ($emailKey == null) {
            throw new Core_Exception_InvalidArgument('email key is null');
        }
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_EMAIL_KEY, $emailKey);
        $list = self::loadList($query);
        return (count($list) != 0);
    }

    /**
     * Renvoie la date de création de l'utilisateur
     * @return DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Renvoie la liste des rôles de l'utilisateur
     * @return Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Vérifie si un utiisateur possède un role donné
     * @param Role $role
     * @return bool
     */
    public function hasRole(Role $role)
    {
        return $this->roles->contains($role);
    }

    public function addRole(Role $role)
    {
        $this->roles->add($role);

        // Update authorizations
        foreach ($role->getAuthorizations() as $authorization) {
            $this->authorizations->add($authorization);
        }
    }

    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
        $this->updateAuthorizations();
    }

    public function updateAuthorizations()
    {
        $this->authorizations->clear();

        foreach ($this->getRoles() as $role) {
            foreach ($role->getAuthorizations() as $authorization) {
                $this->authorizations->add($authorization);
            }
        }
    }

    /**
     * Retourne les autorisations de cet utilisateur.
     *
     * @return Authorization[]
     */
    public function getAuthorizations()
    {
        return $this->authorizations;
    }

    public function addAuthorization(Authorization $authorization)
    {
        $this->authorizations->add($authorization);
    }

    public function removeAuthorization(Authorization $authorization)
    {
        $this->authorizations->removeElement($authorization);
    }

    public function replaceAuthorizations(array $authorizations)
    {
        $this->authorizations = new ArrayCollection($authorizations);
    }

    /**
     * Définit la locale de l'utilisateur
     * @param Core_Locale $locale
     */
    public function setLocale(Core_Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * Retourne la locale de l'utilisateur
     * @return Core_Locale
     */
    public function getLocale()
    {
        if ($this->locale !== null) {
            return $this->locale;
        } else {
            return Core_Locale::loadDefault();
        }
    }

    /**
     * Retourne le nom à afficher de l'utilisateur
     * @return string
     */
    public function getName()
    {
        if (($this->firstName != '') && ($this->lastName != '')) {
            return $this->firstName . ' ' . $this->lastName;
        } elseif ($this->firstName != '') {
            return $this->firstName . ' (' . $this->id . ')';
        } elseif ($this->lastName != '') {
            return $this->lastName;
        } else {
            return $this->email;
        }
    }

    /**
     * Génère une clé mail unique
     */
    public function generateKeyEmail()
    {
        // Vérifie que la chaine n'est pas utilisée. 3 essais (pour éviter la boucle infinie)
        $i = 0;
        do {
            $mailKey = Core_Tools::generateString(10);
            $used = self::isEmailKeyUsed($mailKey);
            $i++;
        } while ($used && ($i < 3));
        // On a trouvé notre chaine : on l'utilise
        if (!$used) {
            $this->emailKey = $mailKey;
        } else {
            throw new Core_Exception(
                "Impossible de générer une clé mail unique pour la création d'un compte utilisateur"
            );
        }
    }

    /**
     * Efface la clé mail (impossible de la modifier)
     */
    public function eraseEmailKey()
    {
        $this->emailKey = null;
    }

    /**
     * @return string Clé email de l'utilisateur
     */
    public function getEmailKey()
    {
        return $this->emailKey;
    }

    /**
     * @param string $password Mot de passe
     */
    public function setPassword($password)
    {
        $this->password = self::hashPassword($password);
    }

    /**
     * Définit un nouveau mot de passe aléatoire
     * @return string Mot de passe généré
     */
    public function setRandomPassword()
    {
        $password = Core_Tools::generateString(8);
        $this->setPassword($password);
        return $password;
    }

    /**
     * Teste le mot de passe
     * @param string $password
     * @return bool True si le password correspond
     */
    public function testPassword($password)
    {
        if ($this->password === md5($password)) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Active l'utilisateur
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Désactive l'utilisateur
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * @return boolean
     */
    public function isEmailValidated()
    {
        return $this->emailValidated;
    }

    /**
     * @param boolean $emailValidated
     */
    public function setEmailValidated($emailValidated)
    {
        $this->emailValidated = $emailValidated;
    }

    /**
     * Hash un mot de passe
     *
     * Utilise MD5 pour rétrocompatibilité
     * TODO changer d'algorithme et utiliser un salt
     * @param string $password
     * @return string
     */
    protected static function hashPassword($password)
    {
        return md5($password);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
