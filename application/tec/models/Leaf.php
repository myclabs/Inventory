<?php
/**
 * @author valentin.claras
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package TEC
 */

/**
 * @package TEC
 * @subpackage Model
 */
class TEC_Model_Leaf extends TEC_Model_Component
{
    /**
     * Contient le nom d'un élément de calcul (pour les arbres numériques et logiques)
     * ou l'action (pour les expression d'executions).
     *
     * @var String
     */
    protected $name;

    /**
     * Fonction appelé avant un persist de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        $this->checkHasParent();
    }

    /**
     * Fonction appelé avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasParent();
    }

    /**
     * Vérifie que la Leaf possède bien un parent : obligatoire !
     */
    protected function checkHasParent()
    {
       if ($this->parent === null) {
           throw new Core_Exception_UndefinedAttribute('A Leaf needs to have a parent element.');
       }
    }

    /**
     * Renvoie la référence de la pool active.
     *  Il s'agit de l'entityManager correspondant.
     *
     * @return string
     */
    public static function getActivePoolName()
    {
        return TEC_Model_Component::getActivePoolName();
    }

    /**
     * Défini le nom de la Leaf.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Renvoi le nom de la Leaf.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}