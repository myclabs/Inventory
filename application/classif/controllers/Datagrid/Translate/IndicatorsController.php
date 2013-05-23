<?php
/**
 * Classe Classif_Datagrid_Translate_IndicatorsController
 * @author valentin.claras
 * @package Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des indicators.
 * @package Classif
 * @subpackage Controller
 */
class Classif_Datagrid_Translate_IndicatorsController extends UI_Controller_Datagrid
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
     * @Secure("editClassif")
     */
    public function getelementsAction()
    {
        foreach (Classif_Model_Indicator::loadList($this->request) as $indicator) {
            $data = array();
            $data['index'] = $indicator->getRef();
            $data['identifier'] = $indicator->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $indicator->reloadWithLocale($locale);
                $data[$language] = $indicator->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Classif_Model_Indicator::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editClassif")
     */
    public function updateelementAction()
    {
        $indicator = Classif_Model_Indicator::loadByRef($this->update['index']);
        $indicator->setTranslationLocale(Core_Locale::load($this->update['column']));
        $indicator->setLabel($this->update['value']);
        $this->data = $indicator->getLabel();

        $this->send(true);
    }
}