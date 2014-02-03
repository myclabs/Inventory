<?php
/**
 * Classe AF_Datagrid_Translate_AlgosController
 * @author valentin.claras
 * @package AF
 * @subpackage Controller
 */

use AF\Domain\Algorithm\Numeric\NumericAlgo;
use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des algos.
 * @package AF
 * @subpackage Controller
 */
class AF_Datagrid_Translate_AlgosController extends UI_Controller_Datagrid
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
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        foreach (NumericAlgo::loadList($this->request) as $algo) {
            $data = array();
            $data['index'] = $algo->getId();
            $data['identifier'] = $algo->getId();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $algo->reloadWithLocale($locale);
                $data[$language] = $algo->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = NumericAlgo::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $algo = NumericAlgo::load($this->update['index']);
        $algo->reloadWithLocale(Core_Locale::load($this->update['column']));
        $algo->setLabel($this->update['value']);
        $this->data = $algo->getLabel();

        $this->send(true);
    }
}