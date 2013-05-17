<?php
/**
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @package Unit
 *
 */

/**
 * Script de création de la table Unité standard
 * @package Unit
 */
class Unit_Script_Populate_StandardUnit
{
    /**
     * Lance la génération
     */
    public function run()
    {
        $this->generateStandardUnit();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['unit']->flush();
    }


    /**
     * Génère les unités stantdars à partir des fichiers xml
     */
     protected function generateStandardUnit()
     {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/standardUnit.xml");
        foreach ($xml->getElementsByTagName('standardUnit') as $standardUnit) {
            $this->parseStandardUnit($standardUnit);
        }
     }


    /**
     * Parcours le fichier xml des unités standards
     * @param DOMElement $element
     */
    protected function parseStandardUnit(DOMElement $element)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $unit = new Unit_Model_Unit_Standard();
        $unit->setRef($element->getAttribute('ref'));
        $unit->setMultiplier($element->getElementsByTagName('multiplier')->item(0)->firstChild->nodeValue);

        $refUnitSystem = $element->getElementsByTagName('unitSystemRef')->item(0)->firstChild->nodeValue;
        $unitSystem = Unit_Model_Unit_System::loadByRef($refUnitSystem);
        $unit->setUnitSystem($unitSystem);

        $refPhysicalQuantity = $element->getElementsByTagName('quantityRef')->item(0)->firstChild->nodeValue;
        $physicalQuantity = Unit_Model_PhysicalQuantity::loadByRef($refPhysicalQuantity);
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

            $unit->setTranslationLocale(Core_Locale::load($lang));
            $unit->setName($name);
            $unit->setSymbol($symbol);
            $unit->save();
            $entityManagers['unit']->flush();
        }
    }

}
