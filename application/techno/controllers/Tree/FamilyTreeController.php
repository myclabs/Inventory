<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;

/**
 * Controller de l'arbre des familles
 * @package Techno
 */
class Techno_Tree_FamilyTreeController extends UI_Controller_Tree
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Tree::getnodesAction()
     * @Secure("viewTechno")
     */
    public function getnodesAction()
    {
        $isEditable = ($this->getParam('mode') == 'edition');

        // Chargement des catégories racine
        if ($this->idNode === null) {
            $categories = Techno_Model_Category::loadRootCategories();
            $currentCategory = null;
        } else {
            /** @var $currentCategory Techno_Model_Category */
            $currentCategory = $this->fromTreeId($this->idNode);
            $categories = $currentCategory->getChildCategories();
        }

        foreach ($categories as $category) {
            $this->addNode($this->getTreeId($category), $category->getLabel(), false, null, false, true, $isEditable);
        }

        if ($currentCategory) {
            foreach ($currentCategory->getFamilies() as $family) {
                // Place un symbole indiquant le type de la famille
                if ($family instanceof Techno_Model_Family_Process) {
                    $label = '[FE] ' . $family->getLabel();
                } else {
                    $label = '[C] ' . $family->getLabel();
                }
                if ($isEditable) {
                    $action = 'edit';
                } else {
                    $action = 'details';
                }
                $url = $this->_helper->url($action,
                                           'family',
                                           'techno',
                                           ['id' => $family->getId()]);
                $this->addNode($this->getTreeId($family), $label, true, $url, true, false, $isEditable);
            }
        }

        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Tree::getlistparentsAction()
     * @Secure("viewTechno")
     */
    public function getlistparentsAction()
    {
        $this->addElementList('', '');

        // Ajoute l'élément "Racine"
        $this->addElementList("root", __('UI', 'name', 'root'));

        /** @var Techno_Model_Category[] $categories */
        $categories = Techno_Model_Category::loadList();
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
            if ($node instanceof Techno_Model_Category) {
                $parentNode = $node->getParentCategory();
            } else {
                /** @var Techno_Model_Family $node */
                $parentNode = $node->getCategory();
            }
        }

        // Charge les siblings
        if ($parentNode == null) {
            $siblings = Techno_Model_Category::loadRootCategories();
        } else {
            if ($node instanceof Techno_Model_Category) {
                $siblings = $parentNode->getChildCategories();
            } else {
                $siblings = $parentNode->getFamilies();
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
     * (non-PHPdoc)
     * @see UI_Controller_Tree::addnodeAction()
     * @Secure("editTechno")
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

        $category = new Techno_Model_Category();
        $category->setLabel($label);
        $category->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $this->message = __('UI', 'message', 'added');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Tree::editnodeAction()
     * @Secure("editTechno")
     */
    public function editnodeAction()
    {
        $node = $this->fromTreeId($this->idNode);

        // Label
        $label = $this->getEditElementValue('labelEdit');
        if ($label != null) {
            $node->setLabel($label);
        }

        // Parent
        $idParent = $this->getEditElementValue('changeParent');
        if ($idParent != null) {
            if ($idParent == 'root') {
                $parent = null;
            } else {
                /** @var $parent Techno_Model_Category */
                $parent = $this->fromTreeId($idParent);
            }

            if ($node instanceof Techno_Model_Category) {
                $node->setParentCategory($parent);
            } elseif ($node instanceof Techno_Model_Family) {
                if ($parent === null) {
                    throw new Core_Exception("Family can't be at root");
                }
                $node->setCategory($parent);
            }
        }

        // Position
        $position = $this->getEditElementValue('changeOrder');
        if ($position == 'first') {
            $node->setPosition(1);
        } elseif ($position == 'last') {
            $node->setPosition($node->getLastEligiblePosition());
        } elseif ($position == 'after') {
            $previousNodeId = $this->_form[$this->id . '_changeOrder']['children']
                [$this->id . '_selectAfter_child']['value'];
            if ($previousNodeId == null) {
                $this->setEditFormElementErrorMessage('children', __('UI', 'formValidation', 'emptyRequiredField'));
                $this->send();
                return;
            }
            $previousNode = $this->fromTreeId($previousNodeId);
            $node->moveAfter($previousNode);
        }

        $node->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * Suppression d'un noeud
     * @Secure("editTechno")
     */
    public function deletenodeAction()
    {
        $node = $this->fromTreeId($this->idNode);
        $node->delete();

        $entityManagers = Zend_Registry::get('EntityManagers');
        try {
            $entityManagers['default']->flush();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            throw new Core_Exception_User('Techno', 'familyTree', 'categoryHasChild');
        }

        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Fonction récupérant les informations d'édition pour le formulaire.
     * @Secure("editTechno")
     */
    public function getinfoeditAction()
    {
        $node = $this->fromTreeId($this->idNode);

        $this->data['label'] = $node->getLabel();
        if ($node instanceof Techno_Model_Category) {
            $this->data['titlePopup'] = __('Techno', 'familyTree', 'editCategoryPopupTitle');
            $this->data['htmlComplement'] = '';
        } else {
            $this->data['titlePopup'] = __('Techno', 'familyTree', 'editFamilyPopupTitle');
            $htmlComplement = '';
            $htmlComplement .= '<a href="techno/family/edit/id/' . $node->getId() . '">';
            $htmlComplement .= '<i class="icon-share-alt"> </i> ';
            $htmlComplement .= __('UI', 'name', 'details');
            $htmlComplement .= '</a>';
            $this->data['htmlComplement'] = $htmlComplement;
        }

        $this->send();
    }

    /**
     * @param Techno_Model_Category|Techno_Model_Family $object
     * @throws Core_Exception
     * @return string ID
     */
    private function getTreeId($object)
    {
        if ($object instanceof Techno_Model_Category) {
            return 'c_' . $object->getId();
        } elseif ($object instanceof Techno_Model_Family) {
            return 'f_' . $object->getId();
        }
        throw new Core_Exception("Unknown object type");
    }

    /**
     * @param string $id
     * @throws Core_Exception
     * @return Techno_Model_Category|Techno_Model_Family
     */
    private function fromTreeId($id)
    {
        list($type, $id) = explode('_', $id);
        if ($type === 'c') {
            return Techno_Model_Category::load($id);
        } elseif ($type === 'f') {
            return Techno_Model_Family::load($id);
        }
        throw new Core_Exception("Unknown object type");
    }

}
