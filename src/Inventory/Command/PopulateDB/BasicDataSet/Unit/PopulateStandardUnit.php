<?php

namespace Inventory\Command\PopulateDB\BasicDataSet\Unit;

use Doctrine\ORM\EntityManager;
use DOMDocument;
use DOMElement;
use DOMNode;
use Unit\Domain\Unit\StandardUnit;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\UnitSystem;

/**
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @author matthieu.napoli
 */
class PopulateStandardUnit
{
    /**
     * @Inject
     * @var EntityManager
     */
    private $entityManager;

    public function run()
    {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/standardUnit.xml");
        foreach ($xml->getElementsByTagName('standardUnit') as $standardUnit) {
            $this->parseStandardUnit($standardUnit);
        }

        $this->entityManager->flush();
    }

    /**
     * Parcours le fichier xml des unités standards
     * @param DOMElement $element
     */
    protected function parseStandardUnit(DOMElement $element)
    {
        $unit = new StandardUnit();
        $unit->setRef($element->getAttribute('ref'));
        $unit->setMultiplier($element->getElementsByTagName('multiplier')->item(0)->firstChild->nodeValue);

        $refUnitSystem = $element->getElementsByTagName('unitSystemRef')->item(0)->firstChild->nodeValue;
        $unitSystem = UnitSystem::loadByRef($refUnitSystem);
        $unit->setUnitSystem($unitSystem);

        $refPhysicalQuantity = $element->getElementsByTagName('quantityRef')->item(0)->firstChild->nodeValue;
        $physicalQuantity = PhysicalQuantity::loadByRef($refPhysicalQuantity);
        $unit->setPhysicalQuantity($physicalQuantity);

        // Label & Symbol
        foreach (['fr', 'en'] as $lang) {
            // Label
            $found = false;
            foreach ($element->getElementsByTagName('name')->item(0)->childNodes as $node) {
                if (trim($node->nodeName) == $lang) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                echo "WARN: not found $lang traduction for name";
                continue;
            }
            /** @var $node DOMNode */
            $name = trim($node->nodeValue);

            // Symbol
            $found = false;
            foreach ($element->getElementsByTagName('symbol')->item(0)->childNodes as $node) {
                if (trim($node->nodeName) == $lang) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                echo "WARN: not found $lang traduction for symbol";
                continue;
            }
            /** @var $node DOMNode */
            $symbol = trim($node->nodeValue);

            $unit->getName()->set($name, $lang);
            $unit->getSymbol()->set($symbol, $lang);
        }

        $unit->save();
        $this->entityManager->flush();
    }
}
