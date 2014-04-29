<?php

use AF\Domain\AFLibrary;
use AF\Domain\Component\Component;
use Core\Annotation\Secure;

class AF_Datagrid_Translate_Components_HelpController extends UI_Controller_Datagrid
{
    /**
     * @Inject("translation.languages")
     * @var string[]
     */
    private $languages;

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("editAFLibrary")
     */
    public function getelementsAction()
    {
        $library = AFLibrary::load($this->getParam('library'));

        foreach ($library->getAFList() as $af) {
            foreach ($af->getRootGroup()->getSubComponentsRecursive() as $component) {
                $data = array();
                $data['index'] = $component->getId();
                $data['identifier'] = $this->translationHelper->toString($component->getAF()->getLabel())
                    .' | '.$component->getRef();

                foreach ($this->languages as $language) {
                    $raw = Core_Tools::removeTextileMarkUp($component->getHelp()->get($language));
                    if (empty($raw)) {
                        $raw = __('UI', 'translate', 'empty');
                    }
                    $data[$language] = $this->cellLongText(
                        'af/datagrid_translate_components_help/view/id/'.$component->getId().'/locale/'.$language,
                        'af/datagrid_translate_components_help/edit/id/'.$component->getId().'/locale/'.$language,
                        substr($raw, 0, 50).((strlen($raw) > 50) ? __('UI', 'translate', '…') : ''),
                        'zoom-in'
                    );
                }
                $this->addline($data);
            }
        }

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editAFLibrary")
     */
    public function updateelementAction()
    {
        $component = Component::load($this->update['index']);
        $component->getHelp()->set($this->update['value'], $this->update['column']);
        $raw = Core_Tools::removeTextileMarkUp($component->getHelp()->get($this->update['column']));
        $this->data = $this->cellLongText(
            'af/datagrid_translate_components_help/view/id/'.$component->getId().'/locale/'.$this->update['column'],
            'af/datagrid_translate_components_help/edit/id/'.$component->getId().'/locale/'.$this->update['column'],
            substr($raw, 0, 50).((strlen($raw) > 50) ? __('UI', 'translate', '…') : ''),
            'zoom-in'
        );

        $this->send(true);
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editAFLibrary")
     */
    public function viewAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $component = Component::load($this->getParam('id'));

        echo Core_Tools::textile($component->getHelp()->get($this->getParam('locale')));
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editAFLibrary")
     */
    public function editAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $component = Component::load($this->getParam('id'));

        echo $component->getHelp()->get($this->getParam('locale'));
    }
}
