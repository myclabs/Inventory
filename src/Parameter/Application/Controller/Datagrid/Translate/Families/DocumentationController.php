<?php

use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;
use Parameter\Domain\Family\Family;

/**
 * Classe du controller du datagrid des traductions des documentations des family.
 * @author valentin.claras
 */
class Parameter_Datagrid_Translate_Families_DocumentationController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var TranslatableListener
     */
    private $translatableListener;

    /**
     * @Inject("translation.languages")
     * @var string[]
     */
    private $languages;

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("editParameterLibrary")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        foreach (Family::loadList($this->request) as $family) {
            /** @var Family $family */
            $data = array();
            $data['index'] = $family->getId();
            $data['identifier'] = $family->getRef();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $family->reloadWithLocale($locale);
                $brutText = Core_Tools::removeTextileMarkUp($family->getDocumentation());
                if (empty($brutText)) {
                    $brutText = __('UI', 'translate', 'empty');
                }
                $data[$language] = $this->cellLongText(
                    'parameter/datagrid_translate_families_documentation/view/id/'.$family->getId().'/locale/'.$language,
                    'parameter/datagrid_translate_families_documentation/edit/id/'.$family->getId().'/locale/'.$language,
                    substr($brutText, 0, 50).((strlen($brutText) > 50) ? __('UI', 'translate', '…') : ''),
                    'zoom-in'
                );
            }
            $this->addline($data);
        }
        $this->totalElements = Family::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editParameterLibrary")
     */
    public function updateelementAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $family = Family::load($this->update['index']);
        $family->reloadWithLocale(Core_Locale::load($this->update['column']));
        $family->setDocumentation($this->update['value']);
        $this->data = $this->cellLongText(
            'parameter/datagrid_translate_families_documentation/view/id/'.$family->getId().'/locale/'.$this->update['column'],
            'parameter/datagrid_translate_families_documentation/edit/id/'.$family->getId().'/locale/'.$this->update['column']
        );

        $this->send(true);
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editParameterLibrary")
     */
    public function viewAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $this->_helper->viewRenderer->setNoRender(true);

        $family = Family::load($this->getParam('id'));
        $locale = Core_Locale::load($this->getParam('locale'));
        $family->reloadWithLocale($locale);

        echo Core_Tools::textile($family->getDocumentation());
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editParameterLibrary")
     */
    public function editAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $this->_helper->viewRenderer->setNoRender(true);

        $family = Family::load($this->getParam('id'));
        $locale = Core_Locale::load($this->getParam('locale'));
        $family->reloadWithLocale($locale);

        echo $family->getDocumentation();
    }
}
