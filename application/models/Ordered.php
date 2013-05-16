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
class Default_Model_Ordered extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    // Constantes de tri et filtres.
    const QUERY_ID = 'id';
    const QUERY_POSITION = 'position';
    const QUERY_CONTEXT = 'context';


    /**
     * @var integer
     */
    protected $id;

    /**
     * @var String
     */
    protected $context;


    /**
     * Surcharge ud context de la strategy.
     */
    protected function getContext()
    {
        return array('context' => $this->context);
    }

    /**
     * Fonction appelé avant un persist de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        try {
            $this->checkHasPosition();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->setPosition();
        }
    }

    /**
     * Fonction appelé avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelé avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelé après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }

    /**
     * Charge l'objet en fonction de sa position.
     *
     * @param int $position
     * @param string $context
     */
    public static function loadByPosition($position, $context=null)
    {
        return self::loadByPositionAndContext($position, array('context' => $context));
    }

    /**
     * Donne la dernière position.
     *
     * @param string $context
     */
    public static function getLastPosition($context=null)
    {
        return self::getLastPositionByContext(array('context' => $context));
    }

    /**
     * @param array $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

}