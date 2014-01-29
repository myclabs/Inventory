<?php
use AF\Domain\Algorithm\Numeric\NumericAlgo;
use AF\Domain\Algorithm\Selection\MainSelectionAlgo;

/**
 * Supprime les indexations d'algos inutiles
 */

define('RUN', false);

require_once __DIR__ . '/../../application/init.php';

/** @var MainSelectionAlgo[] $mainAlgos */
$mainAlgos = MainSelectionAlgo::loadList();

foreach ($mainAlgos as $mainAlgo) {
    try {
        $algosInMain = $mainAlgo->getSubAlgos();
    } catch (Core_Exception_NotFound $e) {
        $algosInMain = [];
    }

    foreach ($mainAlgo->getSet()->getAlgos() as $algo) {
        if ($algo instanceof NumericAlgo) {
            if ($algo->isIndexed() && !in_array($algo, $algosInMain, true)) {
                echo "Correction de l'algo " . $algo->getRef() . PHP_EOL;
                $algo->setContextIndicator(null);
                $algo->clearIndexes();
            }
        }
    }
}

echo "Flush" . PHP_EOL;

\Core\ContainerSingleton::getEntityManager()->flush();
