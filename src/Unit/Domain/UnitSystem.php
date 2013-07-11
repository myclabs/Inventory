<?php
/**
 * Classe UnitSystem
 * @author  valentin.claras
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Unit
 */

namespace Unit\Domain;

use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Unit\Domain\Unit\Unit;

/**
 * Système d'unité
 * @package    Unit
 * @subpackage Model
 */
class UnitSystem extends Core_Model_Entity
{

    use Core_Model_Entity_Translatable;

    // Constantes de tri et filtres.
    const QUERY_ID = 'id';
    const QUERY_NAME = 'name';
    const QUERY_REF = 'ref';

    /**
     * Identifiant du système d'unité.
     * @var int
     */
    protected $id;

    /**
     * Nom du système d'unité.
     * @var string
     */
    protected $name;

    /**
     * Réérent textuel du système d'unité.
     * @var string
     */
    protected $ref;


    /**
     * Renvoie la référence de la pool active.
     *  Il s'agit de l'entityManager correspondant.
     *
     * @return string
     */
    public static function getActivePoolName()
    {
        return Unit::getActivePoolName();
    }

    /**
     * Retourne l'objet Unit à partir de son référent textuel.
     * @param string $ref
     * @return \Unit\Domain\UnitSystem
     */
    public static function loadByRef($ref)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref));
    }

    /**
     * Défini la ref du système d'unité.
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Renvoi la ref du système d'unité.
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Défini le nom du système d'unité.
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Renvoi le nom du système d'unité.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}