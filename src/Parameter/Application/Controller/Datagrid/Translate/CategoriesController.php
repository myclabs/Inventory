<?php

use Core\Annotation\Secure;
use Parameter\Domain\Category;
use Parameter\Domain\ParameterLibrary;

class Parameter_Datagrid_Translate_CategoriesController extends UI_Controller_Datagrid
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

        foreach (Category::loadList($this->request) as $category) {
            /** @var Category $category */
            $data = array();
            $data['index'] = $category->getId();
            $data['identifier'] = $category->getId();

            foreach ($this->languages as $language) {
                $data[$language] = $category->getLabel()->get($language);
            }
            $this->addline($data);
        }
        $this->totalElements = Category::countTotal($this->request);

        $this->send();
    }

    /**
     * @Secure("editParameterLibrary")
     */
    public function updateelementAction()
    {
        $category = Category::load($this->update['index']);
        $category->getLabel()->set($this->update['value'], $this->update['column']);
        $this->data = $category->getLabel()->get($this->update['column']);

        $this->send(true);
    }
}
