<?php
/**
 * Classe Keyword_Datagrid_Translate_KeywordsController
 * @author valentin.claras
 * @package Keyword
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Keyword\Domain\Keyword;
use Keyword\Domain\KeywordRepository;

/**
 * Classe du controller du datagrid des traductions des keywords.
 * @package Keyword
 * @subpackage Controller
 */
class Keyword_Datagrid_Translate_KeywordsController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var KeywordRepository
     */
    private $keywordRepository;

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
        foreach ($this->keywordRepository->getAll($this->request) as $keyword) {
            $data = array();
            $data['index'] = $keyword->getRef();
            $data['identifier'] = $keyword->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = \Core_Locale::load($language);
                $this->keywordRepository->changeLocale($keyword, $locale);
                $data[$language] = $keyword->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = $this->keywordRepository->count($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editKeyword")
     */
    public function updateelementAction()
    {
        $keyword = $this->keywordRepository->getOneByRef($this->update['index']);
        $this->keywordRepository->changeLocale($keyword, \Core_Locale::load($this->update['column']));
        $keyword->setLabel($this->update['value']);
        $this->data = $keyword->getLabel();

        $this->send(true);
    }
}
