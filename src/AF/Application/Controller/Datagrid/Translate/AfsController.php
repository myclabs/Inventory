<?php
/**
 * Classe AF_Datagrid_Translate_AfsController
 * @author valentin.claras
 * @package AF
 * @subpackage Controller
 */

use AF\Domain\AF;
use AF\Domain\AFLibrary;
use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des afs.
 * @package AF
 * @subpackage Controller
 */
class AF_Datagrid_Translate_AfsController extends UI_Controller_Datagrid
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
     * @Secure("editAFLibrary")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);

        $library = AFLibrary::load($this->getParam('library'));
        $this->request->filter->addCondition('library', $library);

        foreach (AF::loadList($this->request) as $aF) {
            $data = array();
            $data['index'] = $aF->getId();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $aF->reloadWithLocale($locale);
                $data[$language] = $aF->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = AF::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editAFLibrary")
     */
    public function updateelementAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $aF = AF::load($this->update['index']);
        $aF->reloadWithLocale(Core_Locale::load($this->update['column']));
        $aF->setLabel($this->update['value']);
        $this->data = $aF->getLabel();

        $this->send(true);
    }
}
