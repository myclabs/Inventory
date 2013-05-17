<?php
/**
 * @author hugo.charbonniere
 * @author yoann.croizer
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
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['unit']->flush();
    }

    /**
     * Génère les unités étendues à partir des fichiers xml
     */
    protected function generateExtendedUnit()
    {
     $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/extension.xml");
        foreach ($xml->getElementsByTagName('quantity') as $xmlPhysicalQuantity) {
            $this->parsePhysicalQuantity($xmlPhysicalQuantity);
        }
    }

    /**
     * Parcours le fichier xml des unités étendues
     * @param DOMElement $element
     */
    protected function parsePhysicalQuantity(DOMElement $element)
    {
        foreach ($element->getElementsByTagName('extension') as $xmlExtensions) {
            $physicalQuantity = Unit_Model_PhysicalQuantity::loadByRef($element->getAttribute('ref'));
            $this->parseExtendedUnit($xmlExtensions, $physicalQuantity);
        }
    }

    /**
     * Parcours le fichier xml des unités étendues
     * @param DOMElement $element
     * @param Unit_Model_PhysicalQuantity $physicalQuantity
     */
    protected function parseExtendedUnit(DOMElement $element, $physicalQuantity)
    {
        $extension = Unit_Model_Unit_Extension::loadByRef($element->getAttribute('ref'));

        $queryPhysicalQuantity = new Core_Model_Query();
        $queryPhysicalQuantity->filter->addCondition(Unit_Model_Unit_Standard::QUERY_PHYSICALQUANTITY, $physicalQuantity);
        foreach (Unit_Model_Unit_Standard::loadList($queryPhysicalQuantity) as $standardUnit) {
            $extendedUnit = new Unit_Model_Unit_Extended();
            $extendedUnit->setRef($standardUnit->getRef().'_'.$element->getAttribute('ref'));
            $extendedUnit->setName(
                    $standardUnit->getName().' '.$element->getElementsByTagName('name')->item(0)->firstChild->nodeValue
                );
            $extendedUnit->setSymbol(
                    $standardUnit->getSymbol().' '.$element->getElementsByTagName('symbol')->item(0)->firstChild->nodeValue
                );
            $extendedUnit->setMultiplier(
                    $standardUnit->getMultiplier() * $element->getElementsByTagName('multiplier')->item(0)->firstChild->nodeValue
                );
            $extendedUnit->setExtension($extension);
            $extendedUnit->setStandardUnit($standardUnit);
            $extendedUnit->save();
        }
    }
}

