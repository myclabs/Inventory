<?php
/**
 * Classe Keyword_Datagrid_Translate_Predicates_ReverselabelController
 * @author valentin.claras
 * @package Keyword
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Keyword\Domain\Predicate;

/**
 * Classe du controller du datagrid des traductions des labels inversés des predicates.
 * @package Keyword
 * @subpackage Controller
 */
class Keyword_Datagrid_Translate_Predicates_ReverselabelController extends UI_Controller_Datagrid
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
     * @Secure("editKeyword")
     */
    public function getelementsAction()
    {
        foreach (Predicate::loadList($this->request) as $predicate) {
            $data = array();
            $data['index'] = $predicate->getReverseRef();
            $data['identifier'] = $predicate->getReverseRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $predicate->reloadWithLocale($locale);
                $data[$language] = $predicate->getReverseLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Predicate::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editKeyword")
     */
    public function updateelementAction()
    {
        $predicate = Predicate::loadByReverseRef($this->update['index']);
        $predicate->reloadWithLocale(Core_Locale::load($this->update['column']));
        $predicate->setReverseLabel($this->update['value']);
        $this->data = $predicate->getReverseLabel();

        $this->send(true);
    }
}
