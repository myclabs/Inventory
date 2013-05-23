<?php
/**
 * Classe AF_Datagrid_Translate_AlgosController
 * @author valentin.claras
 * @package AF
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des algos.
 * @package AF
 * @subpackage Controller
 */
class AF_Datagrid_Translate_AlgosController extends UI_Controller_Datagrid
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
        foreach (Algo_Model_Numeric::loadList($this->request) as $algo) {
            $data = array();
            $data['index'] = $algo->getId();
            $data['identifier'] = $algo->getId();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $algo->reloadWithLocale($locale);
                $data[$language] = $algo->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Algo_Model_Numeric::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        $algo = Algo_Model_Numeric::load($this->update['index']);
        $algo->setTranslationLocale(Core_Locale::load($this->update['column']));
        $algo->setLabel($this->update['value']);
        $this->data = $algo->getLabel();

        $this->send(true);
    }
}