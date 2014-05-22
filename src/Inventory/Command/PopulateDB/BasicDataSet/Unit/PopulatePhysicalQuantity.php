<?php

namespace Inventory\Command\PopulateDB\BasicDataSet\Unit;

use Doctrine\ORM\EntityManager;
use DOMDocument;
use DOMElement;
use DOMNode;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\Unit\StandardUnit;

/**
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @author matthieu.napoli
 */
class PopulatePhysicalQuantity
{
    /**
     * @Inject
     * @var EntityManager
     */
    private $entityManager;

    public function run()
    {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/quantities.xml");
        foreach ($xml->getElementsByTagName('quantity') as $xmlPhysicalQuantity) {
            $this->parsePhysicalQuantity($xmlPhysicalQuantity);
        }

        $this->entityManager->flush();
    }

    protected function parsePhysicalQuantity(DOMElement $element)
    {
        $physicalQuantity = new PhysicalQuantity();
        $physicalQuantity->setRef($element->getAttribute('ref'));
        if ($element->getElementsByTagName('symbol')->item(0)->hasChildNodes()) {
            $physicalQuantity->setSymbol($element->getElementsByTagName('symbol')->item(0)->firstChild->nodeValue);
        }
        if ($element->getElementsByTagName('isBase')->item(0)->firstChild->nodeValue === 'true') {
            $physicalQuantity->setIsBase(true);
        } else {
            $physicalQuantity->setIsBase(false);
        }

        // Label
        foreach ($element->getElementsByTagName('name')->item(0)->childNodes as $node) {
            /** @var $node DOMNode */
            $lang = trim($node->nodeName);
            $value = trim($node->nodeValue);
            if ($lang == '' || $value == '') {
                continue;
            }

            $physicalQuantity->getName()->set($value, $lang);
        }
        $physicalQuantity->save();
        $this->entityManager->flush();
    }

    /**
     * Permet de mettre à jour une quantitée
     */
    public function update()
    {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/quantities.xml");
        $quantities = $xml->getElementsByTagName("quantity");
        foreach ($quantities as $quantity) {
            $this->updateParserQuantity($quantity);
        }

        $this->entityManager->flush();
    }

    protected function updateParserQuantity(DOMElement $element)
    {
        $physicalQuantity = PhysicalQuantity::loadByRef($element->getAttribute('ref'));

        $unitRef = $element->getElementsByTagName('standardUnitRef')->item(0)->firstChild->nodeValue;
        $unit = StandardUnit::loadByRef($unitRef);
        $physicalQuantity->setReferenceUnit($unit);

        if ($element->getElementsByTagName('isBase')->item(0)->firstChild->nodeValue === 'false') {
            foreach ($element->getElementsByTagName('component') as $component) {
                $basePhysicalQuantityRef = $component->getElementsByTagName('baseQuantityRef')
                    ->item(0)->firstChild->nodeValue;
                $basePhysicalQuantity = PhysicalQuantity::loadByRef($basePhysicalQuantityRef);
                $exponent = $component->getElementsByTagName('exponent')->item(0)->firstChild->nodeValue;
                $physicalQuantity->addPhysicalQuantityComponent($basePhysicalQuantity, $exponent);
            }
        } else {
            $physicalQuantity->addPhysicalQuantityComponent($physicalQuantity, 1);
        }
    }
}
