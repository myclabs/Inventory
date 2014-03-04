<?php

namespace Inventory\Command\PopulateDB\BasicDataSet\Unit;

use Core_Locale;
use Doctrine\ORM\EntityManager;
use DOMDocument;
use DOMElement;
use DOMNode;
use Unit\Domain\Unit\DiscreteUnit;

/**
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @author matthieu.napoli
 */
class PopulateDiscreteUnit
{
    /**
     * @Inject
     * @var EntityManager
     */
    private $entityManager;

    public function run()
    {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . "/data/discreteUnit.xml");
        foreach ($xml->getElementsByTagName('discreteUnit') as $discreteUnit) {
            $this->parseDiscreteUnit($discreteUnit);
        }

        $this->entityManager->flush();
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
            $this->entityManager->flush();
        }
    }
}
