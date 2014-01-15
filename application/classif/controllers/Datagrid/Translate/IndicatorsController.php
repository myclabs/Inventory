<?php
/**
 * Classe Classif_Datagrid_Translate_IndicatorsController
 * @author valentin.claras
 * @package Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des indicators.
 * @package Classif
 * @subpackage Controller
 */
class Classif_Datagrid_Translate_IndicatorsController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var TranslatableListener
     */
    private $translatableListener;

    /**
     * @Inject("translation.languages")
     * @var string[]
     */
    private $languages;

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("editClassif")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        foreach (Classif_Model_Indicator::loadList($this->request) as $indicator) {
            $data = array();
            $data['index'] = $indicator->getRef();
            $data['identifier'] = $indicator->getRef();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $indicator->reloadWithLocale($locale);
                $data[$language] = $indicator->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Classif_Model_Indicator::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editClassif")
     */
    public function updateelementAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $indicator = Classif_Model_Indicator::loadByRef($this->update['index']);
        $indicator->reloadWithLocale(Core_Locale::load($this->update['column']));
        $indicator->setLabel($this->update['value']);
        $this->data = $indicator->getLabel();

        $this->send(true);
    }
}
