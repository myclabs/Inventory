<?php
/**
 * @author valentin.claras
 * @package Unit
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Unit_Tableau_DiscreteUnitsController
 * @package Unit
 * @subpackage Controller
 */
class Unit_Datagrids_DiscreteunitsController extends UI_Controller_Datagrid
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
        /* @var $discreteUnit Unit_Model_Unit_Discrete */
        foreach (Unit_Model_Unit_Discrete::loadList($this->request) as $discreteUnit) {
            $element = array();
            $idDiscreteUnit = $discreteUnit->getKey();
            $element['index'] = $idDiscreteUnit['id'];
            $element['name'] = $discreteUnit->getName();
            $element['ref'] = $discreteUnit->getRef();
            $element['symbole'] = $discreteUnit->getSymbol();
            $this->addLine($element);
        }
        $this->totalElements = Unit_Model_Unit_Discrete::countTotal($this->request);
        $this->send();
    }

}