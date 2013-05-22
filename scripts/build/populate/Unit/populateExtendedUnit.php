<?php
/**
 * @author  hugo.charbonniere
 * @author  yoann.croizer
 * @package Unit
 *
 */

/**
 * Script de création de la table UniteEtendue
 * @package Unit
 */
class Unit_Script_Populate_extendedUnit
{

    /**
     * Lance la génération
     */
    public function run()
    {
        $this->generateExtendedUnit();
    }

    /**
     * Génère les unités étendues à partir des fichiers xml
     */
    protected function generateExtendedUnit()
    {
        $massPhysicalQuantity = Unit_Model_PhysicalQuantity::loadByRef('m');
        $this->parsePhysicalQuantity($massPhysicalQuantity);
    }

    /**
     * Parcours le fichier xml des unités étendues
     * @param Unit_Model_PhysicalQuantity $physicalQuantity
     */
    protected function parsePhysicalQuantity(Unit_Model_PhysicalQuantity $physicalQuantity)
    {
        foreach (Unit_Model_Unit_Extension::loadList() as $extension) {
            $this->parseExtendedUnit($extension, $physicalQuantity);
        }
    }

    /**
     * Parcours le fichier xml des unités étendues
     * @param Unit_Model_Unit_Extension   $extension
     * @param Unit_Model_PhysicalQuantity $physicalQuantity
     */
    protected function parseExtendedUnit(Unit_Model_Unit_Extension $extension,
                                         Unit_Model_PhysicalQuantity $physicalQuantity
    ) {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $query = new Core_Model_Query();
        $query->filter->addCondition(Unit_Model_Unit_Standard::QUERY_PHYSICALQUANTITY,
                                                     $physicalQuantity);

        foreach (Unit_Model_Unit_Standard::loadList($query) as $standardUnit) {
            /** @var Unit_Model_Unit_Standard $standardUnit */

            $extendedUnit = new Unit_Model_Unit_Extended();
            $extendedUnit->setRef($standardUnit->getRef() . '_' . $extension->getRef());
            $extendedUnit->setMultiplier($standardUnit->getMultiplier() * $extension->getMultiplier());
            $extendedUnit->setExtension($extension);
            $extendedUnit->setStandardUnit($standardUnit);

            foreach (['fr', 'en'] as $lang) {
                $locale = Core_Locale::load($lang);

                $standardUnit->reloadWithLocale($locale);
                $extension->reloadWithLocale($locale);

                $extendedUnit->setTranslationLocale($locale);

                $extendedUnit->setName($standardUnit->getName() . ' ' . $extension->getName());
                $extendedUnit->setSymbol($standardUnit->getSymbol() . ' ' . $extension->getSymbol());

                $extendedUnit->save();
                $entityManagers['unit']->flush();
            }
        }
    }
}

