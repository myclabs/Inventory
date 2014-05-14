<?php
/**
 * Classe UnitExtension
 * @author  valentin.claras
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Unit
 */

namespace Unit\Domain;

use Core\Translation\TranslatedString;
use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Unit\Domain\Unit\Unit;

/**
 * Extension
 * @package    Unit
 * @subpackage Model
 */
class UnitExtension extends Core_Model_Entity
{
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
     * @var TranslatedString
     */
    protected $name;

    /**
     * Symbole de l'extension.
     * @var TranslatedString
     */
    protected $symbol;

    /**
     * Coefficient mutliplicatteur de l'extension.
     * @var float
     */
    protected $multiplier;

    public function __construct()
    {
        $this->name = new TranslatedString();
        $this->symbol = new TranslatedString();
    }

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
     * @return \Unit\Domain\Unit\Unit
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
     * Renvoi le nom de l'extension.
     * @return TranslatedString
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Renvoi le symbole de l'extension.
     * @return TranslatedString
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
     * @throws \Core_Exception_UndefinedAttribute
     * @return int
     */
    public function getMultiplier()
    {
        if ($this->multiplier == null) {
            throw new \Core_Exception_UndefinedAttribute('Multiplier has not be defined');
        }
        return $this->multiplier;
    }

}
