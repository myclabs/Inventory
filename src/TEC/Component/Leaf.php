<?php
/**
 * @author     valentin.claras
 * @package    TEC
 * @subpackage Component
 */

namespace TEC\Component;

/**
 * @package    TEC
 * @subpackage Component
 */
class Leaf extends Component
{
    /**
     * Contient le nom d'un élément de calcul (pour les arbres numériques et logiques)
     * ou l'action (pour les expression d'executions).
     *
     * @var String
     */
    protected $name;


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