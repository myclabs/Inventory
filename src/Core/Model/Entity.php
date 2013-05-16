<?php
/**
 * @author     valentin.claras
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Model
 */

/**
 * Entity class
 *
 * Design pattern multiton, entity pool.
 *
 * @package    Core
 * @subpackage Model
 */
abstract class Core_Model_Entity
{

    /**
     * Array pointing wich EntityManager is used by each class.
     *
     * @var string[string] $_classMamanger[className] = refEntityManager
     */
    private static $_classManager = array();


    /**
     * Persiste l'objet métier.
     *
     * @return void
     */
    public final function save()
    {
        self::getEntityManager()->persist($this);
    }

    /**
     * Supprime l'objet metier.
     *
     * @return void
     */
    public final function delete()
    {
        self::getEntityManager()->remove($this);
    }

    /**
     * Returns the corresponding instance of the class for the given id.
     *
     * @param array|mixed $id
     *
     * @see getKey()
     *
     * @return static
     */
    public static function load($id)
    {
        return self::getEntityRepository()->load($id);
    }

    /**
     * Charge la liste des objets de cette classe
     *
     * Cette liste peut être paramètrée par des filtres ou des tris optionnels.
     *
     * @param Core_Model_Query $queryParameters Paramètres de la requête
     *
     * @return Core_Model_Entity[]
     */
    public static function loadList(Core_Model_Query $queryParameters = null)
    {
        if ($queryParameters == null) {
            $queryParameters = new Core_Model_Query();
        }

        return self::getEntityRepository()->loadList($queryParameters);
    }

    /**
     * Renvoie le nombre d'éléments total que loadList peut charger.
     *
     * @param Core_Model_Query $queryParameters Paramètres de la requête
     *
     * @return int Nombre d'éléments
     */
    public static function countTotal(Core_Model_Query $queryParameters = null)
    {
        if ($queryParameters == null) {
            $queryParameters = new Core_Model_Query();
        }

        return self::getEntityRepository()->countTotal($queryParameters);
    }

    /**
     * Définit la pool d'objet active.
     *  Il s'agit de l'entityManager correspondant.
     *
     * @param string $poolName
     *
     * @throws Core_Exception_Database
     *
     * @return void
     */
    public static function setActivePoolName($poolName='default')
    {
        self::checkEntityManagerExists($poolName);
        $className = get_called_class();
        self::$_classManager[$className] = $poolName;
    }

    /**
     * Renvoie la référence de la pool active.
     *  Il s'agit de l'entityManager correspondant.
     *
     * @return string
     */
    public static function getActivePoolName()
    {
        $className = get_called_class();
        if (!(isset(self::$_classManager[$className]))) {
            $className::setActivePoolName();
        }
        return self::$_classManager[$className];
    }

    /**
     * Vérifie qu'il est possible d'utiliser un poolName donné.
     *
     * @param string $poolName
     *
     * @throws Core_Exception_Database
     *
     * @return void
     */
    protected static function checkEntityManagerExists($poolName)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        if (!(isset($entityManagers[$poolName]))) {
            throw new Core_Exception_Database('Invalid name given, there is no EntityManager matching '.$poolName);
        }
    }

    /**
     * Renvoie l'EntityManager actif.
     *
     * @return Doctrine\ORM\EntityManager
     */
    protected static function getEntityManager()
    {
        $className = get_called_class();
        self::checkEntityManagerExists($className::getActivePoolName());
        $entityManagers = Zend_Registry::get('EntityManagers');
        return $entityManagers[$className::getActivePoolName()];
    }

    /**
     * Renvoie le Repository associé à la classe.
     *
     * @return Core_Model_Repository
     */
    protected static function getEntityRepository()
    {
        return self::getEntityManager()->getRepository(get_called_class());
    }

    /**
     * Retourne l'alias de la classe.
     *
     * @return string
     */
    public static function getAlias()
    {
        $className = get_called_class();
        return strtolower(preg_replace('#[a-z]|((Model_))#', '', $className));
    }

    /**
     * Renvoie la clé primaire de l'objet.
     *  Le tableau est indexé par le champs de la clef référençant la valeur.
     *
     * @return array
     */
    public function getKey()
    {
        $className = get_called_class();
        return self::getEntityManager()->getMetadataFactory()->getMetadataFor($className)->getIdentifierValues($this);
    }

    /**
     * @return string Représentation textuelle de l'entité
     */
    public function __toString()
    {
        if (isset($this->ref)) {
            return $this->ref;
        }
        $tmp = [];
        foreach ($this->getKey() as $key => $value) {
            $tmp[] = "$key: $value";
        }
        return '{' . implode(', ', $tmp) . '}';
    }

}
