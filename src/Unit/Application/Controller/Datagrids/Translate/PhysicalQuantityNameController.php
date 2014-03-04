<?php
/**
 * @author     matthieu.napoli
 * @package    Unit
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Unit\Domain\PhysicalQuantity;

/**
 * @package    Unit
 * @subpackage Controller
 */
class Unit_Datagrids_Translate_PhysicalQuantityNameController extends UI_Controller_Datagrid
{

    /**
     * @Inject("translation.languages")
     * @var string[]
     */
    private $languages;

    /**
     * @Secure("viewUnit")
     */
    public function getelementsAction()
    {
        foreach (PhysicalQuantity::loadList($this->request) as $physicalQuantity) {
            /** @var PhysicalQuantity $physicalQuantity */
            $data = [];

            $data['identifier'] = $physicalQuantity->getRef();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $physicalQuantity->reloadWithLocale($locale);
                $data[$language] = $physicalQuantity->getName();
            }
            $this->addline($data);
        }
        $this->totalElements = PhysicalQuantity::countTotal($this->request);

        $this->send();
    }

}
