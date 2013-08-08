<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * Arbre de la structure d'un AF
 * @package AF
 */
class AF_Tree_AfStructureController extends UI_Controller_Tree
{

    use UI_Controller_Helper_Form;

    /**
     * @see UI_Controller_Tree::getnodesAction()
     * @Secure("editAF")
     */
    public function getnodesAction()
    {
        if ($this->idNode !== null) {
            /** @var $group AF_Model_Component_Group */
            $group = AF_Model_Component_Group::load($this->idNode);
        } else {
            /** @var $af AF_Model_AF */
            $af = AF_Model_AF::load($this->getParam('id'));
            $group = $af->getRootGroup();
        }

        foreach ($group->getSubComponents() as $component) {
            $isLeaf = (! $component instanceof AF_Model_Component_Group);
            $this->addNode(
                $component->getId(),
                $component->getLabel(),
                $isLeaf,
                null,
                false,
                true,
                true
            );
        }
        $this->send();
    }

    /**
     * @see UI_Controller_Tree::addnodeAction()
     */
    public function addnodeAction()
    {
        throw new Core_Exception_InvalidHTTPQuery("Action interdite");
    }

    /**
     * @see UI_Controller_Tree::editnodeAction()
     * @Secure("editAF")
     */
    public function editnodeAction()
    {
        /** @var $component AF_Model_Component */
        $component = AF_Model_Component::load($this->idNode);

        $newParent = $this->_form[$this->id . '_changeParent']['value'];
        $newPosition = $this->_form[$this->id . '_changeOrder']['value'];
        $afterElement = $this->_form[$this->id . '_changeOrder']['children'][$this->id . '_selectAfter_child']['value'];

        // Groupe
        if ($newParent != null) {
            /** @var $group AF_Model_Component_Group */
            $group = AF_Model_Component_Group::load($newParent);

            $component->getGroup()->removeSubComponent($component);
            $group->addSubComponent($component);
        }

        // Position
        if ($newPosition == 'first') {
            $component->setPosition(1);
        } elseif ($newPosition == 'last') {
            $component->setPosition($component->getLastEligiblePosition());
        } elseif ($newPosition == 'after') {
            /** @var $previousComponent AF_Model_Component */
            $previousComponent = AF_Model_Component::load($afterElement);
            $component->moveAfter($previousComponent);
        }

        $component->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * @see UI_Controller_Tree::deletenodeAction()
     */
    public function deletenodeAction()
    {
        throw new Core_Exception_InvalidHTTPQuery("Action interdite");
    }

    /**
     * @see UI_Controller_Tree::getlistparentsAction()
     * @Secure("editAF")
     */
    public function getlistparentsAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        /** @var $component AF_Model_Component */
        if ($this->idNode != null) {
            $component = AF_Model_Component::load($this->idNode);
            $parentGroup = $component->getGroup();
        } else {
            $component = null;
            $parentGroup = null;
        }

        $rootGroup = $af->getRootGroup();

        // Sélection par défaut = pas de changement
        $this->addElementList('', '');

        $groups = [$rootGroup->getId() => __('UI', 'name', 'root')]
            + $this->getAllAFGroups($rootGroup);

        foreach ($groups as $idNode => $groupName) {
            // Évite d'afficher le noeud que l'on veut déplacer
            if ($component && $component->getId() == $idNode) {
                continue;
            }
            // Évite d'afficher le parent direct du noeud
            if ($parentGroup && $parentGroup->getId() === $idNode) {
                continue;
            }
            $this->addElementList($idNode, $groupName);
        }
        $this->send();
    }

    /**
     * @see UI_Controller_Tree::getlistsiblingsAction()
     * @Secure("editAF")
     */
    public function getlistsiblingsAction()
    {
        /** @var $component AF_Model_Component */
        $component = AF_Model_Component::load($this->idNode);

        $idGroup = $this->getParam('idParent');
        if ($idGroup == null) {
            $group = $component->getGroup();
        } else {
            $group = AF_Model_Component_Group::load($idGroup);
        }

        $siblings = $group->getSubComponents();

        foreach ($siblings as $sibling) {
            if ($component->getGroup() === $group) {
                // Exclut le noeud courant
                if ($sibling === $component) {
                    continue;
                }
                // On ne veut pas ajouter le noeud derrière l'élément où il est déjà
                if ($sibling->getPosition() == $component->getPosition() - 1) {
                    continue;
                }
            }
            $this->addElementList($sibling->getId(), $sibling->getLabel());
        }
        $this->send();
    }

    /**
     * @param AF_Model_Component_Group $group
     * @return AF_Model_Component_Group[]
     */
    private function getAllAFGroups(AF_Model_Component_Group $group)
    {
        $groups = [];
        foreach ($group->getSubComponents() as $component) {
            if ($component instanceof AF_Model_Component_Group) {
                $groups[$component->getId()] = $component->getLabel();
                $groups = $groups + $this->getAllAFGroups($component);
            }
        }
        return $groups;
    }

}
