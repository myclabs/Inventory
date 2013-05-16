<?php
/**
 * Fichier de la classe Excel.
 *
 * @author     valentin.claras
 * @author	   damien.corticchiato
 *
 * @package    Export
 */

/**
 * Une classe permettant de générer un document excel stylisé à partir de plusieurs tableau.
 *
 * @package Export
 */
class Export_Excel_Stylized extends Export_Excel
{
    /**
     * Tableau qui sera affiché dans le document.
     *
     * @var string
     */
    public $institute = null;

    /**
     * Tableau qui sera affiché dans le document.
     *
     * @var string
     */
    public $information = null;

    /**
     * Tableau qui sera affiché dans le document.
     *
     * @var string
     */
    public $result = null;

    /**
     * Tableau qui sera affiché dans le document.
     *
     * @var string
     */
    public $footer = array();


    /**
     * Remplit l'objet PHPExcel avec les données.
     *
     * @param PHPExcel $phpExcel
     */
    protected function fillData($phpExcel)
    {
        $sheet = $phpExcel->getActiveSheet();

        $this->fillInstitute($sheet);
        $this->fillFileTitle($sheet);
        $this->fillInformation($sheet);
        $this->fillResult($sheet);

        $y = 1;
        foreach ($this->footer as $lines) {
            $x = 1;
            foreach ($lines as $cell) {
                // Set the value of each cell.
                $sheet->getColumnDimension($this->convertColumnNumber($x))->setAutoSize(true);
                $coordinates = (string) $this->convertColumnNumber($x).$y;
                $sheet->setCellValue($coordinates, $cell);
                $x++;
            }
            $y++;
        }
    }

    /**
     * @param PHPExcel $sheet
     */
    protected function fillInstitute($sheet)
    {
        // Vérifie que la source du fichier excel a bien été spécifié.
        if ($this->institute === null) {
            // Erreur Système car empèche le bon fonctionnement.
            throw new Core_Exception_InvalidArgument(
                'Il faut spécifier un tableau "institute" pour générer un document Excel'
            );
        }

        if ($this->institute != null) {
            $y = 1;
            foreach ($this->institute as $lines) {
                $x = 1;
                foreach ($lines as $cell) {
                    // Set the value of each cell.
                    $sheet->getColumnDimension($this->convertColumnNumber($x))->setAutoSize(true);
                    $coordinates = (string) $this->convertColumnNumber($x).$y;
                    $sheet->setCellValue($coordinates, $cell);
                    // Set the font style.
                    $sheet->getStyle($coordinates)->getFont()->setName('Calibri');
                    $sheet->getStyle($coordinates)->getFont()->setSize(11);
                    $sheet->getStyle($coordinates)->getFont()->setBold(true);
                    $x++;
                }
                $y++;
            }
            // Add a blank line after this part.
            $sheet->getColumnDimension($this->convertColumnNumber($x))->setAutoSize(true);
            $coordinates = (string) $this->convertColumnNumber($x).$y;
            $sheet->setCellValue($coordinates, '');
            $y++;
        }
    }

    /**
     * @param PHPExcel $sheet
     */
    protected function fillFileTitle($sheet)
    {
        if ($this->fileTitle != null) {
            $y = 1;
            foreach ($this->fileTitle as $lines) {
                $x = 1;
                foreach ($lines as $cell) {
                    // Set the value of each cell.
                    $sheet->getColumnDimension($this->convertColumnNumber($x))->setAutoSize(true);
                    $coordinates = (string) $this->convertColumnNumber($x).$y;
                    $sheet->setCellValue($coordinates, $cell);
                    // Set the font style.
                    $sheet->getStyle($coordinates)->getFont()->setName('Calibri');
                    $sheet->getStyle($coordinates)->getFont()->setSize(16);
                    $sheet->getStyle($coordinates)->getFont()->setUnderline(true);
                    $x++;
                }
                $y++;
            }
            // Add a blank line after this part.
            $sheet->getColumnDimension($this->convertColumnNumber($x))->setAutoSize(true);
            $coordinates = (string) $this->convertColumnNumber($x).$y;
            $sheet->setCellValue($coordinates, '');
        }
    }

    /**
     * @param PHPExcel $sheet
     */
    protected function fillInformation($sheet)
    {
        // Vérifie que la source du fichier excel a bien été spécifié.
        if ($this->information === null) {
            // Erreur Système car empèche le bon fonctionnement.
            throw new Core_Exception_InvalidArgument(
                'Il faut spécifier un tableau "information" pour générer un document Excel'
            );
        }

        if ($this->information != null) {
            $y = 1;
            foreach ($this->information as $lines) {
                $x = 1;
                foreach ($lines as $cell) {
                    // Set the value of each cell.
                    $sheet->getColumnDimension($this->convertColumnNumber($x))->setAutoSize(true);
                    $coordinates = (string) $this->convertColumnNumber($x).$y;
                    $sheet->setCellValue($coordinates, $cell);
                    $x++;
                }
                $columnEnd = $x;
                $y++;
            }
            // Set the border style.
            $cellStart = 'A1';
            $cellEnd = (string) $this->convertColumnNumber($columnEnd - 1).($y - 1);
            $sheet->getStyle($cellStart.':'.$cellEnd)->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
            // Add a blank line after this part.
            $sheet->getColumnDimension($this->convertColumnNumber($x))->setAutoSize(true);
            $coordinates = (string) $this->convertColumnNumber($x).$y;
            $sheet->setCellValue($coordinates, '');
        }
    }

    /**
     * @param PHPExcel $sheet
     */
    protected function fillResult($sheet)
    {
        // Vérifie que la source du fichier excel a bien été spécifié.
        if ($this->result === null) {
            // Erreur Système car empèche le bon fonctionnement.
            throw new Core_Exception_InvalidArgument(
                'Il faut spécifier un tableau "result" pour générer un document Excel'
            );
        }

        if ($this->result != null) {
            $y = 1;
            foreach ($this->result as $lines) {
                $x = 1;
                foreach ($lines as $cell) {
                    // Set the value of each cell.
                    $sheet->getColumnDimension($this->convertColumnNumber($x))->setAutoSize(true);
                    $coordinates = (string) $this->convertColumnNumber($x).$y;
                    $sheet->setCellValue($coordinates, $cell);
                    $x++;
                }
                $columnEnd = $x;
                $y++;
            }
            // Set the border style.
            $cellStart = 'A1';
            $cellEnd = (string) $this->convertColumnNumber($columnEnd - 1).($y - 1);
            $sheet->getStyle($cellStart.':'.$cellEnd)->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
            $sheet->getStyle($cellStart.':'.$cellEnd)->getBorders()->getInside()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            // Add a blank line after this part.
            $sheet->getColumnDimension($this->convertColumnNumber($x))->setAutoSize(true);
            $coordinates = (string) $this->convertColumnNumber($x).$y;
            $sheet->setCellValue($coordinates, '');
        }
    }
}