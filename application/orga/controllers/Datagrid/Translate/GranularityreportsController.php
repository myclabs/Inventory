<?php
/**
 * Classe Orga_Datagrid_Translate_GranularityreportsController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Gedmo\Translatable\TranslatableListener;

/**
 * Classe du controller du datagrid des traductions des Reports de DW issus des Granularity.
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Translate_GranularityreportsController extends UI_Controller_Datagrid
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
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $this->request->filter->addCondition(
            DW_Model_Report::QUERY_CUBE,
            $organization->getGranularityByRef($this->getParam('refGranularity'))->getDWCube()
        );

        foreach (DW_Model_Report::loadList($this->request) as $report) {
            $data = array();
            $data['index'] = $report->getKey()['id'];
            $data['identifier'] = $report->getKey()['id'];

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $report->reloadWithLocale($locale);
                $data[$language] = $report->getLabel();
            }
            $this->addline($data);
        }
        $this->totalElements = DW_Model_Report::countTotal($this->request);

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
        $report = DW_Model_Report::load($this->update['index']);
        $report->reloadWithLocale(Core_Locale::load($this->update['column']));
        $report->setLabel($this->update['value']);
        $this->data = $report->getLabel();

        $this->send(true);
    }
}
