<?php
/**
 * Classe AF_Datagrid_Translate_OptionsController
 * @author valentin.claras
 * @package AF
 * @subpackage Controller
 */

use AF\Domain\AFLibrary;
use AF\Domain\Component\Select\SelectOption;
use AF\Domain\Component\Select;
use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des options.
 * @package AF
 * @subpackage Controller
 */
class AF_Datagrid_Translate_OptionsController extends UI_Controller_Datagrid
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
     * @Secure("editAFLibrary")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);

        $library = AFLibrary::load($this->getParam('library'));

        foreach ($library->getAFList() as $af) {
            foreach ($af->getRootGroup()->getSubComponentsRecursive() as $component) {
                if (! $component instanceof Select) {
                    continue;
                }
                foreach ($component->getOptions() as $option) {
                    $data = array();
                    $data['index'] = $option->getId();
                    $data['identifier'] = $option->getSelect()->getAF()->getLabel()
                        .' | '.$option->getSelect()->getRef()
                        .' | '.$option->getRef();

                    foreach ($this->languages as $language) {
                        $locale = Core_Locale::load($language);
                        $option->reloadWithLocale($locale);
                        $data[$language] = $option->getLabel();
                    }
                    $this->addline($data);
                }
            }
        }

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editAFLibrary")
     */
    public function updateelementAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $option = SelectOption::load($this->update['index']);
        $option->reloadWithLocale(Core_Locale::load($this->update['column']));
        $option->setLabel($this->update['value']);
        $this->data = $option->getLabel();

        $this->send(true);
    }
}
