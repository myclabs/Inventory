<?php

/**
 * @package Inventory
 */

/**
 * Classe permettant d'importer des fichiers Xls
 * @author aurelien.estour
 * @package Inventory
 */

class Inventory_ImportXls
{
    /**
     *  Chemin
     */
    protected $_path;

    /**
     * Construcuteur
     * @param string $path
     */
    public function __construct($path = null)
    {
        if ($path == null) {
            throw new Core_Exception_UndefinedAttribute(
                'Impossible d\'importer les données sans avoir spécifié le chemin'
            );
        }
        $this->_path = $path;
    }

    /**
     *  Function loadFile ($xlsPath)
     *   charge le chemin du fichier et genere le doc
     *   @param string $xlsPath
     */
     public function loadFile($xlsPath)
     {
         $objReader = PHPExcel_IOFactory::createReaderForFile($xlsPath);
         // $objReader->setReadDataOnly(true);
         $excel = $objReader->load($xlsPath);
         return $excel;
     }

     /**
      *  Function ImportAndSaveObject ($xlsFile fichier Excel)
      *   Importe les données du fichier excel et les sauvegardes dans la base.
      *   @param string $idCell
      */
     public function ImportAndSaveObject($idCell)
     {
         $ambiantCell = Orga_Model_Cell::load($idCell);
         $excel       = $this->loadFile($this->_path);
         $sheets      = $excel->getAllSheets();
         $index       = 0;

         // Pour chaque granularité/worksheet
         foreach ($sheets as $currentSheet) {

             $excel->setActiveSheetIndex($index);
             $arraytest = array();

             // Initialisation
             $titleSheet = $currentSheet->getTitle();
             $structure  = Orga_Model_Structure::load(1);
             $granu      = Orga_Model_Granularity::loadByRefAndIdStructure($titleSheet, $structure->getKey());
             $axes       = $granu->getAxes();

             // Parametrage
             $cptaxes    = count($axes);
             $column     = $cptaxes;
             $colSform   = $cptaxes + 2;
             $afForm     = $cptaxes + 3;
             $tempcolumn = $column;

             $maxcolumn          = $column + 10;
             $row                = 2;
             $continue           = true;
             $tableBygranularity = array();

             // boucle par ligne
             while ($continue) {

                 $tempcolumn = 0;
                 $sendRow    = array();

                 //  I - Recherche de la cellule
                 $cptMember = 1;
                 $idMembers = array();

                 // I-1 Récuperation de tout les membres de la ligne
                 $members = array();

                 for ($cptMember; $cptMember <= $cptaxes; $cptMember++ ) {

                    $memberRef = $excel->getActiveSheet()->getCellByColumnAndRow($tempcolumn, $row)->getValue();
                    $axisRef   = $excel->getActiveSheet()->getCellByColumnAndRow($tempcolumn, 1)->getValue();

                    $axisbase = Orga_Model_Axis::loadByRefAndIdStructure($axisRef, 1);
                    $member = Orga_Model_Member::loadByRefAndIdAxis($memberRef, $axisbase->getKey());

                    $members[] = $member;
                    $tempcolumn++;
                 }

                // I-2 Récuperation de la cellule par les membres
                $cell = Orga_Model_Cell::getCellByMembers($members);

                // Traitement uniquement si il y a une cellule
                 if ($cell != null) {

                     // II-1 recuperation du primaryset
                     $celldataprovider = Inventory_Model_CellDataProvider::loadByIdOrgaCell($cell->getKey());
                     $primarySets = $celldataprovider->getAFPrimarySets();
                     $primarySet = current($primarySets);

                     // II-2 Pas de primarySet => Création du PrimarySet
                     if ($primarySet == null) {
                        // récuperation de l'af de la ligne necessaire à la création du primary
                         $formLabelRow = $excel->getActiveSheet()->getCellByColumnAndRow($colSform,$row)->getValue();
                         $refForm = preg_replace('# ?> ?\w*#', '', $formLabelRow);
                         $af = AF_Model_AF::loadByRef($refForm);
                         $primarySet = new AF_Model_Input_PrimarySet();
                         $primarySet->setAf($af);
                         $primarySet->save();
                     }
                     // Passage des paramètres observables
                     $observationParameters = array('providerClassName' => get_class($celldataprovider),
                                                    'providerClassKey'  => $celldataprovider->getKey()
                                                    );
                     $primarySet->setObservationParameters($observationParameters);
                     $primarySet->save();

                     $tempcolumn = $column;
                     // boucle par colonne

                     while ($tempcolumn <= $maxcolumn) {

                         $valueCurrentCellXls = $excel->getActiveSheet()->getCellByColumnAndRow($tempcolumn,$row)->getValue();

                         if ($valueCurrentCellXls == '') {
                             $valueCurrentCellXls = null;
                         }
                         $sendRow[$primarySet->getKey()][] = $valueCurrentCellXls;
                         $tempcolumn++;
                     }
                 }

                 $tempcolumn = $column;
                 $row++;
                 $arraytest[] = $sendRow;

                 // on regarde si la ligne est remplie
                 $tempo = $excel->getActiveSheet()->getCellByColumnAndRow($tempcolumn, $row)->getValue();
                 if ($tempo == '') {
                     $continue = false;
                     $primarySet->setSpreadsheetImportContent($arraytest);
                 }

              }
          $index++;
          }

      }
}
