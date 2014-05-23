<?php

use AF\Domain\AFLibrary;
use AF\Domain\Category;
use Core\Annotation\Secure;

class AF_Datagrid_Translate_CategoriesController extends UI_Controller_Datagrid
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
        $this->request->filter->addCondition('library', $library);

        foreach (Category::loadList($this->request) as $category) {
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
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editAFLibrary")
     */
    public function updateelementAction()
    {
        $category = Category::load($this->update['index']);
        $category->getLabel()->set($this->update['value'], $this->update['column']);
        $this->data = $category->getLabel()->get($this->update['column']);

        $this->send(true);
    }
}
