<?php

/**
 * Entity class
 *
 * @author valentin.claras
 * @author matthieu.napoli
 */
abstract class Core_Model_Entity
{
    /**
     * Persiste l'objet.
     */
    final public function save()
    {
        self::getEntityManager()->persist($this);
    }

    /**
     * Supprime l'objet.
     */
    final public function delete()
    {
        self::getEntityManager()->remove($this);
    }

    /**
     * Returns the corresponding instance of the class for the given id.
     *
     * @param array|mixed $id
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
     * Renvoie la référence de la pool active.
     *  Il s'agit de l'entityManager correspondant.
     *
     * @return string
     *
     * @todo À supprimer
     */
    public static function getActivePoolName()
    {
        return 'default';
    }

    /**
     * Renvoie l'EntityManager actif.
     *
     * @return Doctrine\ORM\EntityManager
     */
    protected static function getEntityManager()
    {
        return \Core\ContainerSingleton::getEntityManager();
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
        return strtolower(preg_replace(['#[a-z]|(Model_)|(Domain\\\)#', '#\\\#'], ['', '_'], $className));
    }

    /**
     * Renvoie la clé primaire de l'objet.
     *  Le tableau est indexé par le champs de la clef référençant la valeur.
     *
     * @return array
     */
    public function getKey()
    {
        return self::getEntityManager()->getClassMetadata(get_called_class())->getIdentifierValues($this);
    }

    /**
     * @return string Représentation textuelle de l'entité
     */
    public function __toString()
    {
        if (isset($this->ref)) {
            return $this->ref;
        }
        if (isset($this->id) && is_numeric($this->id)) {
            return (string) $this->id;
        }
        $tmp = [];
        foreach ($this->getKey() as $key => $value) {
            $tmp[] = "$key: $value";
        }
        return '{' . implode(', ', $tmp) . '}';
    }
}
