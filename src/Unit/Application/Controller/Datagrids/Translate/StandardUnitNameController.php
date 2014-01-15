<?php
/**
 * @author     matthieu.napoli
 * @package    Unit
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Unit\Domain\Unit\StandardUnit;

/**
 * @package    Unit
 * @subpackage Controller
 */
class Unit_Datagrids_Translate_StandardUnitNameController extends UI_Controller_Datagrid
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
        foreach (StandardUnit::loadList($this->request) as $unit) {
            /** @var \Unit\Domain\Unit\StandardUnit $unit */
            $data = [];

            $data['identifier'] = $unit->getRef();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $unit->reloadWithLocale($locale);
                $data[$language] = $unit->getName();
            }
            $this->addline($data);
        }
        $this->totalElements = StandardUnit::countTotal($this->request);

        $this->send();
    }

}
