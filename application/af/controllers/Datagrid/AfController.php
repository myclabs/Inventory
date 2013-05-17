<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * Conditions Controller
 * @package AF
 * @property $view
 */
class AF_Datagrid_AfController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        $afList = AF_Model_AF::loadList($this->request);
        foreach ($afList as $af) {
            /** @var $af AF_Model_AF */
            $data = [];
            $data['index'] = $af->getId();
            $data['category'] = $this->cellList($af->getCategory()->getId());
            $data['ref'] = $af->getRef();
            $data['label'] = $af->getLabel();
            $data['configuration'] = $this->cellLink($this->view->url(array(
                                                                           'module'     => 'af',
                                                                           'controller' => 'edit',
                                                                           'action'     => 'menu',
                                                                           'id'         => $af->getId(),
                )),
                __('UI', 'name', 'configuration'), 'share-alt'
                );
            $data['test'] = $this->cellLink($this->view->url(array(
                                                                  'module'     => 'af',
                                                                  'controller' => 'af',
                                                                  'action'     => 'test',
                                                                  'id'         => $af->getId(),
                )),
                __('UI', 'name', 'test'), 'share-alt'
                );
            $this->addLine($data);
        }
        $this->totalElements = AF_Model_AF::countTotal($this->request);
        $this->send();
    }


    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     * @Secure("editAF")
     */
    public function addelementAction()
    {
        // Validation du formulaire
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $idCategory = $this->getAddElementValue('category');
        if (empty($idCategory)) {
            $this->setAddElementErrorMessage('category', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $label = $this->getAddElementValue('label');
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {

            /** @var $category AF_Model_Category */
            $category = AF_Model_Category::load($idCategory);

            try {
                $af = new AF_Model_AF($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $af->setLabel($label);
            $af->setCategory($category);
            $af->save();

            $entityManagers = Zend_Registry::get('EntityManagers');
            try {
                $entityManagers['default']->flush();
            } catch (Core_ORM_DuplicateEntryException $e) {
                $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
                $this->send();
                return;
            }
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::updateelementAction()
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'category':
                /** @var $category AF_Model_Category */
                $category = AF_Model_Category::load($newValue);
                $af->setCategory($category);
                $this->data = $this->cellList($newValue);
                break;
            case 'label':
                $af->setLabel($newValue);
                $this->data = $af->getLabel();
                break;
            case 'ref':
                $af->setRef($newValue);
                $this->data = $af->getRef();
                break;
        }
        $af->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        try {
            $entityManagers['default']->flush();
        } catch (Core_ORM_DuplicateEntryException $e) {
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
        }
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->_getParam('index'));
        $af->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        try {
            $entityManagers['default']->flush();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            if ($e->isSourceEntityInstanceOf('AF_Model_Component_SubAF')
                && $e->getSourceField() == 'calledAF') {
                throw new Core_Exception_User('AF', 'formList', 'afUsedByOtherAF');
            }
            throw $e;
        }
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
