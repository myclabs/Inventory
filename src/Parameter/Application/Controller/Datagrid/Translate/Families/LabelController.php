<?php

use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;
use Parameter\Domain\Family\Family;
use Parameter\Domain\ParameterLibrary;

/**
 * Classe du controller du datagrid des traductions des families.
 * @author valentin.claras
 */
class Parameter_Datagrid_Translate_Families_LabelController extends UI_Controller_Datagrid
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

        $library = ParameterLibrary::load($this->getParam('library'));
        $this->request->filter->addCondition('library', $library);

        foreach (Family::loadList($this->request) as $family) {
            /** @var Family $family */
            $data = array();
            $data['index'] = $family->getId();
            $data['identifier'] = $family->getRef();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $family->reloadWithLocale($locale);
                $data[$language] = $family->getLabel();
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
        $family->setLabel($this->update['value']);
        $this->data = $family->getLabel();

        $this->send(true);
    }
}
