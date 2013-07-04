<?php
/**
 * @author     matthieu.napoli
 * @package    Unit
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Unit\Domain\UnitExtension;

/**
 * @package    Unit
 * @subpackage Controller
 */
class Unit_Datagrids_Translate_ExtensionSymbolController extends UI_Controller_Datagrid
{

    /**
     * @Secure("viewTechno")
     */
    public function getelementsAction()
    {
        foreach (UnitExtension::loadList($this->request) as $extension) {
            /** @var \Unit\Domain\UnitExtension $extension */
            $data = [];

            $data['identifier'] = $extension->getRef();

            foreach (Zend_Registry::get('languages') as $language) {
                $locale = Core_Locale::load($language);
                $extension->reloadWithLocale($locale);
                $data[$language] = $extension->getSymbol();
            }
            $this->addline($data);
        }
        $this->totalElements = UnitExtension::countTotal($this->request);

        $this->send();
    }

}