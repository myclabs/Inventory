<?php

namespace Classif\Domain;

use Core_Model_Entity;
use Core_Strategy_Ordered;
use Core_Model_Entity_Translatable;
use Core_Exception_UndefinedAttribute;

/**
 * Contexte de classification.
 *
 * @author valentin.claras
 * @author cyril.perraud
 */
class Context extends Core_Model_Entity
{
    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';

    /**
     * @var int
     */
    protected $id;

    /**
     * Référence unique.
     *
     * @var string
     */
    protected $ref;

    /**
     * Libellé.
     *
     * @var string
     */
    protected $label;

    /**
     * Charge un Context à partir de son ref.
     *
     * @param string $ref
     *
     * @return Context
     */
    public static function loadByRef($ref = null)
    {
        return self::getEntityRepository()->loadBy(['ref' => $ref]);
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

    /**
     * Fonction appelée avant un persist de l'objet (défini dans le mapper).
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
     * Fonction appelée avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelée après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }
}
