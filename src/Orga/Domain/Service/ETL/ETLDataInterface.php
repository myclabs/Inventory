<?php
namespace Orga\Domain\Service\ETL;

use Orga\Domain\Cell;

/**
 * Classe permettant de peupler DW.
 *
 * @author valentin.claras
 */
interface ETLDataInterface
{
    /**
     * Supprime l'ensemble des résultats de la Cell donnée.
     *
     * @param Cell $cell
     */
    public function clearDWResultsFromCell(Cell $cell);

    /**
     * Peuple les cubes de DW alimentés par et avec les résultats de la Cell donnée.
     *
     * @param Cell $cell
     */
    public function populateDWResultsFromCell(Cell $cell);

    /**
     * Supprime l'ensemble des résultats du Cube de DW de la Cell donnée.
     *
     * @param Cell $cell
     */
    public function clearDWResultsForCell(Cell $cell);

    /**
     * Peuple le cube de DW de la Cell donnée avec les résultats de l'ensemble des inputs enfants.
     *
     * @param Cell $cell
     */
    public function populateDWResultsForCell(Cell $cell);

    /**
     * Peuple le cube de DW de la Cell donnée avec les résultats de l'ensemble des inputs enfants.
     *
     * @param Cell $cell
     */
    public function calculateResultsForCellAndChildren(Cell $cell);
}
