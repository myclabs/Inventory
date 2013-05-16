<?php
/**
 * Classe Unit_Model_Unit_Extension
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Unit
 */

/**
 * Extension
 * @package Unit
 * @subpackage Model
 */
class Unit_Model_Unit_Extension extends Core_Model_Entity
{

    use Core_Model_Entity_Translatable;

    // Constantes de tri et filtres.
    const QUERY_ID = 'id';
    const QUERY_NAME = 'name';

    /**
     * Identifiant de l'extension
     * @var int
     */
    protected $id;

    /**
     * Référent textuel de l'extension.
     * @var String
     */
    protected $ref;

    /**
     * Nom de l'extension.
     * @var String
     */
    protected $name;

    /**
     * Symbole de l'extension.
     * @var String
     */
    protected $symbol;

    /**
     * Coefficient mutliplicatteur de l'extension.
     * @var float
     */
    protected $multiplier;


    /**
     * Renvoie la référence de la pool active.
     *  Il s'agit de l'entityManager correspondant.
     *
     * @return string
     */
    public static function getActivePoolName()
    {
        return Unit_Model_Unit::getActivePoolName();
    }

    /**
     * Retourne l'objet Unit à partir de son référent textuel.
     * @param string $ref
     * @return Unit_Model_Unit
     */
    public static function loadByRef($ref)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref));
    }

    /**
     * Défini la ref de l'extension.
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Renvoi la ref de l'extension.
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Défini le nom de l'extension.
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Renvoi le nom de l'extension.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Défini le symbole de l'extension.
     * @param string $symbol
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;
    }

    /**
     * Renvoi le symbole de l'extension.
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Défini le coefficient multiplicateur de l'unité.
     * @param int $multiplier
     */
    public function setMultiplier($multiplier)
    {
        $this->multiplier = $multiplier;
    }

    /**
     * Renvoie le coefficient multiplicateur.
     * @throws Core_Exception_UndefinedAttribute
     * @return int
     */
    public function getMultiplier()
    {
        if ($this->multiplier == null) {
            throw new Core_Exception_UndefinedAttribute('Multiplier has not be defined');
        }
        return $this->multiplier;
    }

}