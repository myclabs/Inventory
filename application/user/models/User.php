<?php
/**
 * @author     matthieu.napoli
 * @author     valentin.claras
 * @package    User
 * @subpackage Model
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User domain class
 * @package    User
 * @subpackage Model
 */
class User_Model_User extends User_Model_SecurityIdentity
{

    /**#@+
     * Constantes de tri et filtre
     */
    const QUERY_ID = 'id';
    const QUERY_PASSWORD = 'password';
    const QUERY_LASTNAME = 'lastName';
    const QUERY_FIRSTNAME = 'firstName';
    const QUERY_EMAIL = 'email';
    const QUERY_EMAIL_KEY = 'emailKey';
    const QUERY_CREATIONDATE = 'creationDate';
    /**#@-*/

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
     * @var User_Model_Role[]|Collection
     */
    protected $roles;


    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct();
        $this->creationDate = new DateTime();
        $this->roles = new ArrayCollection();
    }

    /**
     * Renvoie l'utilisateur correspondant au couple (email, password)
     * @param string $email    Email utilisateur
     * @param string $password Mot de passe de l'utilisateur
     * @throws Core_Exception_NotFound L'email ne correspond à aucun utilisateur
     * @throws Core_Exception_InvalidArgument Mauvais mot de passe
     * @return User_Model_User
     */
    public static function login($email, $password)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_EMAIL, $email);
        $list = self::loadList($query);
        if (count($list) == 0) {
            throw new Core_Exception_NotFound("User not found");
        }
        /** @var $user User_Model_User */
        $user = current($list);
        if (! $user->testPassword($password)) {
            throw new Core_Exception_InvalidArgument("Wrong password");
        }
        return $user;
    }

    /**
     * Renvoie l'utilisateur correspondant au mail
     * @param string $email
     * @return User_Model_User
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
     * @return User_Model_User
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
     * @return User_Model_Role[]
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * Vérifie si un utiisateur possède un role donné
     * @param User_Model_Role $role
     * @return bool
     */
    public function hasRole(User_Model_Role $role)
    {
        return $this->roles->contains($role);
    }

    /**
     * Ajoute un rôle à l'utilisateur
     * @param User_Model_Role $role
     */
    public function addRole(User_Model_Role $role)
    {
        if (!$this->hasRole($role)) {
            $this->roles->add($role);
            $role->addUser($this);
        }
    }

    /**
     * Retire un rôle de l'utilisateur
     * @param User_Model_Role $role
     */
    public function removeRole(User_Model_Role $role)
    {
        if ($this->hasRole($role)) {
            $this->roles->removeElement($role);
            $role->removeUser($this);
        }
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
            throw new Core_Exception("Impossible de générer une clé mail unique "
                                         . "pour la création d'un compte utilisateur");
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

}
