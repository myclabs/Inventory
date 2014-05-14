<?php

namespace Inventory\Command\PopulateDB\BasicDataSet\Unit;

use Doctrine\ORM\EntityManager;
use DOMDocument;
use DOMElement;
use DOMNode;
use Unit\Domain\UnitSystem;

/**
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @author matthieu.napoli
 */
class PopulateUnitSystem
{
    /**
     * @Inject
     * @var EntityManager
     */
    private $entityManager;

    public function run()
    {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/unitSystem.xml");
        foreach ($xml->getElementsByTagName('unitSystem') as $xmlUnitSystem) {
            $this->parseUnitSystem($xmlUnitSystem);
        }

        $this->entityManager->flush();
    }

    /**
     * Parcours le fichier xml des système d'unité
     * @param DOMElement $element
     */
    protected function parseUnitSystem(DOMElement $element)
    {
        $unitSystem = new UnitSystem();
        $unitSystem->setRef($element->getAttribute('ref'));

        // Label
        foreach ($element->getElementsByTagName('name')->item(0)->childNodes as $node) {
            /** @var $node DOMNode */
            $lang = trim($node->nodeName);
            $value = trim($node->nodeValue);
            if ($lang == '' || $value == '') {
                continue;
            }

            $unitSystem->getName()->set($value, $lang);
        }
        $unitSystem->save();
        $this->entityManager->flush();
    }
}
