<?php

use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;
use Techno\Domain\Category;

/**
 * Classe du controller du datagrid des traductions des categories.
 * @author valentin.claras
 */
class Techno_Datagrid_Translate_CategoriesController extends UI_Controller_Datagrid
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
     * @Secure("editTechno")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        foreach (Category::loadList($this->request) as $category) {
            /** @var Category $category */
            $data = array();
            $data['index'] = $category->getId();
            $data['identifier'] = $category->getId();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $category->reloadWithLocale($locale);
                $data[$language] = $category->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Category::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editTechno")
     */
    public function updateelementAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $category = Category::load($this->update['index']);
        $category->reloadWithLocale(Core_Locale::load($this->update['column']));
        $category->setLabel($this->update['value']);
        $this->data = $category->getLabel();

        $this->send(true);
    }
}
