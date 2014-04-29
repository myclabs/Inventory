<?php

use Classification\Domain\ClassificationLibrary;
use Classification\Domain\Indicator;
use Core\Annotation\Secure;

class Classification_Datagrid_Translate_IndicatorsController extends UI_Controller_Datagrid
{
    /**
     * @Inject("translation.languages")
     * @var string[]
     */
    private $languages;

    /**
     * @Secure("editClassificationLibrary")
     */
    public function getelementsAction()
    {
        $library = ClassificationLibrary::load($this->getParam('library'));
        $this->request->filter->addCondition('library', $library);

        foreach (Indicator::loadList($this->request) as $indicator) {
            $data = [];
            $data['index'] = $indicator->getId();
            $data['identifier'] = $indicator->getRef();

            foreach ($this->languages as $language) {
                $data[$language] = $indicator->getLabel()->get($language);
            }
            $this->addline($data);
        }
        $this->totalElements = Indicator::countTotal($this->request);

        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function updateelementAction()
    {
        $indicator = Indicator::load($this->update['index']);
        $indicator->getLabel()->set($this->update['value'], $this->update['column']);

        $this->data = $indicator->getLabel()->get($this->update['column']);
        $this->send(true);
    }
}
