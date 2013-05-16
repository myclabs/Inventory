<?php
/**
 * @author  valentin.claras
 * @author  yoann.croizer
 * @author  hugo.charbonnier
 * @package TEC
 */

/**
 * @package    TEC
 * @subpackage Model
 */
abstract class TEC_Model_Component extends Core_Model_Entity
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
     * @var TEC_Model_Composite
     */
    protected $parent = null;

    /**
     * Indique l'état du noeud dans l'opération.
     *
     * @see TEC_Model_Component::MODIFIER_ADD
     * @see TEC_Model_Component::MODIFIER_SUB
     * @see TEC_Model_Component::MODIFIER_NOT
     *
     * @var string
     */
    protected $modifier;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Méthode permettant de modifier le noeud parent du noeud courant.
     *
     * @param TEC_Model_Composite $newParent
     */
    public function setParent(TEC_Model_Composite $newParent)
    {
        if ($this instanceof TEC_Model_Composite) {
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
     * @return TEC_Model_Composite
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Spécification du modifier.
     *
     * @see TEC_Model_Component::MODIFIER_ADD
     * @see TEC_Model_Component::MODIFIER_SUB
     * @see TEC_Model_Component::MODIFIER_NOT
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

}
