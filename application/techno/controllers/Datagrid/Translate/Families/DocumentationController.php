<?php
/**
 * Classe Techno_Datagrid_Translate_Families_DocumentationController
 * @author valentin.claras
 * @package Techno
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des documentations des family.
 * @package Techno
 * @subpackage Controller
 */
class Techno_Datagrid_Translate_Families_DocumentationController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("viewTechno")
     */
    public function getelementsAction()
    {
        foreach (Techno_Model_Family::loadList($this->request) as $family) {
            $data = array();
            $data['index'] = $family->getId();
            $data['identifier'] = $family->getId();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $family->reloadWithLocale($locale);
                $data[$language] = $this->cellLongText(
                    'techno/datagrid_translate_families_documentation/view/id/'.$family->getId().'/locale/'.$language,
                    'techno/datagrid_translate_families_documentation/edit/id/'.$family->getId().'/locale/'.$language
                );
            }
            $this->addline($data);
        }
        $this->totalFamilies = Techno_Model_Family::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editTechno")
     */
    public function updateelementAction()
    {
        $family = Techno_Model_Family::load($this->update['index']);
        $family->setTranslationLocale(Core_Locale::load($this->update['column']));
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

        $family = Techno_Model_Family::load($this->_getParam('id'));
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

        $family = Techno_Model_Family::load($this->_getParam('id'));
        $locale = Core_Locale::load($this->getParam('locale'));
        $family->reloadWithLocale($locale);
        
        echo $family->getDocumentation();
    }
}