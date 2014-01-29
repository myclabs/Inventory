<?php
/**
 * Classe AF_Datagrid_Translate_CategoriesController
 * @author valentin.claras
 * @package AF
 * @subpackage Controller
 */

use AF\Domain\AF\Category;
use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des categories.
 * @package AF
 * @subpackage Controller
 */
class AF_Datagrid_Translate_CategoriesController extends UI_Controller_Datagrid
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
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        foreach (Category::loadList($this->request) as $category) {
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
     * @Secure("editAF")
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
