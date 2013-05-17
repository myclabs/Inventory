<?php
/**
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur du Datagrid listant les inventaires.
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */
class Inventory_Datagrid_Cell_InventoriesController extends UI_Controller_Datagrid
{


    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * Récupération des paramètres de tris et filtres de la manière suivante :
     *  $this->request.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->_getParam('nomArgument').
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

        $idCell = $this->_getParam('idCell');
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
        $orgaGranularity = $orgaCell->getGranularity();
        $project = Inventory_Model_Project::loadByOrgaCube($orgaGranularity->getCube());
        $orgaGranularityForInventoryStatus = $project->getOrgaGranularityForInventoryStatus();
        $crossedOrgaGranularity = $orgaGranularityForInventoryStatus->getCrossedGranularity($orgaGranularity);

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
        foreach ($cellDataProvider->getChildCellsForGranularity($crossedOrgaGranularity, $this->request) as $childCellDataProvider) {
            $childOrgaCell = $childCellDataProvider->getOrgaCell();

            $data = array();
            $data['index'] = $childOrgaCell->getKey()['id'];
            foreach ($childOrgaCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }

            if ($crossedOrgaGranularity->getRef() === $orgaGranularityForInventoryStatus->getRef()) {
                $cellDataProviderInventoryStatus = Inventory_Model_CellDataProvider::loadByOrgaCell($childOrgaCell);
            } else {
                $cellDataProviderInventoryStatus = Inventory_Model_CellDataProvider::loadByOrgaCell(
                    $childOrgaCell->getParentCellForGranularity($orgaGranularityForInventoryStatus)
                );
            }
            $data['inventoryStatus'] = $cellDataProviderInventoryStatus->getInventoryStatus();
            if ($data['inventoryStatus'] !== Inventory_Model_CellDataProvider::STATUS_NOTLAUNCHED) {
                $data['advancementInput'] = 0;
                $data['advancementFinishedInput'] = 0;

                $totalChildInputCells = 0;
                foreach (Inventory_Model_AFGranularities::loadList() as $aFGranularities) {
                    $aFInputOrgaGranularity = $aFGranularities->getAFInputOrgaGranularity();
                    if ($aFInputOrgaGranularity->isNarrowerThan($childOrgaCell->getGranularity())) {
                        $inputCells = $childOrgaCell->getChildCellsForGranularity($aFInputOrgaGranularity);
                        foreach ($inputCells as $inputCell) {
                            $inputCellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($inputCell);
                            try {
                                $childAfInputSetPrimary = $inputCellDataProvider->getAFInputSetPrimary();
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
        $this->totalElements = $cellDataProvider->countTotalChildCellsForGranularity($crossedOrgaGranularity, $this->request);

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
     *  $this->_getParam('nomArgument').
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

        $childOrgaCell = Orga_Model_Cell::load($this->update['index']);
        $childCellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($childOrgaCell);
        $childCellDataProvider->setInventoryStatus($this->update['value']);
        $this->data = $childCellDataProvider->getInventoryStatus();
        $this->message = __('UI', 'message', 'updated');

        $this->send();
    }


}