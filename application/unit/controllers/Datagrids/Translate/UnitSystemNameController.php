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
class Unit_Datagrids_Translate_UnitSystemNameController extends UI_Controller_Datagrid
{

    /**
     * @Secure("viewTechno")
     */
    public function getelementsAction()
    {
        foreach (Unit_Model_Unit_System::loadList($this->request) as $unitSystem) {
            /** @var Unit_Model_Unit_System $unitSystem */
            $data = [];

            $data['identifier'] = $unitSystem->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $unitSystem->reloadWithLocale($locale);
                $data[$language] = $unitSystem->getName();
            }
            $this->addline($data);
        }
        $this->totalElements = Unit_Model_Unit_System::countTotal($this->request);

        $this->send();
    }

}