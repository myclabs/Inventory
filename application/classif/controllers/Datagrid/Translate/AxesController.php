<?php
/**
 * Classe Classif_Datagrid_Translate_AxesController
 * @author valentin.claras
 * @package Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des axes.
 * @package Classif
 * @subpackage Controller
 */
class Classif_Datagrid_Translate_AxesController extends UI_Controller_Datagrid
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
        foreach (Classif_Model_Axis::loadList($this->request) as $axis) {
            $data = array();
            $data['index'] = $axis->getRef();
            $data['identifier'] = $axis->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $axis->reloadWithLocale($locale);
                $data[$language] = $axis->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Classif_Model_Axis::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editClassif")
     */
    public function updateelementAction()
    {
        $axis = Classif_Model_Axis::loadByRef($this->update['index']);
        $axis->setTranslationLocale(Core_Locale::load($this->update['column']));
        $axis->setLabel($this->update['value']);
        $this->data = $axis->getLabel();

        $this->send(true);
    }
}