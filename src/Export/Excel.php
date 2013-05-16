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
 * Une classe permettant de générer un document excel à partir d'un tableau.
 *
 * @package Export
 */
class Export_Excel
{
    /*
     *  Constantes
     */
    CONST EXTENSION_XLS = 'xls';
    CONST EXTENSION_XLSX = 'xlsx';

    /**
     * Nom du fichier généré à la sortie.
     *
     * @var   string
     */
    public $fileName = null;

    /**
     * Extension du document
     *  Par défaut : XLSX.
     *
     * @see EXTENSION_XLS
     * @see EXTENSION_XLSX
     *
     * @var string
     */
    public $extension = self::EXTENSION_XLS;

    /**
     * Auteur du document.
     *  Par défaut : MyC-sense.
     *
     * @var   string
     */
    public $author = 'MyC-sense';

    /**
     * Titre du document.
     *  Par defaut null.
     *
     * @var   string
     */
    public $fileTitle = null;

    /**
     * Sujet du document.
     *  Par defaut null.
     *
     * @var   string
     */
    public $subject = null;

    /**
     * Description du document.
     *  Par defaut null.
     *
     * @var   string
     */
    public $description = null;

    /**
     * Tableau des mots-clefs du document.
     *  Par défaut tableau vide.
     *
     * @var   array
     */
    public $keywords = array();

    /**
     * Instance de l'objet PhpExcel.
     *
     * @var PHPExcel
     */
    public $objPHPExcel = null;

    /**
     * Tableau qui sera affiché dans le document.
     *
     * @var   string
     */
    public $body;

    /**
     * Tableau des styles qui seront affectés aux cellules.
     *
     * @var   string
     */
    protected $styles = array();

    /**
     * Booléen définissant si l'export comportera ou non plusieurs onglet.
     *  Par défaut non.
     *
     * @var bool
     */
    public $isMultiSheet = false;


    /**
     * Constructeur de la classe Excel.
     *
     * @param string $fileName Nom du fichier généré.
     *
     */
    public function  __construct($fileName = null)
    {
        $this->fileName = $fileName;
    }

    /**
     * Convertion d'un nombre en lettre de colonne.
     *
     * @param int $number Mobre à convertir.
     * @return string
     *
     */
    public function convertColumnNumber($number)
    {
        return chr(64 + ($number % 26));
    }

    /**
     * Definie le style d'une cellule ou groupe de cellule.
     *
     * @param string $coordinate
     * @param array() $styles
     * @param int $sheet
     */
    public function setStyleForCoordinate($coordinate, $styles, $sheet=0)
    {
        if ((!isset($this->styles[$sheet]))) {
            $this->styles[$sheet] = array();
        }
        $this->styles[$sheet][$coordinate] = $styles;
    }

    /**
     * Génère l'objet DOMPDF.
     *
     * @return objPHPExcel
     */
    protected function getPhpExcelObject()
    {
        // Vérifie que le nom du fichier a bien été spécifié.
        if ($this->fileName === null) {
            // Erreur Système car empèche le bon fonctionnement.
            throw new Core_Exception_InvalidArgument(
                'Il faut spécifier un nom de fichier pour le document à générer'
            );
        }
        
        // Création de la feuille Excel.
        $phpExcel = new PHPExcel();
        $phpExcel->getProperties()->setCreator($this->author);
        $phpExcel->getProperties()->setLastModifiedBy($this->author);
        if ($this->fileTitle !== null) {
            $phpExcel->getProperties()->setTitle($this->fileTitle);
        }
        if ($this->subject !== null) {
            $phpExcel->getProperties()->setSubject($this->subject);
        }
        if ($this->description !== null) {
            $phpExcel->getProperties()->setDescription($this->description);
        }
        if (count($this->keywords) > 0) {
            $phpExcel->getProperties()->setKeywords(implode($this->keywords, ' '));
        }

        $this->fillData($phpExcel);

        // Styles.
        foreach ($this->styles as $sheetIndex => $coordinatesArray) {
            foreach ($coordinatesArray as $coordinates => $stylesArray) {
                $phpExcel->getSheet($sheetIndex)->getStyle($coordinates)->applyFromArray($stylesArray);
            }
        }

        $phpExcel->setActiveSheetIndex(0);
        return $phpExcel;
    }

    /**
     * Remplit l'objet PHPExcel avec les données.
     *
     * @param PHPExcel $phpExcel
     */
    protected function fillData($phpExcel)
    {
        // Vérifie que la source du fichier excel a bien été spécifié.
        if ($this->body === null) {
            // Erreur Système car empèche le bon fonctionnement.
            throw new Core_Exception_InvalidArgument(
                'Il faut spécifier un tableau pour générer un document Excel'
            );
        }

        // Data.
        if ($this->isMultiSheet) {
            $sheetIndex = 0;
            foreach ($this->body as $title => $data) {
                if ($sheetIndex > 0) {
                    $sheet = $phpExcel->createSheet($sheetIndex);
                } else {
                    $sheet = $phpExcel->getSheet($sheetIndex);
                }
                $sheet->setTitle($title);
                $this->fillSheetWithData($sheet, $data, $sheetIndex);
                $sheetIndex++;
            }
        } else {
            $sheet = $phpExcel->getActiveSheet();
            $this->fillSheetWithData($sheet, $this->body);
        }
    }

    /**
     * Remplit une feuille données.
     *
     * @param  $sheet
     * @param $data
     */
    protected function fillSheetWithData($sheet, $data, $sheetPosition=0)
    {
        // Passage des données.
        $y = 1;
        foreach ($data as $lines) {
            $x = 1;
            foreach ($lines as $cell) {
                $sheet->getColumnDimension($this->convertColumnNumber($x))->setAutoSize(true);
                $coordinates = (string) $this->convertColumnNumber($x).$y;
                if (is_array($cell)) {
                    $sheet->setCellValue($coordinates, $cell[0]);
                    $this->setStyleForCoordinate($coordinates.':'.$coordinates, $cell[1], $sheetPosition);
                } else {
                    $sheet->setCellValue($coordinates, $cell);
                }
                $x++;
            }
            $y++;
        }
    }

    /**
     * Renvoie le writer correspondant au document.
     *
     * @return PHPExcel_Writer_Excel2007|PHPExcel_Writer_Excel5
     */
    protected function getWriter()
    {
        $phpExcel = $this->getPhpExcelObject();
        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        if ($this->extension == self::EXTENSION_XLS) {
            $writer = new PHPExcel_Writer_Excel5($phpExcel);
        } else {
            $writer = new PHPExcel_Writer_Excel2007($phpExcel);
        }

        return $writer;
    }

    /**
     * Renvoie le writer PHPPxcel.
     *
     * @return PHPExcel_Writer_Excel2007|PHPExcel_Writer_Excel5
     */
    public function render()
    {
        return $this->getWriter();
    }

    /**
     * Stream le fichier pdf.
     *
     * @return string
     */
    public function display()
    {
        if ($this->extension == self::EXTENSION_XLS) {
            $contentType = "Content-type: application/vnd.ms-excel";
        } else {
            $contentType = "Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        }
        header($contentType);
        header('Content-Disposition:attachement;filename='.$this->fileName.'.'.$this->extension);
        header('Cache-Control: max-age=0');

        // Affichage, proposition de télécharger sous le nom donné.
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $this->getWriter()->save('php://output');
    }
}