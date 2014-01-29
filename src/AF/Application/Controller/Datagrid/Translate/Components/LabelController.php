<?php
/**
 * Classe AF_Datagrid_Translate_ComponentsController
 * @author valentin.claras
 * @package AF
 * @subpackage Controller
 */

use AF\Domain\AF\Component;
use AF\Domain\AF\Component\AF_Model_Component_Group;
use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des components.
 * @package AF
 * @subpackage Controller
 */
class AF_Datagrid_Translate_Components_LabelController extends UI_Controller_Datagrid
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
        $this->request->filter->addCondition(
            Component::QUERY_REF,
            AF_Model_Component_Group::ROOT_GROUP_REF,
            Core_Model_Filter::OPERATOR_NOT_EQUAL
        );
        foreach (Component::loadList($this->request) as $component) {
            $data = array();
            $data['index'] = $component->getId();
            $data['identifier'] = $component->getAF()->getRef().' | '.$component->getRef();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $component->reloadWithLocale($locale);
                $data[$language] = $component->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Component::countTotal($this->request);

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
        $component = Component::load($this->update['index']);
        $component->reloadWithLocale(Core_Locale::load($this->update['column']));
        $component->setLabel($this->update['value']);
        $this->data = $component->getLabel();

        $this->send(true);
    }
}
