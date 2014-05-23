<?php

use Core\Annotation\Secure;
use Parameter\Domain\Family\Family;
use Parameter\Domain\ParameterLibrary;

class Parameter_Datagrid_Translate_Families_LabelController extends UI_Controller_Datagrid
{
    /**
     * @Inject("translation.languages")
     * @var string[]
     */
    private $languages;

    /**
     * @Secure("editParameterLibrary")
     */
    public function getelementsAction()
    {
        $library = ParameterLibrary::load($this->getParam('library'));
        $this->request->filter->addCondition('library', $library);

        foreach (Family::loadList($this->request) as $family) {
            /** @var Family $family */
            $data = array();
            $data['index'] = $family->getId();
            $data['identifier'] = $family->getRef();

            foreach ($this->languages as $language) {
                $data[$language] = $family->getLabel()->get($language);
            }
            $this->addline($data);
        }
        $this->totalElements = Family::countTotal($this->request);

        $this->send();
    }

    /**
     * @Secure("editParameterLibrary")
     */
    public function updateelementAction()
    {
        $family = Family::load($this->update['index']);
        $family->getLabel()->set($this->update['value'], $this->update['column']);
        $this->data = $family->getLabel()->get($this->update['column']);

        $this->send(true);
    }
}
