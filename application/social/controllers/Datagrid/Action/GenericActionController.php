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
class Social_Datagrid_Action_GenericActionController extends UI_Controller_Datagrid
{

    /**
     * {@inheritdoc}
     * @Secure("viewGenericActions")
     */
    public function getelementsAction()
    {
        $this->request->order->addOrder(Social_Model_Theme::QUERY_LABEL, Core_Model_Order::ORDER_ASC,
                                        Social_Model_Theme::getAlias());
        $this->request->order->addOrder(Social_Model_GenericAction::QUERY_LABEL, Core_Model_Order::ORDER_ASC,
                                        Social_Model_GenericAction::getAlias());
        /** @var $genericActions Social_Model_GenericAction[] */
        $genericActions = Social_Model_GenericAction::loadList($this->request);

        foreach ($genericActions as $genericAction) {
            $theme = $genericAction->getTheme();

            $data = [];
            $data['index'] = $genericAction->getId();
            $data['theme'] = $this->cellList($theme->getId());
            $data['label'] = $this->cellText($genericAction->getLabel());
            $data['description'] = $this->cellLongText('social/action/popup-action-description/id/'
                                                           . $genericAction->getId(),
                                                       null, __('UI', 'name', 'description'), 'zoom-in');
            $data['contextActionCount'] = $this->cellNumber(count($genericAction->getContextActions()));
            $data['details'] = $this->cellLink('social/action/generic-action-details/id/' . $genericAction->getId(),
                                               __('UI', 'name', 'details'), 'share-alt');
            $this->addLine($data);
        }
        $this->totalElements = Social_Model_GenericAction::countTotal($this->request);
        $this->send();
    }

    /**
     * {@inheritdoc}
     * @Secure("addGenericAction")
     */
    public function addelementAction()
    {
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
     * {@inheritdoc}
     * @Secure("deleteGenericAction")
     */
    public function deleteelementAction()
    {
        /** @var $genericAction Social_Model_GenericAction */
        $genericAction = Social_Model_GenericAction::load($this->delete);

        if (count($genericAction->getContextActions()) > 0) {
            throw new Core_Exception_User('Social', 'actionTemplate', 'deletionForbidden');
        } else {
            $genericAction->delete();
            $this->message = __('UI', 'message', 'deleted');
        }

        $this->send();
    }

}
