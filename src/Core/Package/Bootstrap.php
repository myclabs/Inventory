<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Bootstrap
 */

use DI\Container;

/**
 * Classe de bootstrap : initialisation de l'application.
 *
 * @package    Core
 * @subpackage Bootstrap
 */
abstract class Core_Package_Bootstrap extends Zend_Application_Module_Bootstrap
{

    /**
     * @var Container
     */
    public $container;

    /**
     * Renvoie la liste des méthodes ayant été lancé.
     * @return array
     */
    public function getRun()
    {
        return $this->_run;
    }

    /**
     * Défini la liste des méthodes ayant été lancé dans le bootstrap principal.
     * @param array $run
     */
    public function setRun($run)
    {
        $this->_run = $run;
    }

}
