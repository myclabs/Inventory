<?php
/**
 * Classe Keyword_Service_Export
 * @author valentin.claras
 * @package    Keyword
 * @subpackage Service
 */

use Xport\Spreadsheet\Builder\SpreadsheetModelBuilder;
use Xport\Spreadsheet\Exporter\PHPExcelExporter;
use Xport\MappingReader\YamlMappingReader;

/**
 * Service Keyword.
 * @package    Keyword
 * @subpackage Service
 */
class Keyword_Service_Export
{
    /**
     * Exporte la version de Keyword.
     *
     * @param string $format
     */
    public function stream($format='xlsx')
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        // Predicates
        $queryPredicateLabel = new Core_Model_Query();
        $queryPredicateLabel->order->addOrder(Keyword_Model_Predicate::QUERY_LABEL);
        $modelBuilder->bind('predicates', Keyword_Model_Predicate::loadList($queryPredicateLabel));
        $modelBuilder->bind('predicatesSheetLabel', __('Keyword', 'exports', 'predicatesSheetLabel'));
        $modelBuilder->bind('predicateColumnDirectLabel', __('Keyword', 'exports', 'predicateColumnDirectLabel'));
        $modelBuilder->bind('predicateColumnDirectRef', __('Keyword', 'exports', 'predicateColumnDirectRef'));
        $modelBuilder->bind('predicateColumnReverseLabel', __('Keyword', 'exports', 'predicateColumnReverseLabel'));
        $modelBuilder->bind('predicateColumnReverseRef', __('Keyword', 'exports', 'predicateColumnReverseRef'));
        $modelBuilder->bind('predicateColumnDescription', __('Keyword', 'exports', 'predicateColumnDescription'));

        // Keywords
        $queryKeywordLabel = new Core_Model_Query();
        $queryKeywordLabel->order->addOrder(Keyword_Model_Keyword::QUERY_LABEL);
        $modelBuilder->bind('keywords', Keyword_Model_Keyword::loadList($queryKeywordLabel));
        $modelBuilder->bind('keywordsSheetLabel', __('Keyword', 'exports', 'keywordsSheetLabel'));
        $modelBuilder->bind('keywordColumnLabel', __('Keyword', 'exports', 'keywordColumnLabel'));
        $modelBuilder->bind('keywordColumnRef', __('Keyword', 'exports', 'keywordColumnRef'));
        $modelBuilder->bind('keywordColumnAssociationsAsSubject', __('Keyword', 'exports', 'keywordColumnAssociationsAsSubject'));
        $modelBuilder->bind('keywordColumnAssociationsAsObject', __('Keyword', 'exports', 'keywordColumnAssociationsAsObject'));

        // Associations
        $queryAssociationLabels = new Core_Model_Query();
        $queryAssociationLabels->order->addOrder(
            Keyword_Model_Keyword::QUERY_LABEL,
            Core_Model_Order::ORDER_ASC,
            Keyword_Model_Keyword::getAliasAsSubject()
        );
        $queryAssociationLabels->order->addOrder(
            Keyword_Model_Keyword::QUERY_LABEL,
            Core_Model_Order::ORDER_ASC,
            Keyword_Model_Keyword::getAliasAsObject()
        );
        $modelBuilder->bind('associations', Keyword_Model_Association::loadList($queryAssociationLabels));
        $modelBuilder->bind('associationsSheetLabel', __('Keyword', 'exports', 'associationsSheetLabel'));
        $modelBuilder->bind('associationColumnSubject', __('Keyword', 'exports', 'associationColumnSubject'));
        $modelBuilder->bind('associationColumnPredicate', __('Keyword', 'exports', 'associationColumnPredicate'));
        $modelBuilder->bind('associationColumnObject', __('Keyword', 'exports', 'associationColumnObject'));


        switch ($format) {
            case 'xls':
                $writer = new \PHPExcel_Writer_Excel5();
                break;
            case 'xlsx':
            default:
                $writer = new \PHPExcel_Writer_Excel2007();
                break;
        }

        $export->export(
            $modelBuilder->build(new YamlMappingReader(__DIR__.'/export.yml')),
            'php://output',
            $writer
        );
    }

}