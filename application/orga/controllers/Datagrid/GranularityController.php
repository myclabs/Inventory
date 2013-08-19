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
class Orga_Datagrid_GranularityController extends UI_Controller_Datagrid
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
     * @Secure("viewOrganization")
     */
    public function getelementsAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $this->request->filter->addCondition(Orga_Model_Granularity::QUERY_ORGANIZATION, $organization);
        $this->request->order->addOrder(Orga_Model_Granularity::QUERY_POSITION);
        /**@var Orga_Model_Granularity $granularity */
        foreach (Orga_Model_Granularity::loadList($this->request) as $granularity) {
            $data = array();
            $data['index'] = $granularity->getId();
            $listAxes = array();
            foreach ($granularity->getAxes() as $axis) {
                $listAxes[] = $axis->getRef();
            }
            $data['axes'] = $this->cellList($listAxes);
            $data['navigable'] = $granularity->isNavigable();
            $data['orgaTab'] = $granularity->getCellsWithOrgaTab();
            $data['aCL'] = $granularity->getCellsWithACL();
            $data['aFTab'] = $granularity->getCellsWithAFConfigTab();
            $data['dW'] = $granularity->getCellsGenerateDWCubes();
            $data['genericActions'] = $granularity->getCellsWithSocialGenericActions();
            $data['contextActions'] = $granularity->getCellsWithSocialContextActions();
            $data['inputDocuments'] = $granularity->getCellsWithInputDocuments();
            if (!($granularity->hasAxes())) {
                $this->editableCell($data['navigable'], false);
                $this->editableCell($data['orgaTab'], false);
                $data['delete'] = false;
            }
            $this->addLine($data);
        }
        $this->send();
    }

    /**
     * Fonction ajoutant un élément.
     *
     * Renvoie un message d'information.
     *
     * @see getAddElementValue
     * @see setAddElementErrorMessage
     *
     * @Secure("editOrganization")
     */
    public function addelementAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));

        $refAxes = $this->getAddElementValue('axes');
        $listAxes = array();
        $refGranularity = '';
        if (empty($refAxes)) {
            $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'emptyGranularity'));
        }

        foreach ($refAxes as $refAxis) {
            $refGranularity .= $refAxis . '|';
            $axis = Orga_Model_Axis::loadByRefAndOrganization($refAxis, $organization);
            // On regarde si les axes précédement ajouter ne sont pas lié hierachiquement à l'axe actuel.
            if (!$axis->isTransverse($listAxes)) {
                $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'hierarchicallyLinkedAxes'));
                break;
            } else {
                $listAxes[] = $axis;
            }
        }
        $refGranularity = substr($refGranularity, 0, -1);

        try {
            Orga_Model_Granularity::loadByRefAndOrganization($refGranularity, $organization);
            $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'granularityAlreadyExists'));
        } catch (Core_Exception_NotFound $e) {
            // La granularité n'existe pas déjà.
        }

        if (empty($this->_addErrorMessages)) {
            $this->workDispatcher->runBackground(
                new Orga_Work_Task_AddGranularity(
                    $organization,
                    $listAxes,
                    (bool) $this->getAddElementValue('navigable'),
                    (bool) $this->getAddElementValue('orgaTab'),
                    (bool) $this->getAddElementValue('aCL'),
                    (bool) $this->getAddElementValue('aFTab'),
                    (bool) $this->getAddElementValue('dW'),
                    (bool) $this->getAddElementValue('genericActions'),
                    (bool) $this->getAddElementValue('contextActions'),
                    (bool) $this->getAddElementValue('inputDocuments')
                )
            );
            $this->message = __('UI', 'message', 'addedLater');
        }

        $this->send();
    }

    /**
     * Fonction supprimant un élément.
     *
     * Récupération de la ligne à supprimer de la manière suivante :
     *  $this->delete.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie un message d'information.
     *
     * @Secure("editOrganization")
     */
    public function deleteelementAction()
    {
        $granularity = Orga_Model_Granularity::load($this->delete);
        if ($granularity->getCellsWithACL()) {
            throw new Core_Exception_User('Orga', 'granularity', 'granularityCantBeDeleted');
        }

        $granularity->delete();

        try {
            $this->entityManager->flush();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            throw new Core_Exception_User('Orga', 'granularity', 'granularityCantBeDeleted');
        }

        $this->message = __('UI', 'message', 'deleted', array('GRANULARITY' => $granularity->getLabel()));

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
        $granularity = Orga_Model_Granularity::load($this->update['index']);

        switch ($this->update['column']) {
            case 'navigable':
                $granularity->setNavigability((bool) $this->update['value']);
                $this->data = $granularity->isNavigable();
                break;
            case 'orgaTab':
                $granularity->setCellsWithOrgaTab((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithOrgaTab();
                break;
            case 'aCL':
                foreach ($granularity->getCells() as $cell) {
                    $cellResource = User_Model_Resource_Entity::loadByEntity($cell);
                    foreach ($cellResource->getLinkedSecurityIdentities() as $linkedIdentity) {
                        if (!($linkedIdentity instanceof User_Model_Role) || (count($linkedIdentity->getUsers()) > 0)) {
                            throw new Core_Exception_User('Orga', 'exceptions', 'cellHasUsers');
                        }
                    }
                }
                $granularity->setCellsWithACL((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithACL();
                break;
            case 'aFTab':
                $granularity->setCellsWithAFConfigTab((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithAFConfigTab();
                break;
            case 'dW':
                $granularity->setCellsGenerateDWCubes((bool) $this->update['value']);
                $this->data = $granularity->getCellsGenerateDWCubes();
                break;
            case 'genericActions':
                $granularity->setCellsWithSocialGenericActions((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithSocialGenericActions();
                break;
            case 'contextActions':
                $granularity->setCellsWithSocialContextActions((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithSocialContextActions();
                break;
            case 'inputDocuments':
                $granularity->setCellsWithInputDocuments((bool) $this->update['value']);
                $this->data = $granularity->getCellsWithInputDocuments();
                break;
            default:
                parent::updateelementAction();
                break;
        }
        $granularity->save();
        $this->message = __('UI', 'message', 'updated', array('GRANULARITY' => $granularity->getLabel()));

        $this->send();
    }
}