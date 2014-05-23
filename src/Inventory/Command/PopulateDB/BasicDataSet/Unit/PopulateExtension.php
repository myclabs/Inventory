<?php

namespace Inventory\Command\PopulateDB\BasicDataSet\Unit;

use Doctrine\ORM\EntityManager;
use DOMDocument;
use DOMElement;
use DOMNode;
use Unit\Domain\UnitExtension;

/**
 * @author valentin.claras
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @author matthieu.napoli
 */
class PopulateExtension
{
    /**
     * @Inject
     * @var EntityManager
     */
    private $entityManager;

    public function run()
    {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/extension.xml");
        foreach ($xml->getElementsByTagName('extension') as $extension) {
            $this->parseExtension($extension);
        }

        $this->entityManager->flush();
    }

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

            $extension->getName()->set($name, $lang);
            $extension->getSymbol()->set($symbol, $lang);
        }
        $extension->save();
        $this->entityManager->flush();
    }
}
