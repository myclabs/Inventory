<?php
/**
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @package Unit
 *
 */
use Unit\Domain\UnitSystem;

/**
 * Script de création de la table SystemUnite
 * @package Unit
 */
class Unit_Script_Populate_UnitSystem
{

    /**
     * Lance la génération
     */
    public function run()
    {
        $this->generateUnitSystem();
        \Core\ContainerSingleton::getEntityManager()->flush();
    }

    /**
     * Génère les systèmes d'unité à partir des fichiers xml
     */
    protected function generateUnitSystem()
    {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/unitSystem.xml");
        foreach ($xml->getElementsByTagName('unitSystem') as $xmlUnitSystem) {
            $this->parseUnitSystem($xmlUnitSystem);
        }
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

            $unitSystem->setTranslationLocale(Core_Locale::load($lang));
            $unitSystem->setName($value);
            $unitSystem->save();
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }
}
