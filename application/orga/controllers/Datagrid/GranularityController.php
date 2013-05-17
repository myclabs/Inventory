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
class Orga_Datagrid_GranularityController extends UI_Controller_Datagrid
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
     * @Secure("viewOrgaCube")
     */
    public function getelementsAction()
    {
        $cube = Orga_Model_Cube::load(array('id' => $this->_getParam('idCube')));
        $this->request->filter->addCondition(Orga_Model_Granularity::QUERY_CUBE, $cube);
        $this->request->order->addOrder(Orga_Model_Granularity::QUERY_POSITION);
        foreach (Orga_Model_Granularity::loadList($this->request) as $granularity) {
            $data = array();
            $data['index'] = $granularity->getRef();
            $listAxes = array();
            foreach ($granularity->getAxes() as $axis) {
                $listAxes[] = $axis->getRef();
            }
            $data['axes'] = $this->cellList($listAxes);
            $data['navigable'] = $granularity->isNavigable();
            if (!($granularity->hasAxes())) {
                $this->editableCell($data['navigable'], false);
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
     * @Secure("editOrgaCube")
     */
    public function addelementAction()
    {
        $cube = Orga_Model_Cube::load(array('id' => $this->_getParam('idCube')));

        $refAxes = $this->getAddElementValue('axes');
        $listAxes = array();
        $refGranularity = '';
        if (empty($refAxes)) {
            $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'emptyGranularity'));
        }
        foreach ($this->getAddElementValue('axes') as $refAxis) {
            $refGranularity .= $refAxis . '|';
            $axis = Orga_Model_Axis::loadByRefAndCube($refAxis, $cube);
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
            Orga_Model_Granularity::loadByRefAndCube($refGranularity, $cube);
            $this->setAddElementErrorMessage('axes', __('Orga', 'granularity', 'granularityAlreadyExists'));
        } catch (Core_Exception_NotFound $e) {
            // La granularité n'existe pas déjà.
        }
        if (empty($this->_addErrorMessages)) {
            /**@var Core_Work_Dispatcher $dispatcher */
            $dispatcher = Zend_Registry::get('workDispatcher');
            $dispatcher->runBackground(
                new Orga_Work_Task_AddGranularity(
                    $cube,
                    $listAxes,
                    $this->getAddElementValue('navigable')
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
     *  $this->_getParam('nomArgument').
     *
     * Renvoie un message d'information.
     *
     * @Secure("editOrgaCube")
     */
    public function deleteelementAction()
    {
        $cube = Orga_Model_Cube::load(array('id' => $this->_getParam('idCube')));
        $granularity = Orga_Model_Granularity::loadByRefAndCube($this->delete, $cube);

        $granularity->delete();

        $entityManagers = Zend_Registry::get('EntityManagers');
        try {
            $entityManagers['default']->flush();
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
     *  $this->_getParam('nomArgument').
     *
     * Renvoie un message d'information et la nouvelle donnée à afficher dans la cellule.
     *
     * @Secure("editOrgaCube")
     */
    public function updateelementAction()
    {
        $cube = Orga_Model_Cube::load(array('id' => $this->_getParam('idCube')));
        $granularity = Orga_Model_Granularity::loadByRefAndCube($this->update['index'], $cube);

        switch ($this->update['column']) {
            case 'navigable':
                $granularity->setNavigability((bool) $this->update['value']);
                $this->message = __('UI', 'message', 'updated', array('GRANULARITY' => $granularity->getLabel()));
                break;
            default:
                parent::updateelementAction();
                break;
        }
        $this->data = $granularity->isNavigable();

        $this->send();
    }
}