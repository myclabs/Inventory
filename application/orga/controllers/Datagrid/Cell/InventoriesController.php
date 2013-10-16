<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur du Datagrid listant les inventaires.
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Cell_InventoriesController extends UI_Controller_Datagrid
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
     * @Secure("viewCell")
     */
    function getelementsAction()
    {

        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);
        $crossedGranularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));

        if ($cell->getGranularity()->getRef() === $crossedGranularity->getRef()) {
            $this->addLineData($cell, $crossedGranularity);
            $this->totalElements = 1;
        } else {
            $customParameters = array();
            $filterConditions = array();
            foreach ($this->request->filter->getConditions() as $filterConditionArray) {
                if ($filterConditionArray['alias'] == Orga_Model_Member::getAlias()) {
                    $customParameters[] = $filterConditionArray;
                } else {
                    $filterConditions[] = $filterConditionArray;
                }
            }
            $this->request->setCustomParameters($customParameters);
            $this->request->filter->setConditions($filterConditions);
            $this->request->filter->addCondition(
                Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT,
                true,
                Core_Model_Filter::OPERATOR_EQUAL,
                Orga_Model_Cell::getAlias()
            );
            $this->request->filter->addCondition(
                Orga_Model_Cell::QUERY_RELEVANT,
                true,
                Core_Model_Filter::OPERATOR_EQUAL,
                Orga_Model_Cell::getAlias()
            );
            $this->request->order->addOrder(
                Orga_Model_Cell::QUERY_MEMBERS_HASHKEY,
                Core_Model_Order::ORDER_ASC,
                Orga_Model_Cell::getAlias()
            );
            foreach ($cell->loadChildCellsForGranularity($crossedGranularity, $this->request) as $childCell) {
                $this->addLineData($childCell, $crossedGranularity);
            }
            $this->totalElements = $cell->countTotalChildCellsForGranularity($crossedGranularity, $this->request);
        }

        $this->send();
    }

    /**
     * @param Orga_Model_Cell $cell
     * @param Orga_Model_Granularity $crossedGranularity
     * @return array
     */
    private function addLineData(Orga_Model_Cell $cell, Orga_Model_Granularity $crossedGranularity)
    {
        $granularityForInventoryStatus = $cell->getGranularity()->getOrganization()->getGranularityForInventoryStatus();

        $data = array();
        $data['index'] = $cell->getId();
        foreach ($cell->getMembers() as $member) {
            $data[$member->getAxis()->getRef()] = $member->getRef();
        }

        if ($crossedGranularity === $granularityForInventoryStatus) {
            $data['inventoryStatus'] = $cell->getInventoryStatus();
        } else {
            try {
                $data['inventoryStatus'] = $cell->getParentCellForGranularity($granularityForInventoryStatus)->getInventoryStatus();
            } catch (Core_Exception_NotFound $e) {
                $data['inventoryStatus'] = Orga_Model_Cell::STATUS_NOTLAUNCHED;
            }
        }
        if ($data['inventoryStatus'] !== Orga_Model_Cell::STATUS_NOTLAUNCHED) {
            $data['advancementInput'] = 0;
            $data['advancementFinishedInput'] = 0;

            $totalChildInputCells = 0;
            foreach ($cell->getGranularity()->getOrganization()->getInputGranularities() as $inputGranularity) {
                if ($inputGranularity === $cell->getGranularity()) {
                    try {
                        $afInputSetPrimary = $cell->getAFInputSetPrimary();
                        if ($afInputSetPrimary->isInputComplete()) {
                            $data['advancementInput'] ++;
                        }
                        if ($afInputSetPrimary->isFinished()) {
                            $data['advancementFinishedInput'] ++;
                        }
                    } catch (Core_Exception_UndefinedAttribute $e) {
                        // Pas de saisie pour l'instant = pas d'avancement.
                    }
                    $totalChildInputCells ++;
                } elseif ($inputGranularity->isNarrowerThan($cell->getGranularity())) {
                    $inputCells = $cell->getChildCellsForGranularity($inputGranularity);
                    foreach ($inputCells as $inputCell) {
                        try {
                            $childAfInputSetPrimary = $inputCell->getAFInputSetPrimary();
                            if ($childAfInputSetPrimary->isInputComplete()) {
                                $data['advancementInput'] ++;
                            }
                            if ($childAfInputSetPrimary->isFinished()) {
                                $data['advancementFinishedInput'] ++;
                            }
                        } catch (Core_Exception_UndefinedAttribute $e) {
                            // Pas de saisie pour l'instant = pas d'avancement.
                        }
                        $totalChildInputCells ++;
                    }
                }
            }
            if ($totalChildInputCells > 0) {
                $data['advancementInput'] *= 100. / $totalChildInputCells;
                $data['advancementFinishedInput'] *= 100. / $totalChildInputCells;
            }
        }

        $this->addLine($data);
    }

    /**
     * Fonction modifiant un élément.
     *
     * Récupération de la ligne à modifier de la manière suivante :
     *  $this->update['index'].
     *
     * Récupération de la colonne à modifier de la manière suivante :
     *  $this->update['colonne'].
     *
     * Récupération de la nouvelle valeur à modifier de la manière suivante :
     *  $this->update['valeur'].
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie un message d'information et la nouvelle donnée à afficher dans la cellule.
     *
     * @Secure("inputCell")
     */
    function updateelementAction()
    {
        if ($this->update['column'] !== 'inventoryStatus') {
            parent::updateelementAction();
        }

        $childCell = Orga_Model_Cell::load($this->update['index']);
        $childCell->setInventoryStatus($this->update['value']);
        $this->data = $childCell->getInventoryStatus();
        $this->message = __('UI', 'message', 'updated');

        $this->send();
    }


}