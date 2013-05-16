<?php
/**
 * @author valentin.claras
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Exec
 * @subpackage Model
 */

/**
 * Classe utilisée lors des tests unitaires et qui implémente l'interface ValueProvider
 * @package Exec
 * @subpackage Model
 */
class Default_Model_ValueProviderEntity implements Exec_Interface_ValueProvider
{
    /**
     * Tableau qui contiendra une liste de ref => bool
     *
     * @var array
     */
    protected $_tab;


    /**
     * @param array $tab
     */
    public function __construct($tab = null)
    {
        $this->_tab = $tab;
    }

    /**
     * Vérifie la valeur associée à la ref donnée en vue de l'execution.
     *
     * @param String $ref
     */
    public function checkValueForExecution($ref)
    {
        return array();
    }

    /**
     * Renvoi la valeur associée à la ref donnée.
     *
     * @param String $ref
     */
    public function getValueForExecution($ref)
    {
        return $this->_tab[$ref];
    }

}