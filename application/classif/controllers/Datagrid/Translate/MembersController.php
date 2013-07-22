<?php
/**
 * Classe Classif_Datagrid_Translate_MembersController
 * @author valentin.claras
 * @package Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des members.
 * @package Classif
 * @subpackage Controller
 */
class Classif_Datagrid_Translate_MembersController extends UI_Controller_Datagrid
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
        foreach (Classif_Model_Member::loadList($this->request) as $member) {
            $data = array();
            $data['index'] = $member->getId();
            $data['identifier'] = $member->getAxis()->getRef().' | '.$member->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $member->reloadWithLocale($locale);
                $data[$language] = $member->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Classif_Model_Member::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editClassif")
     */
    public function updateelementAction()
    {
        $member = Classif_Model_Member::load($this->update['index']);
        $member->setTranslationLocale(Core_Locale::load($this->update['column']));
        $member->setLabel($this->update['value']);
        $this->data = $member->getLabel();

        $this->send(true);
    }
}