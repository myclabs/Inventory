<?php

use Classification\Domain\ClassificationLibrary;
use Classification\Domain\Context;
use Core\Annotation\Secure;

class Classification_Datagrid_Translate_ContextsController extends UI_Controller_Datagrid
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

        foreach (Context::loadList($this->request) as $context) {
            $data = array();
            $data['index'] = $context->getId();
            $data['identifier'] = $context->getRef();

            foreach ($this->languages as $language) {
                $data[$language] = $context->getLabel()->get($language);
            }
            $this->addline($data);
        }
        $this->totalElements = Context::countTotal($this->request);

        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function updateelementAction()
    {
        $context = Context::load($this->update['index']);
        $context->getLabel()->set($this->update['value'], $this->update['column']);

        $this->data = $context->getLabel()->get($this->update['column']);
        $this->send(true);
    }
}
