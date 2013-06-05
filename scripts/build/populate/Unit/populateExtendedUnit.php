<?php
/**
 * @author  hugo.charbonniere
 * @author  yoann.croizer
 * @package Unit
 *
 */
use Unit\Domain\Unit\ExtendedUnit;
use Unit\Domain\Unit\StandardUnit;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\UnitExtension;

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
        $massPhysicalQuantity = PhysicalQuantity::loadByRef('m');
        $this->parsePhysicalQuantity($massPhysicalQuantity);
    }

    /**
     * Parcours le fichier xml des unités étendues
     * @param PhysicalQuantity $physicalQuantity
     */
    protected function parsePhysicalQuantity(PhysicalQuantity $physicalQuantity)
    {
        foreach (UnitExtension::loadList() as $extension) {
            $this->parseExtendedUnit($extension, $physicalQuantity);
        }
    }

    /**
     * Parcours le fichier xml des unités étendues
     * @param UnitExtension   $extension
     * @param PhysicalQuantity $physicalQuantity
     */
    protected function parseExtendedUnit(UnitExtension $extension,
                                         PhysicalQuantity $physicalQuantity
    ) {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $query = new Core_Model_Query();
        $query->filter->addCondition(StandardUnit::QUERY_PHYSICALQUANTITY,
                                                     $physicalQuantity);

        foreach (StandardUnit::loadList($query) as $standardUnit) {
            /** @var \Unit\Domain\Unit\StandardUnit $standardUnit */

            $extendedUnit = new ExtendedUnit();
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

