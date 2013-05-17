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
class Social_Datagrid_Action_ThemeController extends UI_Controller_Datagrid
{

    /**
     * {@inheritdoc}
     * @Secure("adminThemes")
     */
    public function getelementsAction()
    {
        //Ordre conventionnel
        $this->request->order->addOrder(Social_Model_Theme::QUERY_LABEL);
        /** @var $themes Social_Model_Theme[] */
        $themes = Social_Model_Theme::loadList($this->request);

        foreach ($themes as $theme) {
            $data = array();
            $data['index'] = $theme->getId();
            $data['label'] = $this->cellText($theme->getLabel());
            $data['number'] = $this->cellNumber($theme->getGenericActionCount());

            $this->addLine($data);
        }
        $this->totalElements = Social_Model_Theme::countTotal($this->request);
        $this->send();
    }

    /**
     * {@inheritdoc}
     * @Secure("adminThemes")
     */
    public function addelementAction()
    {
        $label = $this->getAddElementValue('label');
        if (empty($label)) {
            $this->setAddElementErrorMessage('label', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if (count($this->_addErrorMessages) === 0) {
            $theme = new Social_Model_Theme($label);
            $theme->save();
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * {@inheritdoc}
     * @Secure("adminThemes")
     */
    public function updateelementAction()
    {
        /** @var $theme Social_Model_Theme */
        $theme = Social_Model_Theme::load($this->update['index']);
        $label = $this->update['value'];
        if (empty($label)) {
            throw new Core_Exception_User('UI', 'formValidation', 'emptyRequiredField');
        }

        $theme->setLabel($label);
        $theme->save();

        $this->message = __('UI', 'message', 'updated');

        $this->data = $theme->getLabel();
        $this->send();
    }

    /**
     * {@inheritdoc}
     * @Secure("adminThemes")
     */
    public function deleteelementAction()
    {
        /** @var $theme Social_Model_Theme */
        $theme = Social_Model_Theme::load($this->delete);

        $theme->delete();

        $entityManagers = Zend_Registry::get('EntityManagers');
        try {
            $entityManagers['default']->flush();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            throw new Core_Exception_User('Social', 'actionTheme', 'deletionForbidden');
        }

        $this->message = __('UI', 'message', 'deleted');

        $this->send();
    }

}
