<?php
/**
 * @author valentin.claras
 * @author cyril.perraud
 * @package DW
 * @subpackage Export
 */

/**
 * Classe permettant de gérer l'export global détaillé d'un cube au format excel
 * @package DW
 */
class DW_Export_Indicators extends Export_Excel
{
    /**
     * Constructeur.
     *
     * @param DW_Model_Cube $cube
     */
    public function __construct($cube)
    {
        parent::__construct(date('Y-m-d', time()) . '-' . Core_Tools::refactor($cube->getLabel()));

        $this->setData($cube);
    }

    /**
     * Remplit l'export des données.
     *
     * @param DW_Model_Cube $cube
     */
    protected function setData($cube)
    {
        $sheets = array();


        // Entête.
        $sheetData = array();
        $sheetData[] = array($cube->getLabel());
        $sheetData[] = array();
        $sheetData[] = array(__('DW', 'export', 'detailedCompleteExport'));
        $sheetData[] = array();

        $date = date(str_replace('&nbsp;', '', __('DW', 'export', 'dateFormat')));
        $sheetData[] = array(__('UI', 'name', 'date'). __('UI', 'other', ':') . $date);

        $sheets[__('DW', 'export', 'detailedCompleteExportFirstSheet')] = $sheetData;


        // Préparation des onglets.
        $sheetLabels = array();
        foreach ($cube->getIndicators() as $indicator) {
            $cleanLabel = $this->clean($indicator->getLabel());
            $sheetLabels[$indicator->getRef()] = $indicator->getPosition() . ' - '
                . substr($cleanLabel, 0, strrpos(substr($cleanLabel, 0, 30), ' '));

            $sheetHeader = array();
            foreach ($cube->getAxes() as $axis) {
                $sheetHeader[] = $axis->getLabel();
            }
            $sheetHeader[] = __('UI', 'name', 'value') . ' (' . $indicator->getUnit()->getSymbol() . ')';
            $sheetHeader[] = __('UI', 'name', 'uncertainty') . ' (%)';

            $sheets[$sheetLabels[$indicator->getRef()]] = array($sheetHeader);
        }


        // Ajout des résultats aux onglets.
        $queryCubeResults = new Core_Model_Query();
        $queryCubeResults->filter->addCondition(DW_Model_Result::QUERY_CUBE, $cube);
        foreach (DW_Model_Result::loadList($queryCubeResults) as $cubeResult) {
            $row = array();

            foreach ($cube->getAxes() as $axis) {
                $axisMember = $cubeResult->getMemberForAxis($axis);
                $row[] = $labelAxisMember = ($axisMember === null) ? '' : $axisMember->getLabel();
            }

//            $row[] = round(
//                $cubeResult->getValue()->digitalValue,
//                floor(3 - log10(abs($cubeResult->getValue()->digitalValue)))
//            );
            $row[] = $cubeResult->getValue()->digitalValue;
            $row[] = round($cubeResult->getValue()->relativeUncertainty);

            $sheets[$sheetLabels[$cubeResult->getIndicator()->getRef()]][] = $row;
        }

        $this->isMultiSheet = true;
        $this->body = $sheets;
    }

    /**
     * Remplit l'export des données en ajoutant le style aux contenus.
     *
     * @param DW_Model_Cube $cube
     */
    protected function setDataWithStyle($cube)
    {
        $thin = array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('argb' => '000000'),));


        $bold = array('font' => array('bold' => true),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));


        $sheets = array();


        // Entête.
        $sheetData = array();
        $sheetData[] = array(array($cube->getLabel(), array('font' => array('bold' => true, 'size' => 14))));
        $sheetData[] = array();
        $sheetData[] = array(__('DW', 'export', 'detailedCompleteExport'));
        $sheetData[] = array();

        $date = date(str_replace('&nbsp;', '', __('DW', 'export', 'dateFormat')), time());
        $sheetData[] = array(
            array(
                __('UI', 'name', 'date') . __('UI', 'other', ':') . $date,
                array('font' => array('italic' => true))
            )
        );

        $sheets[__('DW', 'export', 'detailedCompleteExportFirstSheet')] = $sheetData;


        // Préparation des onglets.
        $sheetLabels = array();
        foreach ($cube->getIndicators() as $indicator) {
            $cleanLabel = $this->clean($indicator->getLabel());
            $sheetLabels[$indicator->getRef()] = substr($cleanLabel, 0, strrpos(substr($cleanLabel, 0, 30), ' '));

            $sheetHeader = array();
            foreach ($cube->getAxes() as $axis) {
                $sheetHeader[] = array($axis->getLabel(), array_merge(array('borders' => $thin), $bold));
            }
            $sheetHeader[] = array(
                __('UI', 'name', 'value') . ' (' . $indicator->getUnit()->getSymbol() . ')',
                array_merge(array('borders' => $thin), $bold)
            );
            $sheetHeader[] = array(
                __('UI', 'name', 'uncertainty') . ' (%)',
                array_merge(array('borders' => $thin),$bold)
            );

            $sheets[$sheetLabels[$indicator->getRef()]] = array($sheetHeader);
        }

        // Ajout des résultats aux onglets.
        $queryCubeResults = new Core_Model_Query();
        $queryCubeResults->filter->addCondition(DW_Model_Result::QUERY_CUBE, $cube);
        foreach (DW_Model_Result::loadList($queryCubeResults) as $cubeResult) {
            $row = array();

            foreach ($cube->getAxes() as $axis) {
                $axisMember = $cubeResult->getMemberForAxis($axis);
                $row[] = array(
                    ($axisMember === null) ? '' : $axisMember->getLabel(),
                    array_merge(array('borders' => $thin), $bold)
                );
            }

            $row[] = array(
                round(
                    $cubeResult->getValue()->digitalValue,
                    floor(3 - log10(abs($cubeResult->getValue()->digitalValue)))
                ),
                array('borders' => $thin)
            );
            $row[] = array(
                round($cubeResult->getValue()->relativeUncertainty),
                array('borders' => $thin)
            );

            $sheets[$sheetLabels[$cubeResult->getIndicator()->getRef()]][] = $row;
        }

        $this->isMultiSheet = true;
        $this->body = $sheets;
    }

    /**
     * NEttoye la chaine de caractère
     * @param string $string
     */
    protected function clean($string)
    {
        $accent = array('é','è','ë','ê','à','ä','â','å','î','ï','ô','ö','ù','ü','û','ú','ý','ÿ',
        '…','!',"'",'"','#',':','?','/',';',',','ç','}','{','[',']','(',')','&','`','|','@','~',
        '²','°','^','°','º','–','’','À','Á','Â','Ã','Ä','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï',
        'Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý');

        $sans = array('e','e','e','e','a','a','a','a','i','i','o','o','u','u','u','u','y','y','-',
        '-','-','-',' ','-',' ',' ','-','-','c','-','-','-','-','-','-','-','-','-','-','-','-','-',
        '-','-','-','-','-','A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','O','O','O',
        'O','O','U','U','U','U','Y');

        return str_replace($accent, $sans, $string);
    }

}
