<?php
/**
 * Classe Orga_Datagrid_Translate_OrganizationsController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;
use User\Domain\ACL\Actions;

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
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $data = [];
        $data['index'] = $organization->getId();
        $data['identifier'] = $organization->getId();

        foreach ($this->languages as $language) {
            $locale = Core_Locale::load($language);
            $organization->reloadWithLocale($locale);
            $data[$language] = $organization->getLabel();
        }

        $this->addline($data);

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
