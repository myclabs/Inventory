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
class Orga_Datagrid_Cell_GranularitydataproviderController extends UI_Controller_Datagrid
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
     * @Secure("editProject")
     */
    public function getelementsAction()
    {
        $project = Orga_Model_Project::load($this->getParam('idProject'));
        $this->request->filter->addCondition(Orga_Model_Granularity::QUERY_PROJECT, $project);
        $this->request->order->addOrder(Orga_Model_Granularity::QUERY_POSITION);
        foreach (Orga_Model_Granularity::loadList($this->request) as $granularity) {
            $data = array();
            $data['index'] = $granularity->getRef();
            $data['label'] = $granularity->getLabel();
            $data['cellsWithACL'] = $granularity->getCellsWithACL();
            $data['cellsGenerateDWCube'] = $granularity->getCellsGenerateDWCubes();
            $data['cellsWithOrgaTab'] = $granularity->getCellsWithOrgaTab();
            $data['cellsWithAFConfigTab'] = $granularity->getCellsWithAFConfigTab();
            $data['cellsWithSocialGenericActions'] = $granularity->getCellsWithSocialGenericActions();
            $data['cellsWithSocialContextActions'] = $granularity->getCellsWithSocialContextActions();
            $data['cellsWithInputDocs'] = $granularity->getCellsWithInputDocs();
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
     *  $this->getParam('nomArgument').
     *
     * Renvoie un message d'information et la nouvelle donnée à afficher dans la cellule.
     *
     * @Secure("editProject")
     */
    public function updateelementAction()
    {
        $project = Orga_Model_Project::load($this->getParam('idProject'));
        $granularity = Orga_Model_Granularity::loadByRefAndProject($this->update['index'], $project);

        switch ($this->update['column']) {
            case 'cellsWithACL':
                $granularity->setCellsWithACL((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithACL();
                break;
            case 'cellsGenerateDWCube':
                /**@var Core_Work_Dispatcher $dispatcher */
                $dispatcher = Zend_Registry::get('workDispatcher');
                $dispatcher->runBackground(
                    new Orga_Work_Task_SetGranularityCellsGenerateDWCubes(
                        $granularity,
                        $this->update['value']
                    )
                );
                $this->data = $granularity->getCellsGenerateDWCubes();
                $this->message = __('UI', 'message', 'updatedLater',
                                    array('GRANULARITY' => $granularity->getLabel()));
                $this->send();
                return;
            case 'cellsWithOrgaTab':
                $granularity->setCellsWithOrgaTab((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithOrgaTab();
                break;
            case 'cellsWithAFConfigTab':
                $granularity->setCellsWithAFConfigTab((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithAFConfigTab();
                break;
            case 'cellsWithSocialGenericActions':
                $granularity->setCellsWithSocialGenericActions((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithSocialGenericActions();
                break;
            case 'cellsWithSocialContextActions':
                $granularity->setCellsWithSocialContextActions((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithSocialContextActions();
                break;
            case 'cellsWithInputDocs':
                $granularity->setCellsWithInputDocuments((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithInputDocuments();
                break;
            default:
                parent::updateelementAction();
                break;
        }
        $this->message = __('UI', 'message', 'updated', array('GRANULARITY' => $granularity->getLabel()));

        $this->send();
    }
}