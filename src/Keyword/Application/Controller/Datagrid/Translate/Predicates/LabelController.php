<?php
/**
 * Classe Keyword_Datagrid_Translate_Predicates_LabelController
 * @author valentin.claras
 * @package Keyword
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Keyword\Domain\Predicate;

/**
 * Classe du controller du datagrid des traductions des labels des predicates.
 * @package Keyword
 * @subpackage Controller
 */
class Keyword_Datagrid_Translate_Predicates_LabelController extends UI_Controller_Datagrid
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
            $data['index'] = $predicate->getRef();
            $data['identifier'] = $predicate->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $predicate->reloadWithLocale($locale);
                $data[$language] = $predicate->getLabel();
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
        $predicate = Predicate::loadByRef($this->update['index']);
        $predicate->reloadWithLocale(Core_Locale::load($this->update['column']));
        $predicate->setLabel($this->update['value']);
        $this->data = $predicate->getLabel();

        $this->send(true);
    }
}
