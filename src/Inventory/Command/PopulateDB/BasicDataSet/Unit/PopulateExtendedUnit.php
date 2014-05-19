<?php

namespace Inventory\Command\PopulateDB\BasicDataSet\Unit;

use Core\Translation\TranslatedString;
use Core_Model_Query;
use Doctrine\ORM\EntityManager;
use Unit\Domain\Unit\ExtendedUnit;
use Unit\Domain\Unit\StandardUnit;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\UnitExtension;

/**
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @author matthieu.napoli
 */
class PopulateExtendedUnit
{
    /**
     * @Inject
     * @var EntityManager
     */
    private $entityManager;

    public function run()
    {
        $massPhysicalQuantity = PhysicalQuantity::loadByRef('m');

        foreach (UnitExtension::loadList() as $extension) {
            $this->parseExtendedUnit($extension, $massPhysicalQuantity);
        }
    }

    /**
     * Parcours le fichier xml des unités étendues
     * @param UnitExtension   $extension
     * @param PhysicalQuantity $physicalQuantity
     */
    protected function parseExtendedUnit(
        UnitExtension $extension,
        PhysicalQuantity $physicalQuantity
    ) {
        $query = new Core_Model_Query();
        $query->filter->addCondition(StandardUnit::QUERY_PHYSICALQUANTITY, $physicalQuantity);

        foreach (StandardUnit::loadList($query) as $standardUnit) {
            /** @var StandardUnit $standardUnit */

            $extendedUnit = new ExtendedUnit();
            $extendedUnit->setRef($standardUnit->getRef() . '_' . $extension->getRef());
            $extendedUnit->setMultiplier($standardUnit->getMultiplier() * $extension->getMultiplier());
            $extendedUnit->setExtension($extension);
            $extendedUnit->setStandardUnit($standardUnit);
            $extendedUnit->setName(
                TranslatedString::join([$standardUnit->getName(), ' ', $extension->getName()])
            );
            $extendedUnit->setSymbol(
                TranslatedString::join([$standardUnit->getSymbol(), ' ', $extension->getSymbol()])
            );
            $extendedUnit->save();
        }
        $this->entityManager->flush();
    }
}
