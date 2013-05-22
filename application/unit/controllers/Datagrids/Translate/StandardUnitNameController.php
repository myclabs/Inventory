<?php
/**
 * @author     matthieu.napoli
 * @package    Unit
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * @package    Unit
 * @subpackage Controller
 */
class Unit_Datagrids_Translate_StandardUnitNameController extends UI_Controller_Datagrid
{

    /**
     * @Secure("viewTechno")
     */
    public function getelementsAction()
    {
        foreach (Unit_Model_Unit_Standard::loadList($this->request) as $unit) {
            /** @var Unit_Model_Unit_Standard $unit */
            $data = [];

            $data['identifier'] = $unit->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $unit->reloadWithLocale($locale);
                $data[$language] = $unit->getName();
            }
            $this->addline($data);
        }
        $this->totalElements = Unit_Model_Unit_Standard::countTotal($this->request);

        $this->send();
    }

}