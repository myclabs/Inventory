<?php

use AF\Domain\AFLibrary;
use AF\Domain\Component\Select\SelectOption;
use AF\Domain\Component\Select;
use Core\Annotation\Secure;

class AF_Datagrid_Translate_OptionsController extends UI_Controller_Datagrid
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
                if (! $component instanceof Select) {
                    continue;
                }
                foreach ($component->getOptions() as $option) {
                    $data = array();
                    $data['index'] = $option->getId();
                    $data['identifier'] = $this->translator->toString($option->getSelect()->getAF()->getLabel())
                        .' | '.$option->getSelect()->getRef()
                        .' | '.$option->getRef();

                    foreach ($this->languages as $language) {
                        $data[$language] = $option->getLabel()->get($language);
                    }
                    $this->addline($data);
                }
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
        $option = SelectOption::load($this->update['index']);
        $option->getLabel()->set($this->update['value'], $this->update['column']);
        $this->data = $this->cellTranslatedText($option->getLabel());

        $this->send(true);
    }
}
