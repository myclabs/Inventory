<?php

namespace Classification\Domain;

use Core_Model_Entity;
use Core_Strategy_Ordered;
use Core_Model_Entity_Translatable;
use Core_Exception_UndefinedAttribute;

/**
 * Context de Classification.
 *
 * @author valentin.claras
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
     * @var ClassificationLibrary
     */
    protected $library;

    /**
     * @var string
     */
    protected $ref;

    /**
     * @var string
     */
    protected $label;


    public function __construct(ClassificationLibrary $library)
    {
        $this->library = $library;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ClassificationLibrary
     */
    public function getLibrary()
    {
        return $this->library;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $value
     */
    public function setLabel($value)
    {
        $this->label = $value;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
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

    /**
     * @return array
     */
    protected function getContext()
    {
        return ['library' => $this->library];
    }
}
