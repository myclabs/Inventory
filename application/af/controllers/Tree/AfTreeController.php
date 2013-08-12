<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * Controller de l'arbre des AF
 * @package AF
 */
class AF_Tree_AfTreeController extends UI_Controller_Tree
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Tree::getnodesAction()
     * @Secure("editAF")
     */
    public function getnodesAction()
    {
        // Chargement des catégories racines
        if ($this->idNode === null) {
            $categories = AF_Model_Category::loadRootCategories();
            $currentCategory = null;
        } else {
            /** @var $currentCategory AF_Model_Category */
            $currentCategory = $this->fromTreeId($this->idNode);
            $categories = $currentCategory->getChildCategories();
        }

        foreach ($categories as $category) {
            $this->addNode($this->getTreeId($category), $category->getLabel(), false, null, false, true);
        }

        if ($currentCategory) {
            foreach ($currentCategory->getAFs() as $af) {
                $this->addNode($this->getTreeId($af), $af->getLabel(), true, null, false, true);
            }
        }

        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Tree::getlistparentsAction()
     * @Secure("editAF")
     */
    public function getlistparentsAction()
    {
        $this->addElementList('', '');

        // Ajoute l'élément "Racine"
        $this->addElementList("root", __('AF', 'formTree', 'rootCategoryLabel'));

        /** @var AF_Model_Category[] $categories */
        $categories = AF_Model_Category::loadList();
        foreach ($categories as $category) {
            $this->addElementList($this->getTreeId($category), $category->getLabel());
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Tree::getlistsiblingsAction()
     * @Secure("editAF")
     */
    public function getlistsiblingsAction()
    {
        $node = $this->fromTreeId($this->idNode);

        // Détermine le parent
        $sameParent = false;
        $idParent = $this->getParam('idParent');
        if ($idParent == "root") {
            $parentNode = null;
        } elseif ($idParent) {
            $parentNode = $this->fromTreeId($idParent);
        } else {
            // Pas de parent défini, on prend le parent du noeud sélectionné
            $sameParent = true;
            if ($node instanceof AF_Model_Category) {
                $parentNode = $node->getParentCategory();
            } else {
                /** @var AF_Model_AF $node */
                $parentNode = $node->getCategory();
            }
        }

        // Charge les siblings
        if ($parentNode == null) {
            $siblings = AF_Model_Category::loadRootCategories();
        } else {
            if ($node instanceof AF_Model_Category) {
                $siblings = $parentNode->getChildCategories();
            } else {
                $siblings = $parentNode->getAFs();
            }
        }

        foreach ($siblings as $sibling) {
            // Exclut le noeud courant
            if ($sibling === $node) {
                continue;
            }
            // On ne veut pas ajouter le noeud derrière l'élément où il est déjà
            if ($sameParent && $sibling->getPosition() == $node->getPosition() - 1) {
                continue;
            }
            $this->addElementList($this->getTreeId($sibling), $sibling->getLabel());
        }

        $this->send();
    }

    /**
     * Fonction récupérant les informations d'édition pour le formulaire.
     * @Secure("editAF")
     */
    public function getinfoeditAction()
    {
        $node = $this->fromTreeId($this->idNode);

        $this->data['label'] = $node->getLabel();
        if ($node instanceof AF_Model_Category) {
            $this->data['title'] = __('AF', 'formTree', 'editCategoryPanelTitle');
            $this->data['htmlComplement'] = '';
        } else {
            $this->data['title'] = __('AF', 'formTree', 'editAFPanelTitle');
            $htmlComplement = '';
            $htmlComplement .= '<a href="af/edit/menu/id/' . $node->getId() . '">';
            $htmlComplement .= '<i class="icon-share-alt"> </i> ';
            $htmlComplement .= __('UI', 'name', 'configuration');
            $htmlComplement .= '</a>';
            $htmlComplement .= '<br />';
            $htmlComplement .= '<a href="af/af/test/id/' . $node->getId() . '">';
            $htmlComplement .= '<i class="icon-share-alt"> </i> ';
            $htmlComplement .= __('UI', 'name', 'test');
            $htmlComplement .= '</a>';
            $this->data['htmlComplement'] = $htmlComplement;
        }

        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Tree::addnodeAction()
     * @Secure("editAF")
     */
    public function addnodeAction()
    {
        // Validate the form
        $label = $this->getAddElementValue('label');
        if ($label == '') {
            $this->setAddFormElementErrorMessage('label', __('UI', 'formValidation', 'emptyRequiredField'));
            $this->send();
            return;
        }

        $category = new AF_Model_Category();
        $category->setLabel($label);
        $category->save();

        $this->entityManager->flush();

        $this->message = __('UI', 'message', 'added');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Tree::editnodeAction()
     * @Secure("editAF")
     */
    public function editnodeAction()
    {
        $node = $this->fromTreeId($this->idNode);

        $newParent = $this->_form[$this->id . '_changeParent']['value'];
        $newPosition = $this->_form[$this->id . '_changeOrder']['value'];
        $afterElement = $this->_form[$this->id . '_changeOrder']['children'][$this->id . '_selectAfter_child']['value'];
        $newLabel = $this->getEditElementValue('labelEdit');

        // Label
        if ($newLabel == '') {
            $this->setEditFormElementErrorMessage('labelEdit', __('UI', 'formValidation', 'emptyRequiredField'));
            $this->send();
            return;
        }
        if ($newLabel !== $node->getLabel()) {
            $node->setLabel($newLabel);
        }

        // Parent
        if ($newParent != null) {
            if ($newParent === 'root') {
                $parent = null;
            } else {
                /** @var $parent AF_Model_Category */
                $parent = $this->fromTreeId($newParent);
            }

            if ($node instanceof AF_Model_Category) {
                $node->setParentCategory($parent);
            } elseif ($node instanceof AF_Model_AF) {
                if ($parent === null) {
                    throw new Core_Exception("AF can't be at root");
                }
                $node->setCategory($parent);
            }
        }

        // Position
        if ($newPosition == 'first') {
            $node->setPosition(1);
        } elseif ($newPosition == 'last') {
            $node->setPosition($node->getLastEligiblePosition());
        } elseif ($newPosition == 'after') {
            $previousNode = $this->fromTreeId($afterElement);
            $node->moveAfter($previousNode);
        }

        $node->save();
        $this->entityManager->flush();

        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * Suppression d'un noeud
     * @Secure("editAF")
     */
    public function deletenodeAction()
    {
        $node = $this->fromTreeId($this->idNode);
        $node->delete();

        try {
            $this->entityManager->flush();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            if ($e->isSourceEntityInstanceOf('AF_Model_Component_SubAF')
                && $e->getSourceField() == 'calledAF') {
                throw new Core_Exception_User('AF', 'formList', 'afUsedByOtherAF');
            }
            throw new Core_Exception_User('AF', 'formTree', 'categoryHasChild');
        }

        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * @param AF_Model_Category|AF_Model_AF $object
     * @throws Core_Exception
     * @return string ID
     */
    private function getTreeId($object)
    {
        if ($object instanceof AF_Model_Category) {
            return 'c_' . $object->getId();
        } elseif ($object instanceof AF_Model_AF) {
            return 'a_' . $object->getId();
        }
        throw new Core_Exception("Unknown object type");
    }

    /**
     * @param string $id
     * @throws Core_Exception
     * @return AF_Model_Category|AF_Model_AF
     */
    private function fromTreeId($id)
    {
        list($type, $id) = explode('_', $id);
        if ($type === 'c') {
            return AF_Model_Category::load($id);
        } elseif ($type === 'a') {
            return AF_Model_AF::load($id);
        }
        throw new Core_Exception("Unknown object type");
    }

}
