<?php

use Classification\Domain\Axis;
use Classification\Domain\ClassificationLibrary;
use Core\Annotation\Secure;

class Classification_Datagrid_Translate_AxesController extends UI_Controller_Datagrid
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

        foreach (Axis::loadList($this->request) as $axis) {
            $data = [];
            $data['index'] = $axis->getId();
            $data['identifier'] = $axis->getRef();

            foreach ($this->languages as $language) {
                $data[$language] = $axis->getLabel()->get($language);
            }
            $this->addline($data);
        }
        $this->totalElements = Axis::countTotal($this->request);

        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function updateelementAction()
    {
        $axis = Axis::load($this->update['index']);
        $axis->getLabel()->set($this->update['value'], $this->update['column']);
        $this->data = $axis->getLabel()->get($this->update['column']);

        $this->send(true);
    }
}
