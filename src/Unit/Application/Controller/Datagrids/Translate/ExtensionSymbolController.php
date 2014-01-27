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
     * @Inject("translation.languages")
     * @var string[]
     */
    private $languages;

    /**
     * @Secure("viewUnit")
     */
    public function getelementsAction()
    {
        foreach (UnitExtension::loadList($this->request) as $extension) {
            /** @var \Unit\Domain\UnitExtension $extension */
            $data = [];

            $data['identifier'] = $extension->getRef();

            foreach ($this->languages as $language) {
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
