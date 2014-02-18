<?php
/**
 * Classe Classification_Datagrid_Translate_AxesController
 * @author valentin.claras
 * @package Classification
 * @subpackage Controller
 */

use Classification\Domain\IndicatorAxis;
use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des axes.
 * @package Classification
 * @subpackage Controller
 */
class Classification_Datagrid_Translate_AxesController extends UI_Controller_Datagrid
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
        foreach (IndicatorAxis::loadList($this->request) as $axis) {
            $data = array();
            $data['index'] = $axis->getRef();
            $data['identifier'] = $axis->getRef();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $axis->reloadWithLocale($locale);
                $data[$language] = $axis->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = IndicatorAxis::countTotal($this->request);

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
        $axis = IndicatorAxis::loadByRef($this->update['index']);
        $axis->reloadWithLocale(Core_Locale::load($this->update['column']));
        $axis->setLabel($this->update['value']);
        $this->data = $axis->getLabel();

        $this->send(true);
    }
}
