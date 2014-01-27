<?php
/**
 * @author     matthieu.napoli
 * @package    Unit
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Unit\Domain\UnitSystem;

/**
 * @package    Unit
 * @subpackage Controller
 */
class Unit_Datagrids_Translate_UnitSystemNameController extends UI_Controller_Datagrid
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
        foreach (UnitSystem::loadList($this->request) as $unitSystem) {
            /** @var UnitSystem $unitSystem */
            $data = [];

            $data['identifier'] = $unitSystem->getRef();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $unitSystem->reloadWithLocale($locale);
                $data[$language] = $unitSystem->getName();
            }
            $this->addline($data);
        }
        $this->totalElements = UnitSystem::countTotal($this->request);

        $this->send();
    }

}
