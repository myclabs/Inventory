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
class Social_Datagrid_Message_Contact_UserGroupController extends UI_Controller_Datagrid
{

    /**
     * {@inheritdoc}
     * @Secure("loggedIn")
     */
    public function getelementsAction()
    {
        $this->request->order->addOrder(Social_Model_UserGroup::QUERY_LABEL);

        /** @var $contacts Social_Model_UserGroup[] */
        $contacts = Social_Model_UserGroup::loadList($this->request);
        $this->totalElements = Social_Model_UserGroup::countTotal($this->request);

        foreach ($contacts as $contact) {
            $data = [];
            $data['label'] = $this->cellText($contact->getLabel());
            $data['details'] = $this->cellPopup('social/user-group/details/id/' . $contact->getId(),
                                                __('UI', 'name', 'members'), 'zoom-in');
            $data['newMessage'] = $this->cellLink('social/message/new?idUserGroup=' . $contact->getId(),
                                              __('Social', 'contact', 'sendMessageTo'), 'envelope');
            $this->addLine($data);
        }
        $this->send();
    }

    /**
     * {@inheritdoc}
     */
    public function addelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery('action impossible');
    }

    /**
     * {@inheritdoc}
     */
    public function updateelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery('action impossible');
    }

    /**
     * {@inheritdoc}
     */
    public function deleteelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery('action impossible');
    }

}
