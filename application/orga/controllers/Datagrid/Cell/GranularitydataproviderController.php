<?php
/**
 * @author valentin.claras
 * @author sidoine.tardieu
 * @package Orga
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * Datagrid de granularity
 * @package Orga
 */
class Orga_Datagrid_Cell_GranularitydataproviderController extends UI_Controller_Datagrid
{

    /**
     * @Inject
     * @var Core_Work_Dispatcher
     */
    private $workDispatcher;

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
     * @Secure("editOrganization")
     */
    public function getelementsAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $this->request->filter->addCondition(Orga_Model_Granularity::QUERY_ORGANIZATION, $organization);
        $this->request->order->addOrder(Orga_Model_Granularity::QUERY_POSITION);
        foreach (Orga_Model_Granularity::loadList($this->request) as $granularity) {
            /** @var Orga_Model_Granularity $granularity */
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
     * @Secure("editOrganization")
     */
    public function updateelementAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $granularity = $organization->getGranularityByRef($this->update['index']);

        switch ($this->update['column']) {
            case 'cellsWithACL':
                $granularity->setCellsWithACL((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithACL();
                break;
            case 'cellsGenerateDWCube':
                $this->workDispatcher->runBackground(
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