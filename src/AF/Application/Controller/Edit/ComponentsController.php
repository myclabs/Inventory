<?php

use AF\Domain\Component\Component;
use AF\Domain\Component\Select;
use Core\Annotation\Secure;
use Techno\Domain\Family\Dimension;
use Techno\Domain\Category as TechnoCategory;

/**
 * @author matthieu.napoli
 */
class AF_Edit_ComponentsController extends Core_Controller
{
    /**
     * Popup qui affiche l'aide d'un composant
     * @Secure("editAF")
     */
    public function popupHelpAction()
    {
        $this->view->component = Component::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Popup qui affiche les options d'un select
     * @Secure("editAF")
     */
    public function popupSelectOptionsAction()
    {
        $this->view->selectField = Select::load($this->getParam('idSelect'));

        $families = [];
        foreach (TechnoCategory::loadRootCategories() as $rootCategory) {
            $families = array_merge($families, $rootCategory->getAllFamilies());
        }
        $this->view->families = $families;
        $this->_helper->layout()->disableLayout();
    }

    /**
     * @Secure("editAF")
     */
    public function copyTechnoMembersAsOptionsAction()
    {
        $selectField = Select::load($this->getParam('idSelect'));

        foreach ($selectField->getOptions() as $option) {
            $option->delete();
        }
        $this->entityManager->flush();

        foreach (Dimension::load($this->getParam('dimension'))->getMembers() as $member) {
            $option = new Select\SelectOption();
            $option->setRef($member->getRef());
            $option->setLabel($member->getLabel());
            $selectField->addOption($option);
        }
        $this->entityManager->flush();

        $this->sendJsonResponse(['message' => __('UI', 'message', 'updated')]);
    }
}
