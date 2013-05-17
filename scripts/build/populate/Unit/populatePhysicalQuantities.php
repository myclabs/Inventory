<?php
/**
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @package Unit
 *
 */

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
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['unit']->flush();
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
        $entityManagers = Zend_Registry::get('EntityManagers');

        $physicalQuantity = new Unit_Model_PhysicalQuantity();
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
            $entityManagers['unit']->flush();
        }
    }

    /**
     * Permet de mettre à jour une quantitée
     */
    public function update()
    {
        $this->updatePhysicalQuantities();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['unit']->flush();
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
        $physicalQuantity = Unit_Model_PhysicalQuantity::loadByRef($element->getAttribute('ref'));

        $unitRef = $element->getElementsByTagName('standardUnitRef')->item(0)->firstChild->nodeValue;
        $unit = Unit_Model_Unit_Standard::loadByRef($unitRef);
        $physicalQuantity->setReferenceUnit($unit);

        if ($element->getElementsByTagName('isBase')->item(0)->firstChild->nodeValue === 'false') {
            foreach ($element->getElementsByTagName('component') as $component) {
                $basePhysicalQuantityRef = $component->getElementsByTagName('baseQuantityRef')->item(0)->firstChild->nodeValue;
                $basePhysicalQuantity = Unit_Model_PhysicalQuantity::loadByRef($basePhysicalQuantityRef);
                $exponent = $component->getElementsByTagName('exponent')->item(0)->firstChild->nodeValue;
                $physicalQuantity->addPhysicalQuantityComponent($basePhysicalQuantity, $exponent);
            }
        } else {
            $physicalQuantity->addPhysicalQuantityComponent($physicalQuantity, 1);
        }
    }
}