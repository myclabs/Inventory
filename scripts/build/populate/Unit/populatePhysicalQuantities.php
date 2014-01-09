<?php
/**
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @package Unit
 *
 */
use Unit\Domain\Unit\StandardUnit;
use Unit\Domain\PhysicalQuantity;

/**
 * Script de création des tables grandeurphysique et composantGrandeurPhysique
 * @package Unit
 */
class Unit_Script_Populate_PhysicalQuantities
{
    /**
     * Lance la génération
     */
    public function run()
    {
        $this->generatePhysicalQuantities();
        \Core\ContainerSingleton::getEntityManager()->flush();
    }

    /**
     * Génère les quantites  à partir des fichiers xml
     */
     protected function generatePhysicalQuantities()
     {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/quantities.xml");
        foreach ($xml->getElementsByTagName('quantity') as $xmlPhysicalQuantity) {
            $this->parsePhysicalQuantity($xmlPhysicalQuantity);
        }
     }

    /**
     * Parcours le fichier xml des quantites
     * @param DOMElement $element
     */
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

            $physicalQuantity->setTranslationLocale(Core_Locale::load($lang));
            $physicalQuantity->setName($value);
            $physicalQuantity->save();
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }

    /**
     * Permet de mettre à jour une quantitée
     */
    public function update()
    {
        $this->updatePhysicalQuantities();
        \Core\ContainerSingleton::getEntityManager()->flush();
    }

    /**
     * appel updtate()
     */
    protected function updatePhysicalQuantities()
    {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/quantities.xml");
        $quantities = $xml->getElementsByTagName("quantity");
        foreach ($quantities as $quantity) {
            $this->updateParserQuantity($quantity);
        }
    }

    /**
     * Enter description here ...
     * @param DOMElement $element
     */
    protected function updateParserQuantity(DOMElement $element)
    {
        $physicalQuantity = PhysicalQuantity::loadByRef($element->getAttribute('ref'));

        $unitRef = $element->getElementsByTagName('standardUnitRef')->item(0)->firstChild->nodeValue;
        $unit = StandardUnit::loadByRef($unitRef);
        $physicalQuantity->setReferenceUnit($unit);

        if ($element->getElementsByTagName('isBase')->item(0)->firstChild->nodeValue === 'false') {
            foreach ($element->getElementsByTagName('component') as $component) {
                $basePhysicalQuantityRef = $component->getElementsByTagName('baseQuantityRef')->item(0)->firstChild->nodeValue;
                $basePhysicalQuantity = PhysicalQuantity::loadByRef($basePhysicalQuantityRef);
                $exponent = $component->getElementsByTagName('exponent')->item(0)->firstChild->nodeValue;
                $physicalQuantity->addPhysicalQuantityComponent($basePhysicalQuantity, $exponent);
            }
        } else {
            $physicalQuantity->addPhysicalQuantityComponent($physicalQuantity, 1);
        }
    }
}
