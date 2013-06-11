<?php
/**
 * @package    User
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * @package    User
 * @subpackage Controller
 */
class User_Datagrid_AuthorizationController extends UI_Controller_Datagrid
{

    /**
     * @var User_Service_ACL
     */
    private $aclService;

    /**
     * (non-PHPdoc)
     */
    public function init()
    {
        parent::init();
        $this->aclService = $this->get('User_Service_ACL');
    }

    /**
     * @Secure("editUser")
     */
    public function getelementsAction()
    {
        /** @var $loggedInUser User_Model_User */
        $loggedInUser = $this->_helper->auth();

        $authorizations = $loggedInUser->getDirectAuthorizations();

        foreach ($authorizations as $authorization) {

            $data = [];

            $data['action'] = $authorization->getAction()->getLabel();

            $resource = $authorization->getResource();
            if ($resource instanceof User_Model_Resource_Named) {
                $data['resource'] = $resource->getName();
            }
            if ($resource instanceof User_Model_Resource_Entity) {
                $entity = $resource->getEntity();
                if ($entity) {
                    $data['resource'] = $resource->getEntityName() . ' ' . $resource->getEntityIdentifier();
                } else {
                    $data['resource'] = $resource->getEntityName();
                }
            }

            $this->addLine($data);
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     */
    public function addelementAction()
    {
    }

    /**
     * (non-PHPdoc)
     */
    public function updateelementAction()
    {
    }

    /**
     * (non-PHPdoc)
     */
    public function deleteelementAction()
    {
    }

}
