<?php

namespace Parameter\Domain\Family;

use Core_Exception_InvalidArgument;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Strategy_Ordered;
use Core_Tools;

/**
 * Membre d'une dimension.
 *
 * @author guillaume.querat
 * @author matthieu.napoli
 */
class Member extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $ref;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Dimension
     */
    protected $dimension;

    /**
     * Cellules associées à ce membre
     * On est obligé de déclarer cette relation pour avoir le cascade delete, sans ça
     * le cascade delete depuis la famille pose problème
     * @var Cell[]
     */
    protected $cells;

    /**
     * @param Dimension  $dimension
     * @param string     $ref
     * @param string     $label
     */
    public function __construct(Dimension $dimension, $ref, $label)
    {
        $this->dimension = $dimension;
        Core_Tools::checkRef($ref);
        $this->ref = $ref;
        $this->label = $label;

        // Ajout réciproque à la dimension
        $dimension->addMember($this);
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        // Update les coordonnées des cellules
        foreach ($this->cells as $cell) {
            $cell->updateMembersHashKey();
        }
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return Dimension
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
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
     * Renvoie les valeurs du contexte pour l'objet.
     * @throws Core_Exception_InvalidArgument
     * @return array
     */
    protected function getContext()
    {
        if ($this->dimension->getId() == null) {
            throw new Core_Exception_InvalidArgument("La dimension du membre doit être persistée et flushée");
        }
        return [
            'dimension' => $this->getDimension(),
        ];
    }
}
