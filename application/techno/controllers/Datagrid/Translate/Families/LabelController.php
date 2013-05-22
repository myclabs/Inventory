<?php
/**
 * Classe Techno_Datagrid_Translate_FamiliesController
 * @author valentin.claras
 * @package Techno
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des families.
 * @package Techno
 * @subpackage Controller
 */
class Techno_Datagrid_Translate_Families_LabelController extends UI_Controller_Datagrid
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
                $data[$language] = $family->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Techno_Model_Family::countTotal($this->request);

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
        $family->setLabel($this->update['value']);
        $this->data = $family->getLabel();

        $this->send(true);
    }
}