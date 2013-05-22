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
class Unit_Datagrids_Translate_PhysicalQuantityNameController extends UI_Controller_Datagrid
{

    /**
     * @Secure("viewTechno")
     */
    public function getelementsAction()
    {
        foreach (Unit_Model_PhysicalQuantity::loadList($this->request) as $physicalQuantity) {
            /** @var Unit_Model_PhysicalQuantity $physicalQuantity */
            $data = [];

            $data['identifier'] = $physicalQuantity->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $physicalQuantity->reloadWithLocale($locale);
                $data[$language] = $physicalQuantity->getName();
            }
            $this->addline($data);
        }
        $this->totalElements = Unit_Model_PhysicalQuantity::countTotal($this->request);

        $this->send();
    }

}