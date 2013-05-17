<?php
/**
 * @author valentin.claras
 * @package Inventory
 */

use Core\Annotation\Secure;

/**
 * Controller du datagrid de configuration des formulaires des cellules.
 * @package Inventory
 */
class Inventory_Datagrid_Cell_Afgranularities_ConfigController extends UI_Controller_Datagrid
{

    /**
     * Methode appelee pour remplir le tableau.
     * @Secure("editCell")
     */
    public function getelementsAction()
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

        $aFConfigOrgaGranularity = Orga_Model_Granularity::load(array('id' => $this->_getParam('idGranularity')));
        $aFInputOrgaGranularity = Orga_Model_Granularity::load(array('id' => $this->_getParam('idInputGranularity')));
        $aFGranularities = Inventory_Model_AFGranularities::loadByAFInputOrgaGranularity($aFInputOrgaGranularity);

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
        foreach ($cellDataProvider->getChildCellsForGranularity($aFConfigOrgaGranularity, $this->request) as $childCellDataProvider) {
            $childOrgaCell = $childCellDataProvider->getOrgaCell();

            $data = array();
            $data['index'] = $childOrgaCell->getKey()['id'];
            foreach ($childOrgaCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }
            try {
                $cellsGroupDataProvider = $aFGranularities->getCellsGroupDataProviderForContainerCellDataProvider(
                    $childCellDataProvider
                );
                $data['aF'] = $this->cellList($cellsGroupDataProvider->getAF()->getRef());
            } catch (Core_Exception_NotFound $e) {
                // Aucun AF n'a encore été spécifié pour cette cellule et granularité.
            }
            $this->addLine($data);
        }
        $this->totalElements = $cellDataProvider->countTotalChildCellsForGranularity($aFConfigOrgaGranularity, $this->request);

        $this->send();
    }

    /**
     * Modifie les valeurs d'un element.
     * @Secure("editCell")
     */
    public function updateelementAction()
    {
        if ($this->update['column'] !== 'aF') {
            parent::updateelementAction();
        }

        $aFInputOrgaGranularity = Orga_Model_Granularity::load(array('id' => $this->_getParam('idInputGranularity')));
        $aFGranularities = Inventory_Model_AFGranularities::loadByAFInputOrgaGranularity($aFInputOrgaGranularity);

        $childOrgaCell = Orga_Model_Cell::load($this->update['index']);
        $childCellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($childOrgaCell);

        $aFRef = $this->update['value'];
        if (empty($aFRef)) {
            $aF = null;
        } else {
            $aF = AF_Model_AF::loadByRef($aFRef);
        }

        try {
            $cellsGroupDataProvider = $aFGranularities->getCellsGroupDataProviderForContainerCellDataProvider(
                $childCellDataProvider
            );
            if ($aF !== null) {
                $cellsGroupDataProvider->setAF($aF);
                $this->data = $this->cellList($aF->getRef());
            } else {
                $cellsGroupDataProvider->delete();
            }
        } catch (Core_Exception_NotFound $e) {
            // Aucun AF n'a encore été spécifié pour cette cellule et granularité.
            if ($aF !== null) {
                $cellsGroupDataProvider = new Inventory_Model_CellsGroupDataProvider();
                $cellsGroupDataProvider->setContainerCellDataProvider($childCellDataProvider);
                $cellsGroupDataProvider->setAFGranularities($aFGranularities);
                $cellsGroupDataProvider->setAF($aF);
                $cellsGroupDataProvider->save();
            }
        }

        if ($aF !== null) {
            $this->data = $this->cellList($aF->getRef());
        } else {
            $this->data = $this->cellList(null);
        }
        $this->message = __('UI', 'message', 'updated');

        $this->send();
    }


}
