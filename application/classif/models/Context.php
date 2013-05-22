<?php
/**
 * Classe Classif_Model_Context
 * @author     valentin.claras
 * @author     cyril.perraud
 * @package    Classif
 * @subpackage Model
 */

/**
 * Permet de gérer un context.
 * @package    Classif
 * @subpackage Model
 */
class Classif_Model_Context extends Core_Model_Entity
{

    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';

    /**
     * Identifiant unique du Context.
     *
     * @var int
     */
    protected $id;

    /**
     * Référence unique du Context.
     *
     * @var string
     */
    protected $ref;

    /**
     * Label du Context.
     *
     * @var string
     */
    protected $label;


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
     * Charge un Context à partir de sa ref.
     *
     * @param string $ref
     *
     * @return Classif_Model_Context
     */
    public static function loadByRef($ref = null)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Modifie la référence du Context.
     *
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Retourne la référence unique du Context.
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Modifie le label du Context.
     *
     * @param string $value
     */
    public function setLabel($value)
    {
        $this->label = $value;
    }

    /**
     * Retourne le label du Context.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Représentation de l'instance.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->ref;
    }

}
