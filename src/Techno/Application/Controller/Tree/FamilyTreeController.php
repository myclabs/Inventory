<?php

use Core\Annotation\Secure;
use Techno\Domain\Family\Family;
use Techno\Domain\Category;
use Techno\Domain\Family\ProcessFamily;

/**
 * Controller de l'arbre des familles
 * @author matthieu.napoli
 */
class Techno_Tree_FamilyTreeController extends UI_Controller_Tree
{
    /**
     * @Secure("viewTechno")
     */
    public function getnodesAction()
    {
        $isEditable = ($this->getParam('mode') == 'edition');

        // Chargement des catégories racine
        if ($this->idNode === null) {
            $categories = Category::loadRootCategories();
            $currentCategory = null;
        } else {
            /** @var $currentCategory Category */
            $currentCategory = $this->fromTreeId($this->idNode);
            $categories = $currentCategory->getChildCategories();
        }

        foreach ($categories as $category) {
            $this->addNode($this->getTreeId($category), $category->getLabel(), false, null, false, true, $isEditable);
        }

        if ($currentCategory) {
            foreach ($currentCategory->getFamilies() as $family) {
                // Place un symbole indiquant le type de la famille
                if ($family instanceof ProcessFamily) {
                    $label = $family->getLabel();
                } else {
                    $label = $family->getLabel();
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
     * @Secure("viewTechno")
     */
    public function getlistparentsAction()
    {
        $this->addElementList('', '');

        // Ajoute l'élément "Racine"
        $this->addElementList("root", __('UI', 'name', 'root'));

        /** @var Category[] $categories */
        $categories = Category::loadList();
        foreach ($categories as $category) {
            $this->addElementList($this->getTreeId($category), $category->getLabel());
        }
        $this->send();
    }

    /**
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
            if ($node instanceof Category) {
                $parentNode = $node->getParentCategory();
            } else {
                /** @var Family $node */
                $parentNode = $node->getCategory();
            }
        }

        // Charge les siblings
        if ($parentNode == null) {
            $siblings = Category::loadRootCategories();
        } else {
            if ($node instanceof Category) {
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

        $category = new Category($label);
        $category->save();
        $this->entityManager->flush();

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
        if ($label == '') {
            $this->setEditFormElementErrorMessage('labelEdit', __('UI', 'formValidation', 'emptyRequiredField'));
            $this->send();
            return;
        }
        if ($label != null) {
            $node->setLabel($label);
        }

        // Parent
        $idParent = $this->getEditElementValue('changeParent');
        if ($idParent != null) {
            if ($idParent == 'root') {
                $parent = null;
            } else {
                /** @var $parent Category */
                $parent = $this->fromTreeId($idParent);
            }

            if ($node instanceof Category) {
                $node->setParentCategory($parent);
            } elseif ($node instanceof Family) {
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
        $this->entityManager->flush();

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

        try {
            $this->entityManager->flush();
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
        if ($node instanceof Category) {
            $this->data['titlePopup'] = __('Techno', 'familyTree', 'editCategoryPopupTitle');
            $this->data['htmlComplement'] = '';
        } else {
            $this->data['titlePopup'] = __('Techno', 'familyTree', 'editFamilyPopupTitle');
            $htmlComplement = '';
            $htmlComplement .= '<a href="techno/family/edit/id/' . $node->getId() . '">';
            $htmlComplement .= '<i class="fa fa-external-link"> </i> ';
            $htmlComplement .= __('UI', 'name', 'details');
            $htmlComplement .= '</a>';
            $this->data['htmlComplement'] = $htmlComplement;
        }

        $this->send();
    }

    /**
     * @param Category|Family $object
     * @throws Core_Exception
     * @return string ID
     */
    private function getTreeId($object)
    {
        if ($object instanceof Category) {
            return 'c_' . $object->getId();
        } elseif ($object instanceof Family) {
            return 'f_' . $object->getId();
        }
        throw new Core_Exception("Unknown object type");
    }

    /**
     * @param string $id
     * @throws Core_Exception
     * @return Category|Family
     */
    private function fromTreeId($id)
    {
        list($type, $id) = explode('_', $id);
        if ($type === 'c') {
            return Category::load($id);
        } elseif ($type === 'f') {
            return Family::load($id);
        }
        throw new Core_Exception("Unknown object type");
    }

}
