<?php
/**
 * Classe Classif_Datagrid_Translate_ContextsController
 * @author valentin.claras
 * @package Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des contexts.
 * @package Classif
 * @subpackage Controller
 */
class Classif_Datagrid_Translate_ContextsController extends UI_Controller_Datagrid
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
        foreach (Classif_Model_Context::loadList($this->request) as $context) {
            $data = array();
            $data['index'] = $context->getRef();
            $data['identifier'] = $context->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $context->reloadWithLocale($locale);
                $data[$language] = $context->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Classif_Model_Context::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editClassif")
     */
    public function updateelementAction()
    {
        $context = Classif_Model_Context::loadByRef($this->update['index']);
        $context->setTranslationLocale(Core_Locale::load($this->update['column']));
        $context->setLabel($this->update['value']);
        $this->data = $context->getLabel();

        $this->send(true);
    }
}