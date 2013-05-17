<?php

/**
 * @package Inventory
 */

/**
 * Classe permettant d'exporter des fichiers Xls
 * @author aurelien.estour
 * @package Inventory
 */
class Inventory_ExportXls
{
    /**
     * @var string $filename
     *  Recupere le filename de type "-classe"
     *  exemple : "-granularity" "-structure"
     */
    protected $filename;

    /**
     * Function setFilename
     *  assigne un nom de fichier
     *  @param string $filename
     */
    public function setFileName($filename)
    {
        if ($filename == null) {
            throw new Core_Exception_UndefinedAttribute(
                    'On ne peux pas saisir un nom de fichier vide'
            );
        }
        $this->_filename = $filename;
    }

    /**
     * Function getFilename
     *  recupere le nom du fichier Xls
     * @throws Core_Exception_UndefinedAttribute
     */
    public function getFilename()
    {
        if ($this->_filename == null) {
            throw new Core_Exception_UndefinedAttribute(
                    'Le nom de fichier n\'existe pas'
            );
        }
        return $this->_filename;
    }

    /**
     * Function Render
     *  Genere le Xls
     *  @param orga_model_cell $orgaCell
     *  @param array $sheets
     */
    public function RenderXls($orgaCell, $sheets)
    {

        // generation fichier
        $filename = 'Export';
        $filename = $filename . $orgaCell->getLabelCourt();
        $filename = $filename . $this->getFilename();

        // création du fichier
        $xls = new Export_Excel($filename);
        $xls->extension = Export_Excel::XLS;
        $xls->isMultiSheet = true;

        // chargement xls
        $xls->array = $sheets;
        $xls->render();
    }


    /**
     *  Fonction d'exportation des saisies
     *  @param orga_model_cell $orgaCell
     */

    public function ExportSaisieForm($orgaCell)
    {
        // @todo ? peut être enlever la suppression de limitation... ?
        set_time_limit(85);
        ini_set('memory_limit', '1024M');

        // Requête cell
        $queryCell = new Core_Model_Query();
        $queryCell->filter->addCondition(Orga_Model_Cell::FILTER_ALLPARENTSRELEVANT, true,
                                         Core_Model_Filter::OPERATOR_EQUAL);
        $queryCell->filter->addCondition(Orga_Model_Cell::FILTER_IDRELEVANT, true,
                                         Core_Model_Filter::OPERATOR_EQUAL);
        // Requête project
        $queryProject = new Core_Model_Query();
        $queryProject->filter->addCondition(Inventory_Model_AFGranularities::FILTER_IDPROJECT, true,
                                            Core_Model_Filter::OPERATOR_EQUAL);

        $inputGranues = Inventory_Model_AFGranularities::loadList($queryProject);
        // Tableau de tableau contenant les données à mettre pour chaque onglet du document xsl
        $sheets = array();

        // Style du document xsl
        $alignRight = array('alignment' => array(
                			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
        ));
        $alignLeft  = array('alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        ));
        $alignCenter = array('alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));

        $bold  = array('font' => array('bold' => true, 'size' => 11, $alignCenter));
        $style = array();
        $style[] = $bold;

        // pour chaque Granularité, donc pour chaque onglet du document xsl
        foreach ($inputGranues as $inputGranu) {

            $genericDataCells = Orga_DatagridConfiguration::getDatagridGenericData($queryCell, $orgaCell->getKey(),
                                                                                   $inputGranu->getAFInputGranularity()
                                                                                              ->getKey()
                                                                                   );
            // Contenu d'un onglet
            $sheet = array();
            // Permet d'afficher qu'une fois les entêtes
            $firstForHeader = true;

            // Pour chaque Cellule
            foreach ($genericDataCells as $dataCell) {

                $cell         = Orga_Model_Cell::load($dataCell['index']);
                $granularity  = $cell->getGranularity();  	// ER : Autrement dit il s'agit de $inputGranu ! :-)
                $axesList     = $granularity->getAxes();	// ER : Il suffirait de récupérer la liste d'axes de $inputGranu une fois pour toutes, et pas pour chaque cellule ! :-)
                $memberList   = $cell->getMembers();

                // ---------------- AXES ------------------------------------------
				// ER : moche, il faudrait générer ce tableau une seule fois en dehors de la boucle. 
                $refAxisList      = array();
                foreach ($axesList as $axis) {
                    $refAxisList[] = array($axis->getRef(), $style);
                }

                // ---------------- HEADER -----------------------------------------
                // à afficher qu'une fois
				// ER : moche, il faudrait générer le Header en dehors de la boucle sur les cellules
                if ($firstForHeader) {
                    $header         = array_merge($refAxisList,
                                                  AF_Model_Input_PrimarySet::getSpreadsheetExportHeader($style));
                    $sheet[]        = $header;
                    $firstForHeader = false; // ER : moche (inutile si on générait le header en dehors de la boucle sur les cellules)
                }
                // ---------------- MEMBRES -----------------------------------------
                $refMemberList = array();
                $index = null;	// moche de mettre ça ici, ça devrait figurer dans la boucle suivante ? sinon pas remis à null après chaque étape ? 
                foreach ($memberList as $member) {
                    // On trie le tableau de membres pour être sur qu'ils s'associent bien avec le bon axe
                    foreach ($refAxisList as $key => $refAxis) {
                        if (current($refAxis) === $member->getAxis()->getRef()) { // ER : "current" = moche, ici on veut juste la première entrée non ? 
                            $index = $key;
                        }
                    }
                    if (isset($index)) {
                        $refMemberList[$index] = $member->getRef();
                    }
                }

                // ---------------- SAISIES -----------------------------------------
                $cellDataProvider = Inventory_Model_CellDataProvider::loadByIdOrgaCell($cell->getKey());
                $afPrimarySet     = current($cellDataProvider->getAFPrimarySets());
                if ($afPrimarySet !== false) {
                    // On récupère le tableau des saisies
                    $saisies  = $afPrimarySet->getSpreadsheetExportContent();
                    // Pour chaque saisie on ajoute les membres
                    foreach ($saisies as $saisie) {
                        $sheet[] = array_merge($refMemberList, $saisie);
                    }
                }
            }
            $sheets[$granularity->getRef()] = $sheet;
        }
        $this->RenderXls($orgaCell, $sheets);
    }


    /**
     * Function ExportXls
     *  fonction général d'export
     *  @param orga_model_cell $orgaCell
     */
    public function ExportSaisieXls($orgaCell)
    {
        $this->ExportSaisieForm($orgaCell);
    }
}
