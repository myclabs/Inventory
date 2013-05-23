<?php
/**
 * Classe AF_Datagrid_Translate_AfsController
 * @author valentin.claras
 * @package AF
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des afs.
 * @package AF
 * @subpackage Controller
 */
class AF_Datagrid_Translate_AfsController extends UI_Controller_Datagrid
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
        foreach (AF_Model_AF::loadList($this->request) as $aF) {
            $data = array();
            $data['index'] = $aF->getId();
            $data['identifier'] = $aF->getId();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $aF->reloadWithLocale($locale);
                $data[$language] = $aF->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = AF_Model_AF::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        $aF = AF_Model_AF::load($this->update['index']);
        $aF->setTranslationLocale(Core_Locale::load($this->update['column']));
        $aF->setLabel($this->update['value']);
        $this->data = $aF->getLabel();

        $this->send(true);
    }
}