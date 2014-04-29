<?php

use AF\Domain\AF;
use AF\Domain\AFLibrary;
use Core\Annotation\Secure;

class AF_Datagrid_Translate_AfsController extends UI_Controller_Datagrid
{
    /**
     * @Inject("translation.languages")
     * @var string[]
     */
    private $languages;

    /**
     * @Secure("editAFLibrary")
     */
    public function getelementsAction()
    {
        $library = AFLibrary::load($this->getParam('library'));
        $this->request->filter->addCondition('library', $library);

        foreach (AF::loadList($this->request) as $af) {
            $data = [];
            $data['index'] = $af->getId();

            foreach ($this->languages as $language) {
                $data[$language] = $af->getLabel()->get($language);
            }
            $this->addline($data);
        }
        $this->totalElements = AF::countTotal($this->request);

        $this->send();
    }

    /**
     * @Secure("editAFLibrary")
     */
    public function updateelementAction()
    {
        $af = AF::load($this->update['index']);
        $af->getLabel()->set($this->update['value'], $this->update['column']);

        $this->data = $af->getLabel()->get($this->update['column']);
        $this->send();
    }
}
