<?php
/**
 * @author valentin.claras
 * @author sidoine.tardieu
 * @package Orga
 */

use Core\Annotation\Secure;

/**
 * Datagrid de granularity
 * @package Orga
 */
class Inventory_Datagrid_Cell_GranularitydataproviderController extends UI_Controller_Datagrid
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
     * @Secure("editProject")
     */
    public function getelementsAction()
    {
        $project = Inventory_Model_Project::load(array('id' => $this->_getParam('idProject')));
        $this->request->filter->addCondition(Orga_Model_Granularity::QUERY_CUBE, $project->getOrgaCube());
        $this->request->order->addOrder(Orga_Model_Granularity::QUERY_POSITION);
        foreach (Orga_Model_Granularity::loadList($this->request) as $orgaGranularity) {
            $granularityDataProvider = Inventory_Model_GranularityDataProvider::loadByOrgaGranularity(
                $orgaGranularity
            );
            $data = array();
            $data['index'] = $orgaGranularity->getRef();
            $data['label'] = $orgaGranularity->getLabel();
            $data['cellsWithACL'] = $granularityDataProvider->getCellsWithACL();
            $data['cellsGenerateDWCube'] = $granularityDataProvider->getCellsGenerateDWCubes();
            $data['cellsWithOrgaTab'] = $granularityDataProvider->getCellsWithOrgaTab();
            $data['cellsWithAFConfigTab'] = $granularityDataProvider->getCellsWithAFConfigTab();
            $data['cellsWithSocialGenericActions'] = $granularityDataProvider->getCellsWithSocialGenericActions();
            $data['cellsWithSocialContextActions'] = $granularityDataProvider->getCellsWithSocialContextActions();
            $data['cellsWithInputDocs'] = $granularityDataProvider->getCellsWithInputDocs();
            $this->addLine($data);
        }
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
     *  $this->_getParam('nomArgument').
     *
     * Renvoie un message d'information et la nouvelle donnée à afficher dans la cellule.
     *
     * @Secure("editProject")
     */
    public function updateelementAction()
    {
        $project = Inventory_Model_Project::load(array('id' => $this->_getParam('idProject')));
        $orgaGranularity = Orga_Model_Granularity::loadByRefAndCube($this->update['index'], $project->getOrgaCube());
        $granularityDataProvider = Inventory_Model_GranularityDataProvider::loadByOrgaGranularity($orgaGranularity);

        switch ($this->update['column']) {
            case 'cellsWithACL':
                $granularityDataProvider->setCellsWithACL((bool) $this->update['value']);
                $this->data = $granularityDataProvider->getCellsWithACL();
                break;
            case 'cellsGenerateDWCube':
                /**@var Core_Work_Dispatcher $dispatcher */
                $dispatcher = Zend_Registry::get('workDispatcher');
                $dispatcher->runBackground(
                    new Inventory_Work_Task_SetGranularityDataProviderCellsGenerateDWCubes(
                        $granularityDataProvider,
                        $this->update['value']
                    )
                );
                $this->data = $granularityDataProvider->getCellsGenerateDWCubes();
                $this->message = __('UI', 'message', 'updatedLater',
                                    array('GRANULARITY' => $orgaGranularity->getLabel()));
                $this->send();
                return;
            case 'cellsWithOrgaTab':
                $granularityDataProvider->setCellsWithOrgaTab((bool) $this->update['value']);
                $this->data = $granularityDataProvider->getCellsWithOrgaTab();
                break;
            case 'cellsWithAFConfigTab':
                $granularityDataProvider->setCellsWithAFConfigTab((bool) $this->update['value']);
                $this->data = $granularityDataProvider->getCellsWithAFConfigTab();
                break;
            case 'cellsWithSocialGenericActions':
                $granularityDataProvider->setCellsWithSocialGenericActions((bool) $this->update['value']);
                $this->data = $granularityDataProvider->getCellsWithSocialGenericActions();
                break;
            case 'cellsWithSocialContextActions':
                $granularityDataProvider->setCellsWithSocialContextActions((bool) $this->update['value']);
                $this->data = $granularityDataProvider->getCellsWithSocialContextActions();
                break;
            case 'cellsWithInputDocs':
                $granularityDataProvider->setCellsWithInputDocs((bool) $this->update['value']);
                $this->data = $granularityDataProvider->getCellsWithInputDocs();
                break;
            default:
                parent::updateelementAction();
                break;
        }
        $this->message = __('UI', 'message', 'updated', array('GRANULARITY' => $orgaGranularity->getLabel()));

        $this->send();
    }
}