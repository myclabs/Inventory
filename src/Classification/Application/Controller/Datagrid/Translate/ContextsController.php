<?php
/**
 * Classe Classification_Datagrid_Translate_ContextsController
 * @author valentin.claras
 * @package Classification
 * @subpackage Controller
 */

use Classification\Domain\Context;
use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des contexts.
 * @package Classification
 * @subpackage Controller
 */
class Classification_Datagrid_Translate_ContextsController extends UI_Controller_Datagrid
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
     * @Secure("editClassificationLibrary")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        foreach (Context::loadList($this->request) as $context) {
            $data = array();
            $data['index'] = $context->getId();
            $data['identifier'] = $context->getRef();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $context->reloadWithLocale($locale);
                $data[$language] = $context->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Context::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editClassificationLibrary")
     */
    public function updateelementAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $context = Context::load($this->update['index']);
        $context->reloadWithLocale(Core_Locale::load($this->update['column']));
        $context->setLabel($this->update['value']);
        $this->data = $context->getLabel();

        $this->send(true);
    }
}
