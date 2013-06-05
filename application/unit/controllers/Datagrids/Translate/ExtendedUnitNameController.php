<?php
/**
 * @author     matthieu.napoli
 * @package    Unit
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Unit\Domain\Unit\ExtendedUnit;

/**
 * @package    Unit
 * @subpackage Controller
 */
class Unit_Datagrids_Translate_ExtendedUnitNameController extends UI_Controller_Datagrid
{

    /**
     * @Secure("viewTechno")
     */
    public function getelementsAction()
    {
        foreach (ExtendedUnit::loadList($this->request) as $unit) {
            /** @var ExtendedUnit $unit */
            $data = [];

            $data['identifier'] = $unit->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $unit->reloadWithLocale($locale);
                $data[$language] = $unit->getName();
            }
            $this->addline($data);
        }
        $this->totalElements = ExtendedUnit::countTotal($this->request);

        $this->send();
    }

}