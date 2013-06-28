<?php
/**
 * Classe Orga_Datagrid_Translate_OrganizationsController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des traductions des organizations.
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Translate_OrganizationsController extends UI_Controller_Datagrid
{
    /**
     * Désativation du fallback des traduction
     */
    public function init()
    {
        parent::init();
        Zend_Registry::get('doctrineTranslate')->setTranslationFallback(false);
    }

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("editOrganizations")
     */
    public function getelementsAction()
    {
        $this->request->aclFilter->enabled = true;
        $this->request->aclFilter->user = $this->_helper->auth();
        $this->request->aclFilter->action = User_Model_Action_Default::VIEW();

        foreach (Orga_Model_Organization::loadList($this->request) as $organization) {
            $data = array();
            $data['index'] = $organization->getId();
            $data['identifier'] = $organization->getId();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $organization->reloadWithLocale($locale);
                $data[$language] = $organization->getLabel();
            }

            $data['axes'] = $this->cellLink('orga/translate/axes/idOrganization/'.$organization->getId());
            $data['members'] = $this->cellLink('orga/translate/members/idOrganization/'.$organization->getId());
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
        $organization = Orga_Model_Organization::load($this->update['index']);
        $organization->setTranslationLocale(Core_Locale::load($this->update['column']));
        $organization->setLabel($this->update['value']);
        $this->data = $organization->getLabel();

        $this->send(true);
    }
}