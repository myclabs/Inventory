<?php

use Core\Annotation\Secure;
use Techno\Domain\Family\Family;

/**
 * Classe du controller du datagrid des traductions des families.
 * @author valentin.claras
 */
class Techno_Datagrid_Translate_Families_LabelController extends UI_Controller_Datagrid
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
     * @Secure("editTechno")
     */
    public function getelementsAction()
    {
        foreach (Family::loadList($this->request) as $family) {
            /** @var Family $family */
            $data = array();
            $data['index'] = $family->getId();
            $data['identifier'] = $family->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $family->reloadWithLocale($locale);
                $data[$language] = $family->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Family::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editTechno")
     */
    public function updateelementAction()
    {
        $family = Family::load($this->update['index']);
        $family->reloadWithLocale(Core_Locale::load($this->update['column']));
        $family->setLabel($this->update['value']);
        $this->data = $family->getLabel();

        $this->send(true);
    }
}