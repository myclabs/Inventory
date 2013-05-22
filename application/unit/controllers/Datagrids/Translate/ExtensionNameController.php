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
class Unit_Datagrids_Translate_ExtensionNameController extends UI_Controller_Datagrid
{

    /**
     * @Secure("viewTechno")
     */
    public function getelementsAction()
    {
        foreach (Unit_Model_Unit_Extension::loadList($this->request) as $extension) {
            /** @var Unit_Model_Unit_Extension $extension */
            $data = [];

            $data['identifier'] = $extension->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $extension->reloadWithLocale($locale);
                $data[$language] = $extension->getName();
            }
            $this->addline($data);
        }
        $this->totalElements = Unit_Model_Unit_Extension::countTotal($this->request);

        $this->send();
    }

}