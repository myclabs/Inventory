<?php
/**
 * Classe Techno_Datagrid_Translate_Families_DocumentationController
 * @author valentin.claras
 * @package Techno
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Techno\Domain\Family\Family;

/**
 * Classe du controller du datagrid des traductions des documentations des family.
 * @package Techno
 * @subpackage Controller
 */
class Techno_Datagrid_Translate_Families_DocumentationController extends UI_Controller_Datagrid
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
        foreach (Family::loadList($this->request) as $family) {
            $data = array();
            $data['index'] = $family->getId();
            $data['identifier'] = $family->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $family->reloadWithLocale($locale);
                $brutText = Core_Tools::removeTextileMarkUp($family->getDocumentation());
                if (empty($brutText)) {
                    $brutText = __('UI', 'translate', 'empty');
                }
                $data[$language] = $this->cellLongText(
                    'techno/datagrid_translate_families_documentation/view/id/'.$family->getId().'/locale/'.$language,
                    'techno/datagrid_translate_families_documentation/edit/id/'.$family->getId().'/locale/'.$language,
                    substr($brutText, 0, 50).((strlen($brutText) > 50) ? __('UI', 'translate', '…') : ''),
                    'zoom-in'
                );
            }
            $this->addline($data);
        }
        $this->totalElements = Family::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editTechno")
     */
    public function updateelementAction()
    {
        $family = Family::load($this->update['index']);
        $family->reloadWithLocale(Core_Locale::load($this->update['column']));
        $family->setDocumentation($this->update['value']);
        $this->data = $this->cellLongText(
            'techno/datagrid_translate_families_documentation/view/id/'.$family->getId().'/locale/'.$this->update['column'],
            'techno/datagrid_translate_families_documentation/edit/id/'.$family->getId().'/locale/'.$this->update['column']
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

        $family = Family::load($this->getParam('id'));
        $locale = Core_Locale::load($this->getParam('locale'));
        $family->reloadWithLocale($locale);

        echo Core_Tools::textile($family->getDocumentation());
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editTechno")
     */
    public function editAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $family = Family::load($this->getParam('id'));
        $locale = Core_Locale::load($this->getParam('locale'));
        $family->reloadWithLocale($locale);
        
        echo $family->getDocumentation();
    }
}