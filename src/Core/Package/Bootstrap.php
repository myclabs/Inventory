<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Bootstrap
 */

/**
 * Classe de bootstrap : initialisation de l'application.
 *
 * @package    Core
 * @subpackage Bootstrap
 */
abstract class Core_Package_Bootstrap extends Zend_Application_Module_Bootstrap
{

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

    /**
     * Ajoute les informations de mapping du Module au driver de Doctrine.
     */
    protected function addModuleMappingInformationsToDoctrine()
    {
        $moduleName = $this->getModuleName();
        $loweredModuleName = strtolower($moduleName);
        $pathToModule = Core_Package_Manager::getPackage($moduleName)->getPath();

        /* @var $doctrineConfig Doctrine\ORM\Configuration */
        $doctrineConfig = Zend_Registry::get('doctrineConfiguration');
        $doctrineConfig->getMetadataDriverImpl()->getLocator()->addPaths(
            array(
                $pathToModule . '/application/'. $loweredModuleName . '/models/mappers'
            )
        );
    }

    /**
     * Ajoute les controleurs du Module au Zend_Controller_Front.
     */
    protected function addModulesControllersToFront()
    {
        $moduleName = $this->getModuleName();
        $loweredModuleName = strtolower($moduleName);
        $pathToModule = Core_Package_Manager::getPackage($moduleName)->getPath();

        Zend_Controller_Front::getInstance()->addControllerDirectory(
            $pathToModule . '/application/' . $loweredModuleName . '/controllers', $loweredModuleName
        );
    }

    /**
     * Méthode renvoyant les pages associées.
     *  Nécéssaire pour le bon fonctionnement des ACL.
     * @return array
     */
    public static function getAssociatedPages()
    {
        return array();
    }

}
