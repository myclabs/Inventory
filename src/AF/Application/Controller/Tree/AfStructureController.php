<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @package AF
 */

use AF\Domain\AF\AF;
use AF\Domain\AF\Component\Component;
use AF\Domain\AF\Component\Group;
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
            /** @var $group Group */
            $group = Group::load($this->idNode);
        } else {
            /** @var $af AF */
            $af = AF::load($this->getParam('id'));
            $group = $af->getRootGroup();
        }

        foreach ($group->getSubComponents() as $component) {
            $isLeaf = (! $component instanceof Group);
            $this->addNode(
                $component->getId(),
                $component->getLabel() . ' <em>(' . $component->getRef() . ')</em>',
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
        /** @var $component Component */
        $component = Component::load($this->idNode);

        $newParent = $this->_form[$this->id . '_changeParent']['value'];
        $newPosition = $this->_form[$this->id . '_changeOrder']['value'];
        $afterElement = $this->_form[$this->id . '_changeOrder']['children'][$this->id . '_selectAfter_child']['value'];

        // Groupe
        if ($newParent != 0) {
            /** @var $group Group */
            $group = Group::load($newParent);

            $component->getGroup()->removeSubComponent($component);
            $group->addSubComponent($component);
        }

        // Position
        if ($newPosition == 'first') {
            $component->setPosition(1);
        } elseif ($newPosition == 'last') {
            $component->setPosition($component->getLastEligiblePosition());
        } elseif ($newPosition == 'after') {
            /** @var $previousComponent Component */
            $previousComponent = Component::load($afterElement);
            $component->moveAfter($previousComponent);
        }

        $component->save();
        $this->entityManager->flush();

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
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        /** @var $component \AF\Domain\AF\Component\Component */
        if ($this->idNode != null) {
            $component = Component::load($this->idNode);
            $parentGroup = $component->getGroup();
        } else {
            $component = null;
            $parentGroup = null;
        }

        $rootGroup = $af->getRootGroup();

        // Sélection par défaut = pas de changement
        $this->addElementList('0', '');

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
        /** @var $component Component */
        $component = Component::load($this->idNode);

        $idGroup = $this->getParam('idParent');
        if ($idGroup == null) {
            $group = $component->getGroup();
        } else {
            $group = Group::load($idGroup);
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
     * @param Group $group
     * @return Group[]
     */
    private function getAllAFGroups(Group $group)
    {
        $groups = [];
        foreach ($group->getSubComponents() as $component) {
            if ($component instanceof Group) {
                $groups[$component->getId()] = $component->getLabel();
                $groups = $groups + $this->getAllAFGroups($component);
            }
        }
        return $groups;
    }

}
