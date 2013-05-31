<?php
/**
 * Classe Orga_Datagrid_RelevantController
 * @author valentin.claras
 * @author sidoine.tardieu
 * @package Orga
 */

use Core\Annotation\Secure;

/**
 * Controller des datagrid des cellules
 * @package Orga
 */
class Orga_Datagrid_RelevantController extends UI_Controller_Datagrid
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
     * @Secure("viewProject")
     */
    public function getelementsAction()
    {
        $this->request->setCustomParameters($this->request->filter->getConditions());
        $this->request->filter->setConditions(array());

        $cell = Orga_Model_Cell::load(array('id' => $this->getParam('idCell')));
        $granularity = Orga_Model_Granularity::load(array('id' => $this->getParam('idGranularity')));

        $this->request->order->addOrder(Orga_Model_Cell::QUERY_MEMBERS_HASHKEY);
        foreach ($cell->getChildCellsForGranularity($granularity, $this->request) as $childCell) {
            $data = array();
            $data['index'] = $childCell->getKey()['id'];
            foreach ($childCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }
            $data['relevant'] = $childCell->getRelevant();
            $data['allParentsRelevant'] = $childCell->getAllParentsRelevant();
            $this->addLine($data);
        }
        $this->totalElements = $cell->countTotalChildCellsForGranularity($granularity, $this->request);

        $this->send();
    }

    /**
     * Fonction modifiant un élément.
     *
     * Récupération de la ligne à modifier de la manière suivante :
     *  $this->update['index'].
     *
     * Récupération de la colonne à modifier de la manière suivante :
     *  $this->update['column'].
     *
     * Récupération de la nouvelle valeur à modifier de la manière suivante :
     *  $this->update['value'].
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie un message d'information et la nouvelle donnée à afficher dans la cellule.
     *
     * @Secure("editProject")
     */
    function updateelementAction()
    {
        if ($this->update['column'] !== 'relevant') {
            parent::updateelementAction();
        }

        $childCell = Orga_Model_Cell::load(array('id' => $this->update['index']));

        $childCell->setRelevant((bool) $this->update['value']);
        $this->data = $childCell->getRelevant();

        $this->message = __('UI', 'message', 'updated');

        $this->send();
    }

}
