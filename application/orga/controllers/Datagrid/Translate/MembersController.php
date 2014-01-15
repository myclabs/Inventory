<?php
/**
 * Classe Orga_Datagrid_Translate_MembersController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des members.
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Translate_MembersController extends UI_Controller_Datagrid
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
     * @Secure("editOrganization")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $this->request->filter->addCondition(
            Orga_Model_Member::QUERY_AXIS,
            Orga_Model_Organization::load($this->getParam('idOrganization'))->getAxisByRef(
                $this->getParam('refAxis')
            )
        );

        foreach (Orga_Model_Member::loadList($this->request) as $member) {
            $data = array();
            $data['index'] = $member->getCompleteRef();
            $data['identifier'] = $member->getAxis()->getRef().' | '.$member->getRef();
            $parentMembersHshKey = $member->getParentMembersHashKey();
            if (!empty($parentMembersHshKey)) {
                $data['identifier'] .= ' ('. $parentMembersHshKey .')';
            }

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $member->reloadWithLocale($locale);
                $data[$language] = $member->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = Orga_Model_Member::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editOrganization")
     */
    public function updateelementAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = $organization->getAxisByRef($this->getParam('refAxis'));
        $member = $axis->getMemberByCompleteRef($this->update['index']);
        $member->reloadWithLocale(Core_Locale::load($this->update['column']));
        $member->setLabel($this->update['value']);
        $this->data = $member->getLabel();

        $this->send(true);
    }
}
