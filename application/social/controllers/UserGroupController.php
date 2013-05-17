<?php
/**
 * @author  matthieu.napoli
 * @package Social
 */

use Core\Annotation\Secure;

/**
 * @package Social
 */
class Social_UserGroupController extends Core_Controller_Ajax
{

    /**
     * @Secure("viewUserGroup")
     */
    public function detailsAction()
    {
        $this->view->group = Social_Model_UserGroup::load($this->_getParam('id'));
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }
    }

}
