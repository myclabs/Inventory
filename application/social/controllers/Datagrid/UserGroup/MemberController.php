<?php
/**
 * @package    Social
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * @package    Social
 * @subpackage Controller
 */
class Social_Datagrid_UserGroup_MemberController extends UI_Controller_Datagrid
{

    /**
     * @Secure("viewUserGroup")
     */
    public function getelementsAction()
    {
        /** @var $group Social_Model_UserGroup */
        $group = Social_Model_UserGroup::load($this->getParam('id'));
        $users = $group->getUsers();

        foreach ($users as $user) {
            $data = [];
            $data['index'] = $user->getId();
            $data['prenom'] = $user->getFirstName();
            $data['nom'] = $user->getLastName();
            $data['email'] = $user->getEmail();
            $this->addLine($data);
        }
        $this->send();
    }

    /**
     * {@inheritdoc}
     */
    public function addelementAction()
    {
        throw new Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function updateelementAction()
    {
        throw new Exception();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteelementAction()
    {
        throw new Exception();
    }

}
