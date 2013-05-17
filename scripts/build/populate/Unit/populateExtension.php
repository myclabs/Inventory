<?php
/**
 * @author valentin.claras
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @package Unit
 */

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
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['unit']->flush();
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
        $extension = new Unit_Model_Unit_Extension();
        $extension->setRef($element->getAttribute('ref'));
        $extension->setName($element->getElementsByTagName('name')->item(0)->firstChild->nodeValue);
        $extension->setSymbol($element->getElementsByTagName('symbol')->item(0)->firstChild->nodeValue);
        $extension->setMultiplier($element->getElementsByTagName('multiplier')->item(0)->firstChild->nodeValue);
        $extension->save();
    }
}