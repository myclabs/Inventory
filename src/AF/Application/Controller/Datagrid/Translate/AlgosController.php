<?php

use AF\Domain\AFLibrary;
use AF\Domain\Algorithm\Numeric\NumericAlgo;
use Core\Annotation\Secure;

class AF_Datagrid_Translate_AlgosController extends UI_Controller_Datagrid
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
            foreach ($af->getAlgos() as $algo) {
                if (! $algo instanceof NumericAlgo) {
                    continue;
                }

                $data = array();
                $data['index'] = $algo->getId();
                $data['identifier'] = $algo->getId();

                foreach ($this->languages as $language) {
                    $data[$language] = $algo->getLabel()->get($language);
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
        $algo = NumericAlgo::load($this->update['index']);
        $algo->getLabel()->set($this->update['value'], $this->update['column']);

        $this->data = $algo->getLabel()->get($this->update['column']);
        $this->send(true);
    }
}
