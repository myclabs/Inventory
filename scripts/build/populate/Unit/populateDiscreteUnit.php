<?php
/**
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @package Unit
 *
 */

/**
 * Script de création de la table UniteDiscrete
 * @package Unit
 */
class Unit_Script_Populate_DiscreteUnit
{
    /**
     * Lance la génération
     */
    public function run()
    {
        $this->generateDiscretesUnits();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['unit']->flush();
    }

    /**
     * Génère les unités discrètes à partir des fichiers xml
     */
    protected function generateDiscretesUnits()
    {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/discreteUnit.xml");
        foreach ($xml->getElementsByTagName('discreteUnit') as $discreteUnit) {
            $this->parseDiscreteUnit($discreteUnit);
        }
    }

    /**
     * Parcours le fichier xml des unités discrètes
     * @param DOMElement $element
     */
    protected function parseDiscreteUnit(DOMElement $element)
    {
        $discreteUnit = new Unit_Model_Unit_Discrete();
        $discreteUnit->setRef($element->getAttribute('ref'));
        $discreteUnit->setName($element->getElementsByTagName('name')->item(0)->firstChild->nodeValue);
        $discreteUnit->setSymbol($element->getElementsByTagName('name')->item(0)->firstChild->nodeValue);
        $discreteUnit->save();
    }

}