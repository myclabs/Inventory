<?php
/**
 * Classe Orga_Datagrid_Translate_OrganizationsController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;
use User\Domain\ACL\Action;

/**
 * Classe du controller du datagrid des traductions des organizations.
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Translate_OrganizationsController extends UI_Controller_Datagrid
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
     * @Secure("editOrganizations")
     */
    public function getelementsAction()
    {
        $this->translatableListener->setTranslationFallback(false);
        $this->request->aclFilter->enabled = true;
        $this->request->aclFilter->user = $this->_helper->auth();
        $this->request->aclFilter->action = Action::VIEW();

        foreach (Orga_Model_Organization::loadList($this->request) as $organization) {
            $data = array();
            $data['index'] = $organization->getId();
            $data['identifier'] = $organization->getId();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $organization->reloadWithLocale($locale);
                $data[$language] = $organization->getLabel();
            }

            $data['axes'] = $this->cellLink('orga/translate/axes/idOrganization/'.$organization->getId());
            $data['members'] = $this->cellLink('orga/translate/members/idOrganization/'.$organization->getId());
            $data['granularityReports'] = $this->cellLink('orga/translate/granularityreports/idOrganization/'.$organization->getId());
            $this->addline($data);
        }
        $this->totalElements = Orga_Model_Organization::countTotal($this->request);

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
        $organization = Orga_Model_Organization::load($this->update['index']);
        $organization->reloadWithLocale(Core_Locale::load($this->update['column']));
        $organization->setLabel($this->update['value']);
        $this->data = $organization->getLabel();

        $this->send(true);
    }
}
