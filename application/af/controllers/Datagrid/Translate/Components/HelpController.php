<?php
/**
 * Classe AF_Datagrid_Translate_ComponentsController
 * @author valentin.claras
 * @package AF
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des components.
 * @package AF
 * @subpackage Controller
 */
class AF_Datagrid_Translate_Components_HelpController extends UI_Controller_Datagrid
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
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        foreach (AF_Model_Component::loadList($this->request) as $component) {
            $data = array();
            $data['index'] = $component->getId();
            $data['identifier'] = $component->getAF()->getRef().' | '.$component->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
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
        $component = AF_Model_Component::load($this->update['index']);
        $component->setTranslationLocale(Core_Locale::load($this->update['column']));
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
        $this->_helper->viewRenderer->setNoRender(true);

        $component = AF_Model_Component::load($this->getParam('id'));
        $locale = Core_Locale::load($this->getParam('locale'));
        $component->reloadWithLocale($locale);

        echo $component->getHelp();
    }
}