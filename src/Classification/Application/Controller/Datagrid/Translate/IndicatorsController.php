<?php
/**
 * Classe Classification_Datagrid_Translate_IndicatorsController
 * @author valentin.claras
 * @package Classification
 * @subpackage Controller
 */

use Classification\Domain\Indicator;
use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des indicators.
 * @package Classification
 * @subpackage Controller
 */
class Classification_Datagrid_Translate_IndicatorsController extends UI_Controller_Datagrid
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
     * @Secure("editClassification")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        foreach (Indicator::loadList($this->request) as $indicator) {
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
        $this->totalElements = Indicator::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editClassification")
     */
    public function updateelementAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $indicator = Indicator::loadByRef($this->update['index']);
        $indicator->reloadWithLocale(Core_Locale::load($this->update['column']));
        $indicator->setLabel($this->update['value']);
        $this->data = $indicator->getLabel();

        $this->send(true);
    }
}
