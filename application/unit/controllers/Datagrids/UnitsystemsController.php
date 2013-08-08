<?php
/**
 * @author yoann.croizer
 * @package Unit
 */

use Core\Annotation\Secure;

/**
 * Unit_Tableau_ListeSystemeUnitsController
 * @package Unit
 * @subpackage Controller
 */
class Unit_Datagrids_UnitsystemsController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * Récupération des paramètres de tris et filtres de la manière suivante :
     *  $this->request.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie la liste d'éléments, le nombre total et un message optionnel.
     *
     * @Secure("viewUnit")
     */
    public function getelementsAction()
    {
        /* @var $unitSystem Unit_Model_Unit_System */
        foreach (Unit_Model_Unit_System::loadList($this->request) as $unitSystem) {
            $element = array();
            $idUnitSystem = $unitSystem->getKey();
            $element['index'] = $idUnitSystem['id'];
            $element['name'] = $unitSystem->getName();
            $element['ref'] = $unitSystem->getRef();
            $this->addLine($element);
        }
        $this->totalElements = Unit_Model_Unit_System::countTotal($this->request);
        $this->send();
    }

}