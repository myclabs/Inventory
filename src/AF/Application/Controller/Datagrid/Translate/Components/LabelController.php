<?php

use AF\Domain\AFLibrary;
use AF\Domain\Component\Component;
use Core\Annotation\Secure;

class AF_Datagrid_Translate_Components_LabelController extends UI_Controller_Datagrid
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
                $data['identifier'] = $this->translator->get($component->getAF()->getLabel())
                    .' | '.$component->getRef();

                foreach ($this->languages as $language) {
                    $data[$language] = $component->getLabel()->get($language);
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
        $component->getLabel()->set($this->update['value'], $this->update['column']);

        $this->data = $component->getLabel()->get($this->update['column']);
        $this->send(true);
    }
}
