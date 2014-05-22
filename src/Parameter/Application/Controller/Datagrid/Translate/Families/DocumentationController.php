<?php

use Core\Annotation\Secure;
use Parameter\Domain\Family\Family;
use Parameter\Domain\ParameterLibrary;

class Parameter_Datagrid_Translate_Families_DocumentationController extends UI_Controller_Datagrid
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
            $data = [];
            $data['index'] = $family->getId();
            $data['identifier'] = $family->getRef();

            foreach ($this->languages as $language) {
                $brutText = Core_Tools::removeTextileMarkUp($family->getDocumentation()->get($language));
                if (empty($brutText)) {
                    $brutText = __('UI', 'translate', 'empty');
                }
                $data[$language] = $this->cellLongText(
                    'parameter/datagrid_translate_families_documentation/view/id/'.$family->getId().'/locale/'.$language,
                    'parameter/datagrid_translate_families_documentation/edit/id/'.$family->getId().'/locale/'.$language,
                    substr($brutText, 0, 50).((strlen($brutText) > 50) ? '…' : ''),
                    'zoom-in'
                );
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
        $family->getDocumentation()->set($this->update['value'], $this->update['column']);
        $this->data = $this->cellLongText(
            'parameter/datagrid_translate_families_documentation/view/id/'.$family->getId().'/locale/'.$this->update['column'],
            'parameter/datagrid_translate_families_documentation/edit/id/'.$family->getId().'/locale/'.$this->update['column']
        );

        $this->send(true);
    }

    /**
     * @Secure("editParameterLibrary")
     */
    public function viewAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $family = Family::load($this->getParam('id'));

        echo Core_Tools::textile($family->getDocumentation()->get($this->getParam('locale')));
    }

    /**
     * @Secure("editParameterLibrary")
     */
    public function editAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $family = Family::load($this->getParam('id'));

        echo $family->getDocumentation()->get($this->getParam('locale'));
    }
}
