<?php
/**
 * @author     valentin.claras
 * @package    TEC
 * @subpackage Component
 */

namespace TEC\Component;

use Core_Exception_InvalidArgument;

/**
 * @package    TEC
 * @subpackage Component
 */
abstract class Component
{
    /**
     * Constante précisant qu'il s'agit d'une addition / mutliplication.
     */
    const MODIFIER_ADD = 1;

    /**
     * Constante précisant qu'il s'agit d'une soustraction / division.
     */
    const MODIFIER_SUB = -1;

    /**
     * Constante précisant qu'il s'agit d'un non.
     */
    const MODIFIER_NOT = '!';

    /**
     * Identifiant unique du Component.
     *
     * @var int
     */
    protected $id;

    /**
     * Parent du component.
     *
     * @var Composite
     */
    protected $parent = null;

    /**
     * Indique l'état du noeud dans l'opération.
     *
     * @see Component::MODIFIER_ADD
     * @see Component::MODIFIER_SUB
     * @see Component::MODIFIER_NOT
     *
     * @var string
     */
    protected $modifier;


    /**
     * Méthode permettant de modifier le noeud parent du noeud courant.
     *
     * @param Composite $newParent
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setParent(Composite $newParent)
    {
        if ($this instanceof Composite) {
            $tempParent = $newParent;
            //Vérification de l'absence de cycles.
            while ($tempParent->parent != null) {
                if ($tempParent->parent !== $this) {
                    $tempParent = $tempParent->parent;
                } else {
                    throw new Core_Exception_InvalidArgument('Error : Cycle in the Tree detected');
                }
            }
        }

        $oldParent = $this->parent;
        $this->parent = $newParent;
        $newParent->addChild($this);
        if ($oldParent !== null) {
            $this->parent->removeChild($this);
        }
    }

    /**
     * Récupère le noeud parent du noeud courant.
     *
     * @return Composite
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Spécification du modifier.
     *
     * @see Component::MODIFIER_ADD
     * @see Component::MODIFIER_SUB
     * @see Component::MODIFIER_NOT
     *
     * @param string $modifier
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * Renvoi le modifier du noeud.
     *
     * @return string
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}
