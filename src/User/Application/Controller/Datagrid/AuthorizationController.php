<?php
/**
 * @package    User
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use User\Domain\ACL\Resource\EntityResource;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\ACL\ACLService;
use User\Domain\User;

/**
 * @package    User
 * @subpackage Controller
 */
class User_Datagrid_AuthorizationController extends UI_Controller_Datagrid
{

    /**
     * @Inject
     * @var ACLService
     */
    private $aclService;

    /**
     * @Secure("editUser")
     */
    public function getelementsAction()
    {
        /** @var $loggedInUser User */
        $loggedInUser = $this->_helper->auth();

        $authorizations = $loggedInUser->getAuthorizations();

        foreach ($authorizations as $authorization) {

            $data = [];

            $data['action'] = $authorization->getAction()->getLabel();

            $resource = $authorization->getResource();
            if ($resource instanceof NamedResource) {
                $data['resource'] = $resource->getName();
            }
            if ($resource instanceof EntityResource) {
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
