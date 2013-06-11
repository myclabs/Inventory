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

        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);
        $granularity = $cell->getGranularity();
        $project = $granularity->getProject();
        $granularityForInventoryStatus = $project->getGranularityForInventoryStatus();
        $crossedGranularity = $granularityForInventoryStatus->getCrossedGranularity($granularity);

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
            $data = array();
            $data['index'] = $childCell->getId();
            foreach ($childCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }

            if ($crossedGranularity->getRef() === $granularityForInventoryStatus->getRef()) {
                $cellInventoryStatus = $childCell;
            } else {
                $cellInventoryStatus = $childCell->getParentCellForGranularity($granularityForInventoryStatus);
            }
            $data['inventoryStatus'] = $cellInventoryStatus->getInventoryStatus();
            if ($data['inventoryStatus'] !== Orga_Model_Cell::STATUS_NOTLAUNCHED) {
                $data['advancementInput'] = 0;
                $data['advancementFinishedInput'] = 0;

                $totalChildInputCells = 0;
                foreach (Orga_Model_AFGranularities::loadList() as $aFGranularities) {
                    $aFInputGranularity = $aFGranularities->getAFInputOrgaGranularity();
                    if ($aFInputGranularity->isNarrowerThan($childCell->getGranularity())) {
                        $inputCells = $childCell->getChildCellsForGranularity($aFInputGranularity);
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
        $this->totalElements = $cell->countTotalChildCellsForGranularity($crossedGranularity, $this->request);

        $this->send();
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