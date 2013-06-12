<?php
/**
 * @author valentin.claras
 * @package Orga
 */

use Core\Annotation\Secure;

/**
 * Controller du datagrid de configuration des formulaires des cellules.
 * @package Orga
 */
class Orga_Datagrid_Cell_Afgranularities_ConfigController extends UI_Controller_Datagrid
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

        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);

        $aFConfigOrgaGranularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));
        $aFInputOrgaGranularity = Orga_Model_Granularity::load($this->getParam('idInputGranularity'));

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
        foreach ($cell->loadChildCellsForGranularity($aFConfigOrgaGranularity, $this->request) as $configChildCell) {
            $data = array();
            $data['index'] = $configChildCell->getId();
            foreach ($configChildCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }
            try {
                $cellsGroupDataProvider = $configChildCell->getCellsGroupForInputGranularity($aFInputOrgaGranularity);
                $data['aF'] = $this->cellList($cellsGroupDataProvider->getAF()->getRef());
            } catch (Core_Exception_UndefinedAttribute $e) {
                // Aucun AF n'a encore été spécifié pour cette cellule et granularité.
            }
            $this->addLine($data);
        }
        $this->totalElements = $cell->countTotalChildCellsForGranularity($aFConfigOrgaGranularity, $this->request);

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

        $inputGranularity = Orga_Model_Granularity::load($this->getParam('idInputGranularity'));

        $configCell = Orga_Model_Cell::load($this->update['index']);

        $aFRef = $this->update['value'];
        if (empty($aFRef)) {
            $aF = null;
        } else {
            $aF = AF_Model_AF::loadByRef($aFRef);
        }

        $cellsGroupDataProvider = $configCell->getCellsGroupForInputGranularity($inputGranularity);
        if ($aF !== null) {
            $cellsGroupDataProvider->setAF($aF);
        } else {
            $cellsGroupDataProvider->setAF();
        }
        $this->data = $this->cellList($aF);

        if ($aF !== null) {
            $this->data = $this->cellList($aF->getRef());
        } else {
            $this->data = $this->cellList(null);
        }
        $this->message = __('UI', 'message', 'updated');

        $this->send();
    }


}
