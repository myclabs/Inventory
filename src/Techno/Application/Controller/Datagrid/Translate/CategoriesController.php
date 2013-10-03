<?php

use Core\Annotation\Secure;
use Techno\Domain\Category;

/**
 * Classe du controller du datagrid des traductions des categories.
 * @author valentin.claras
 */
class Techno_Datagrid_Translate_CategoriesController extends UI_Controller_Datagrid
{
    /**
     * Désactivation du fallback des traductions.
     */
    public function init()
    {
        parent::init();
        Zend_Registry::get('doctrineTranslate')->setTranslationFallback(false);
    }

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("editTechno")
     */
    public function getelementsAction()
    {
        foreach (Category::loadList($this->request) as $category) {
            /** @var Category $category */
            $data = array();
            $data['index'] = $category->getId();
            $data['identifier'] = $category->getId();

            foreach (Zend_Registry::get('languages') as $language) {
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
        $category = Category::load($this->update['index']);
        $category->reloadWithLocale(Core_Locale::load($this->update['column']));
        $category->setLabel($this->update['value']);
        $this->data = $category->getLabel();

        $this->send(true);
    }
}