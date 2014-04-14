<?php
/**
 * Classe AF_Datagrid_Translate_ComponentsController
 * @author valentin.claras
 * @package AF
 * @subpackage Controller
 */

use AF\Domain\AFLibrary;
use AF\Domain\Component\Component;
use AF\Domain\Component\Group;
use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des components.
 * @package AF
 * @subpackage Controller
 */
class AF_Datagrid_Translate_Components_HelpController extends UI_Controller_Datagrid
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
                $data = array();
                $data['index'] = $component->getId();
                $data['identifier'] = $component->getAF()->getRef().' | '.$component->getRef();

                foreach ($this->languages as $language) {
                    $locale = Core_Locale::load($language);
                    $component->reloadWithLocale($locale);
                    $brutText = Core_Tools::removeTextileMarkUp($component->getHelp());
                    if (empty($brutText)) {
                        $brutText = __('UI', 'translate', 'empty');
                    }
                    $data[$language] = $this->cellLongText(
                        'af/datagrid_translate_components_help/view/id/'.$component->getId().'/locale/'.$language,
                        'af/datagrid_translate_components_help/edit/id/'.$component->getId().'/locale/'.$language,
                        substr($brutText, 0, 50).((strlen($brutText) > 50) ? __('UI', 'translate', '…') : ''),
                        'zoom-in'
                    );
                }
                $this->addline($data);
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
        $component = Component::load($this->update['index']);
        $component->reloadWithLocale(Core_Locale::load($this->update['column']));
        $component->setHelp($this->update['value']);
        $brutText = Core_Tools::removeTextileMarkUp($component->getHelp());
        $this->data = $this->cellLongText(
            'af/datagrid_translate_components_help/view/id/'.$component->getId().'/locale/'.$this->update['column'],
            'af/datagrid_translate_components_help/edit/id/'.$component->getId().'/locale/'.$this->update['column'],
            substr($brutText, 0, 50).((strlen($brutText) > 50) ? __('UI', 'translate', '…') : ''),
            'zoom-in'
        );

        $this->send(true);
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editAFLibrary")
     */
    public function viewAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $this->_helper->viewRenderer->setNoRender(true);

        $component = Component::load($this->getParam('id'));
        $locale = Core_Locale::load($this->getParam('locale'));
        $component->reloadWithLocale($locale);

        echo Core_Tools::textile($component->getHelp());
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editAFLibrary")
     */
    public function editAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $this->_helper->viewRenderer->setNoRender(true);

        $component = Component::load($this->getParam('id'));
        $locale = Core_Locale::load($this->getParam('locale'));
        $component->reloadWithLocale($locale);

        echo $component->getHelp();
    }
}
