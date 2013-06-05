<?php
/**
 * @author yoann.croizer
 * @package Unit
 */

use Core\Annotation\Secure;
use Unit\Domain\Unit\ExtendedUnit;

/**
 * Unit_Tableau_ListeunitesetenduesController
 * @package Unit
 * @subpackage Controller
 */
class Unit_Datagrids_ExtendedunitsController extends UI_Controller_Datagrid
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
        /* @var $extendedUnit ExtendedUnit */
        foreach (ExtendedUnit::loadList($this->request) as $extendedUnit) {
            $element = array();
            $idExtendedUnit = $extendedUnit->getKey();
            $element['index'] = $idExtendedUnit['id'];
            $element['name'] = $extendedUnit->getName();
            $element['ref'] = $extendedUnit->getRef();
            $element['symbol'] = $extendedUnit->getSymbol();
            $element['multiplier'] = Core_Locale::loadDefault()->formatNumber($extendedUnit->getMultiplier(), 10);
            $this->addLine($element);
        }
        $this->totalElements = ExtendedUnit::countTotal($this->request);
        $this->send();
    }

}