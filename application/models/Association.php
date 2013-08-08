<?php
/**
 * @author valentin.claras
 * @package Core
 * @subpackage Test
 */

/**
 * Classe de test d' association.
 * @package Core
 * @subpackage Test
 */
class Inventory_Model_Association extends Core_Model_Entity
{
    // Constantes de tri et filtres.
    const QUERY_ID = 'id';
    const QUERY_NAME = 'name';
    const QUERY_SIMPLE = 'simples';


    /**
     * @var integer
     */
    protected $id;

    /**
     * @var String
     */
    protected $name;

    /**
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $simples;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->simples = new Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Renvoie une liste de Inventory_Model_Association en fonction du nombre minimal de Simple associés.
     *
     * @param Core_Model_Query $queryParameters
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return Inventory_Model_Association
     */
    public static function loadWithMoreThanXSimple(Core_Model_Query $queryParameters)
    {
        if (!(isset($queryParameters->xSimple))) {
            throw new Core_Exception_UndefinedAttribute('The Query need parameter xSimple to be specified.');
        }

        return self::getEntityRepository()->getAssociationWithMoreThanXSimples($queryParameters);
    }

    /**
     * Renvoie une liste de Inventory_Model_Association en fonction du nombre minimal de Simple associés.
     *
     * @param Core_Model_Query $queryParameters
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return int
     */
    public static function countWithMoreThanXSimple(Core_Model_Query $queryParameters)
    {
        if (!(isset($queryParameters->xSimple))) {
            throw new Core_Exception_UndefinedAttribute('The Query need parameter xSimple to be specified.');
        }

        return self::getEntityRepository()->countAssociationWithMoreThanXSimples($queryParameters);
    }

    /**
     * @param stiring $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Ajoute un Simple à la collection.
     * @param Inventory_Model_Simple $simple
     */
    public function addSimple(Inventory_Model_Simple $simple)
    {
        $this->simples->add($simple);
    }

    /**
     * Retire un Simple de la collection.
     * @param Inventory_Model_Simple $simple
     */
    public function removeSimple(Inventory_Model_Simple $simple)
    {
        if ($this->hasSimple($simple)) {
            $this->simples->removeElement($simple);
        }
    }

    /**
     * Vérifie si le Simple est contenu dans la collection.
     * @param Inventory_Model_Simple $simple
     * @return boolean
     */
    public function hasSimple(Inventory_Model_Simple $simple)
    {
        return $this->simples->contains($simple);
    }

    /**
     * Renvoi l'ensemble des Simple de la collection.
     * @return Inventory_Model_Simple[]
     */
    public function getSimples()
    {
        return $this->simples->toArray();
    }

}