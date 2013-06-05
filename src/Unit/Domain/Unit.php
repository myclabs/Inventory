<?php
/**
 * @author  valentin.claras
 * @author  hugo.charbonniere
 * @author  yoann.croizer
 * @package Unit
 */

namespace Unit\Domain;

use Core_Model_Entity;
use Core_Model_Entity_Translatable;

/**
 * Unité
 * @package Unit
 */
abstract class Unit extends Core_Model_Entity
{

    use Core_Model_Entity_Translatable;

    // Constantes de tri et filtres.
    const QUERY_ID = 'id';
    const QUERY_NAME = 'name';
    const QUERY_SYMBOL = 'symbol';
    const QUERY_REF = 'ref';


    /**
     * Identifiant d'une unité
     * @var int
     */
    protected $id;

    /**
     * Référent textuel d'une unité
     * @var string
     */
    protected $ref;

    /**
     * Nom d'une unité
     * @var string
     */
    protected $name;

    /**
     * Symbole d'une unité
     * @var string
     */
    protected $symbol;


    /**
     * Retourne l'objet Unit à partir de son référent textuel.
     * @param string $ref
     * @return \Unit\Domain\Unit
     */
    public static function loadByRef($ref)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref));
    }

    /**
     * Défini la ref de l'unité.
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Renvoi la ref de l'unité.
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Défini le nom de l'unité.
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Renvoi le nom de l'unité.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Défini le symbole de l'unité.
     * @param string $symbol
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;
    }

    /**
     * Renvoi le symbole de l'unité.
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Renvoi l'unité de reference.
     */
    abstract public function getReferenceUnit();

    /**
     * Renvoi le facteur de Conversion de l'unité
     * @param \Unit\Domain\Unit $unit
     */
    abstract public function getConversionFactor(Unit $unit);

}