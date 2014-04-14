<?php
/**
 * Classe Classification_Datagrid_Translate_AxesController
 * @author valentin.claras
 * @package Classification
 * @subpackage Controller
 */

use Classification\Domain\Axis;
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
     * @Secure("editClassificationLibrary")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        foreach (Axis::loadList($this->request) as $axis) {
            $data = [];
            $data['index'] = $axis->getId();
            $data['identifier'] = $axis->getRef();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $axis->reloadWithLocale($locale);
                $data[$language] = $axis->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Axis::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editClassificationLibrary")
     */
    public function updateelementAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $axis = Axis::load($this->update['index']);
        $axis->reloadWithLocale(Core_Locale::load($this->update['column']));
        $axis->setLabel($this->update['value']);
        $this->data = $axis->getLabel();

        $this->send(true);
    }
}
