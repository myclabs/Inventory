<?php
/**
 * @author  joseph.rouffet
 * @author  matthieu.napoli
 * @package Social
 */

use Core\Annotation\Secure;

/**
 * @package Social
 */
class Orga_Datagrid_Cell_Actions_GenericController extends UI_Controller_Datagrid
{

    /**
     * {@inheritdoc}
     * @Secure("viewGenericActions")
     */
    public function getelementsAction()
    {
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);

        foreach ($cell->getSocialGenericActions() as $genericAction) {
            $theme = $genericAction->getTheme();

            $data = [];
            $data['index'] = $genericAction->getId();
            $data['theme'] = $this->cellList($theme->getId());
            $data['label'] = $this->cellText($genericAction->getLabel());
            $data['description'] = $this->cellLongText('social/action/popup-action-description/id/'
                                                           . $genericAction->getId(),
                                                           null, __('UI', 'name', 'description'), 'zoom-in');
            $data['contextActionCount'] = $this->cellNumber(count($genericAction->getContextActions()));
            $data['details'] = $this->cellLink(
                'orga/cell/genericactiondetails/id/'.$genericAction->getId().'/idCell/'.$idCell,
                __('UI', 'name', 'details'),
                'share-alt'
            );
            $this->addLine($data);
        }
        $this->send();
    }

    /**
     * {@inheritdoc}
     * @Secure("addGenericAction")
     */
    public function addelementAction()
    {
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);

        $idTheme = $this->getAddElementValue('theme');
        if (empty($idTheme)) {
            $this->setAddElementErrorMessage('theme', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $label = $this->getAddElementValue('label');
        if (empty($label)) {
            $this->setAddElementErrorMessage('label', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if (count($this->_addErrorMessages) === 0) {
            /** @var $theme Social_Model_Theme */
            $theme = Social_Model_Theme::load($idTheme);
            $genericAction = new Social_Model_GenericAction($theme);
            $genericAction->setLabel($label);
            $genericAction->setDescription($this->getAddElementValue('description'));
            $genericAction->save();
            $cell->addSocialGenericAction($genericAction);
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * {@inheritdoc}
     * @Secure("editGenericAction")
     */
    public function updateelementAction()
    {
        /** @var $genericAction Social_Model_GenericAction */
        $genericAction = Social_Model_GenericAction::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'label':
                $genericAction->setLabel($newValue);
                $this->data = $genericAction->getLabel();
                break;
        }
        $this->message = __('UI', 'message', 'updated');
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
     * @Secure("deleteGenericAction")
     */
    function deleteelementAction()
    {
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);

        /** @var $genericAction Social_Model_GenericAction */
        $genericAction = Social_Model_GenericAction::load($this->delete);

        if (count($genericAction->getContextActions()) > 0) {
            throw new Core_Exception_User('Social', 'actionTemplate', 'deletionForbidden');
        } else {
            $cell->removeSocialGenericAction($genericAction);
            $genericAction->delete();
            $this->message = __('UI', 'message', 'deleted');
        }

        $this->send();
    }

}
