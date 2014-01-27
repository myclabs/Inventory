<?php
/**
 * Classe AF_Datagrid_Translate_ComponentsController
 * @author valentin.claras
 * @package AF
 * @subpackage Controller
 */

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
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $this->request->filter->addCondition(
            AF_Model_Component::QUERY_REF,
            AF_Model_Component_Group::ROOT_GROUP_REF,
            Core_Model_Filter::OPERATOR_NOT_EQUAL
        );
        foreach (AF_Model_Component::loadList($this->request) as $component) {
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
        $this->totalElements = AF_Model_Component::countTotal($this->request);

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
        $component = AF_Model_Component::load($this->update['index']);
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
     * @Secure("editAF")
     */
    public function viewAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $this->_helper->viewRenderer->setNoRender(true);

        $component = AF_Model_Component::load($this->getParam('id'));
        $locale = Core_Locale::load($this->getParam('locale'));
        $component->reloadWithLocale($locale);

        echo Core_Tools::textile($component->getHelp());
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editAF")
     */
    public function editAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $this->_helper->viewRenderer->setNoRender(true);

        $component = AF_Model_Component::load($this->getParam('id'));
        $locale = Core_Locale::load($this->getParam('locale'));
        $component->reloadWithLocale($locale);

        echo $component->getHelp();
    }
}
