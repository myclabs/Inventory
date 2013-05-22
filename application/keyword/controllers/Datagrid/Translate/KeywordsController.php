<?php
/**
 * Classe Keyword_Datagrid_Translate_KeywordsController
 * @author valentin.claras
 * @package Keyword
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des keywords.
 * @package Keyword
 * @subpackage Controller
 */
class Keyword_Datagrid_Translate_KeywordsController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("viewKeyword")
     */
    public function getelementsAction()
    {
        foreach (Keyword_Model_Keyword::loadList($this->request) as $keyword) {
            $data = array();
            $data['index'] = $keyword->getRef();
            $data['identifier'] = $keyword->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $keyword->reloadWithLocale($locale);
                $data[$language] = $keyword->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Keyword_Model_Keyword::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editKeyword")
     */
    public function updateelementAction()
    {
        $keyword = Keyword_Model_Keyword::loadByRef($this->update['index']);
        $keyword->setTranslationLocale(Core_Locale::load($this->update['column']));
        $keyword->setLabel($this->update['value']);
        $this->data = $keyword->getLabel();

        $this->send(true);
    }
}