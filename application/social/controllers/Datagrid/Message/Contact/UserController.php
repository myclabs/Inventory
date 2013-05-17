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
class Social_Datagrid_Message_Contact_UserController extends UI_Controller_Datagrid
{

    /**
     * {@inheritdoc}
     * @Secure("loggedIn")
     */
    public function getelementsAction()
    {
        /** @var $contacts User_Model_User[] */
        $contacts = User_Model_User::loadList($this->request);
        $this->totalElements = User_Model_User::countTotal($this->request);

        foreach ($contacts as $contact) {
            $data = [];
            $data['firstName'] = $this->cellText($contact->getFirstName());
            $data['lastName'] = $this->cellText($contact->getLastName());
            $data['email'] = $this->cellText($contact->getEmail());
            $data['details'] = $this->cellPopup('user/profile/see/id/' . $contact->getId(),
                                                __('UI', 'name', 'informations'), 'zoom-in');
            $data['newMessage'] = $this->cellLink('social/message/new?idUser=' . $contact->getId(),
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
