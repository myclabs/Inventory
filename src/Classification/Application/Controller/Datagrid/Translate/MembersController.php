<?php
/**
 * Classe Classification_Datagrid_Translate_MembersController
 * @author valentin.claras
 * @package Classification
 * @subpackage Controller
 */

use Classification\Domain\AxisMember;
use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des members.
 * @package Classification
 * @subpackage Controller
 */
class Classification_Datagrid_Translate_MembersController extends UI_Controller_Datagrid
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
        foreach (AxisMember::loadList($this->request) as $member) {
            $data = array();
            $data['index'] = $member->getId();
            $data['identifier'] = $member->getAxis()->getRef().' | '.$member->getRef();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $member->reloadWithLocale($locale);
                $data[$language] = $member->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = AxisMember::countTotal($this->request);

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
        $member = AxisMember::load($this->update['index']);
        $member->reloadWithLocale(Core_Locale::load($this->update['column']));
        $member->setLabel($this->update['value']);
        $this->data = $member->getLabel();

        $this->send(true);
    }
}
