<?php
/**
 * @author valentin.claras
 * @package Core
 * @subpackage Test
 */

/**
 * Classe de test simple.
 * @package Core
 * @subpackage Test
 */
class Inventory_Model_Simple extends Core_Model_Entity
{
    // Constantes de tri et filtres.
    const QUERY_ID = 'id';
    const QUERY_NAME = 'name';
    const QUERY_DATE = 'creationDate';


    /**
     * @var integer
     */
    protected $id;

    /**
     * @var String
     */
    protected $name;

    /**
     * @var DateTime
     */
    protected $creationDate;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->creationDate = new DateTime();
    }

    /**
     * @param string $name
     * @return Inventory_Model_Simple
     */
    public static function loadByName($name)
    {
        return self::getEntityRepository()->loadBy(array('name' => $name));
    }

    /**
     * @param string $name
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
     * @param DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return Core_Date
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }
}