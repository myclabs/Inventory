<?php
/**
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @package Unit
 *
 */
use Unit\Domain\Unit\DiscreteUnit;

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
        \Core\ContainerSingleton::getEntityManager()->flush();
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
        $discreteUnit = new DiscreteUnit();
        $discreteUnit->setRef($element->getAttribute('ref'));

        foreach ($element->getElementsByTagName('name')->item(0)->childNodes as $node) {
            /** @var $node DOMNode */
            $lang = trim($node->nodeName);
            $value = trim($node->nodeValue);
            if ($lang == '' || $value == '') {
                continue;
            }

            $discreteUnit->setTranslationLocale(Core_Locale::load($lang));
            $discreteUnit->setName($value);
            $discreteUnit->setSymbol($value);
            $discreteUnit->save();
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }

}
