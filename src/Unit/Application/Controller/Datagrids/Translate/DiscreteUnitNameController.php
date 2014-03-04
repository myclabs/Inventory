<?php
/**
 * @author     matthieu.napoli
 * @package    Unit
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Unit\Domain\Unit\DiscreteUnit;

/**
 * @package    Unit
 * @subpackage Controller
 */
class Unit_Datagrids_Translate_DiscreteUnitNameController extends UI_Controller_Datagrid
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
        foreach (DiscreteUnit::loadList($this->request) as $unit) {
            /** @var \Unit\Domain\Unit\DiscreteUnit $unit */
            $data = [];

            $data['identifier'] = $unit->getRef();

            foreach ($this->languages as $language) {
                $locale = Core_Locale::load($language);
                $unit->reloadWithLocale($locale);
                $data[$language] = $unit->getName();
            }
            $this->addline($data);
        }
        $this->totalElements = DiscreteUnit::countTotal($this->request);

        $this->send();
    }
}
