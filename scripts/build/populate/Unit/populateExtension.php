<?php
/**
 * @author valentin.claras
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @package Unit
 */
use Unit\Domain\UnitExtension;

/**
 * Script de création de la table Extension
 * @package Unit
 */
class Unit_Script_Populate_Extension
{
    /**
     * Lance la génération
     */
    public function run()
    {
        $this->generateExtension();
        \Core\ContainerSingleton::getEntityManager()->flush();
    }

    /**
     * Génère les Extension  à partir des fichiers xml
     */
    protected function generateExtension()
    {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/extension.xml");
        foreach ($xml->getElementsByTagName('extension') as $extension) {
            $this->parseExtension($extension);
        }
    }

    /**
     * Parcours le fichier xml des Extensions
     * @param DOMElement $element
     */
    protected function parseExtension(DOMElement $element)
    {
        $extension = new UnitExtension();
        $extension->setRef($element->getAttribute('ref'));
        $extension->setMultiplier($element->getElementsByTagName('multiplier')->item(0)->firstChild->nodeValue);

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

            $extension->setTranslationLocale(Core_Locale::load($lang));
            $extension->setName($name);
            $extension->setSymbol($symbol);
            $extension->save();
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }
}
