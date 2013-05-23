<?php
/**
 * Classe Techno_Datagrid_Translate_Elements_DocumentationController
 * @author valentin.claras
 * @package Techno
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des documentations des element.
 * @package Techno
 * @subpackage Controller
 */
class Techno_Datagrid_Translate_Elements_DocumentationController extends UI_Controller_Datagrid
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
        foreach (Techno_Model_Element::loadList($this->request) as $element) {
            $data = array();
            $data['index'] = $element->getId();
            $data['identifier'] = $element->getId();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $element->reloadWithLocale($locale);
                $brutText = Core_Tools::removeTextileMarkUp($element->getDocumentation());
                if (empty($brutText)) {
                    $brutText = __('UI', 'translate', 'empty');
                }
                $data[$language] = $this->cellLongText(
                    'techno/datagrid_translate_elements_documentation/view/id/'.$element->getId().'/locale/'.$language,
                    'techno/datagrid_translate_elements_documentation/edit/id/'.$element->getId().'/locale/'.$language,
                    substr($brutText, 0, 50).((strlen($brutText) > 50) ? __('UI', 'translate', '…') : ''),
                    'zoom-in'
                );
            }
            $this->addline($data);
        }
        $this->totalElements = Techno_Model_Element::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editTechno")
     */
    public function updateelementAction()
    {
        $element = Techno_Model_Element::load($this->update['index']);
        $element->setTranslationLocale(Core_Locale::load($this->update['column']));
        $element->setDocumentation($this->update['value']);
        $this->data = $this->cellLongText(
            'techno/datagrid_translate_elements_documentation/view/id/'.$element->getId().'/locale/'.$this->update['column'],
            'techno/datagrid_translate_elements_documentation/edit/id/'.$element->getId().'/locale/'.$this->update['column']
        );

        $this->send(true);
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editTechno")
     */
    public function viewAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $element = Techno_Model_Element::load($this->_getParam('id'));
        $locale = Core_Locale::load($this->getParam('locale'));
        $element->reloadWithLocale($locale);

        echo Core_Tools::textile($element->getDocumentation());
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editTechno")
     */
    public function editAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $element = Techno_Model_Element::load($this->_getParam('id'));
        $locale = Core_Locale::load($this->getParam('locale'));
        $element->reloadWithLocale($locale);

        echo $element->getDocumentation();
    }
}