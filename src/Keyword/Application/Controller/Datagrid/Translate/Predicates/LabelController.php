<?php
/**
 * Classe Keyword_Datagrid_Translate_Predicates_LabelController
 * @author valentin.claras
 * @package Keyword
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Keyword\Domain\Predicate;
use Keyword\Domain\PredicateRepository;

/**
 * Classe du controller du datagrid des traductions des labels des predicates.
 * @package Keyword
 * @subpackage Controller
 */
class Keyword_Datagrid_Translate_Predicates_LabelController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var PredicateRepository
     */
    private $predicateRepository;

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
        foreach ($this->predicateRepository->getAll($this->request) as $predicate) {
            $data = array();
            $data['index'] = $predicate->getRef();
            $data['identifier'] = $predicate->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = \Core_Locale::load($language);
                $this->predicateRepository->changeLocale($predicate, $locale);
                $data[$language] = $predicate->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = $this->predicateRepository->count($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editKeyword")
     */
    public function updateelementAction()
    {
        $predicate = $this->predicateRepository->getByRef($this->update['index']);
        $this->predicateRepository->changeLocale($predicate, \Core_Locale::load($this->update['column']));
        $predicate->setLabel($this->update['value']);
        $this->data = $predicate->getLabel();

        $this->send(true);
    }
}
