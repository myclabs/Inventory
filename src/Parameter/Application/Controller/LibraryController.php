<?php

use Core\Annotation\Secure;
use MyCLabs\ACL\ACLManager;
use Parameter\Domain\ParameterLibrary;
use User\Domain\ACL\Actions;

/**
 * @author matthieu.napoli
 */
class Parameter_LibraryController extends Core_Controller
{
    /**
     * @Inject
     * @var ACLManager
     */
    private $aclManager;

    /**
     * @Secure("viewParameterLibrary")
     */
    public function viewAction()
    {
        /** @var $library ParameterLibrary */
        $library = ParameterLibrary::load($this->getParam('id'));

        $this->view->assign('library', $library);
        $canEdit = $this->aclManager->isAllowed($this->_helper->auth(), Actions::EDIT, $library);
        $this->view->assign('edit', $canEdit);
        $this->setActiveMenuItemParameterLibrary($library->getId());
    }
}
