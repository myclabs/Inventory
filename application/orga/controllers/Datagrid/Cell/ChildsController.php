<?php
/**
 * Classe Orga_Datagrid_CellController
 * @author valentin.claras
 * @author sidoine.tardieu
 * @package Orga
 */

use Core\Annotation\Secure;

/**
 * Controller des datagrid des cellules
 * @package Orga
 */
class Orga_Datagrid_Cell_ChildsController extends UI_Controller_Datagrid
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
    public function getelementsAction()
    {
        $this->request->setCustomParameters($this->request->filter->getConditions());
        $this->request->filter->setConditions(array());

        $cell = Orga_Model_Cell::load($this->getParam('idCell'));
        $granularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));

        $this->request->order->addOrder(Orga_Model_Cell::QUERY_MEMBERS_HASHKEY);
        $this->request->filter->addCondition(Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT, true);
        $this->request->filter->addCondition(Orga_Model_Cell::QUERY_RELEVANT, true);
        foreach ($cell->loadChildCellsForGranularity($granularity, $this->request) as $childCell) {
            $data = array();
            $data['index'] = $childCell->getId();
            foreach ($childCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }
            $data['link'] = $this->cellLink(
                'orga/cell/details/idCell/' . $childCell->getId(),
                '<i class="icon-share-alt"></i> '.__('UI', 'verb', 'goTo')
            );
            $this->addLine($data);
        }
        $this->totalElements = $cell->countTotalChildCellsForGranularity($granularity, $this->request);

        $this->send();
    }

}
