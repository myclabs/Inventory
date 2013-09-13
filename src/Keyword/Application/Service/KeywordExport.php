<?php
/**
 * Classe KeywordExport
 * @author valentin.claras
 * @package    Keyword
 * @subpackage Service
 */

namespace Keyword\Application\Service;

use Keyword\Domain\Association;
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;
use Xport\Spreadsheet\Builder\SpreadsheetModelBuilder;
use Xport\Spreadsheet\Exporter\PHPExcelExporter;
use Xport\MappingReader\YamlMappingReader;

/**
 * Service Keyword.
 * @package    Keyword
 * @subpackage Service
 */
class KeywordExport
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
        $queryPredicateLabel = new \Core_Model_Query();
        $queryPredicateLabel->order->addOrder(Predicate::QUERY_LABEL);
        $modelBuilder->bind('predicates', Predicate::loadList($queryPredicateLabel));
        $modelBuilder->bind('predicatesSheetLabel', __('Keyword', 'predicate', 'predicates'));
        $modelBuilder->bind('predicateColumnDirectLabel', __('Keyword', 'predicate', 'directPredicateLabelHeader'));
        $modelBuilder->bind('predicateColumnDirectRef', __('Keyword', 'predicate', 'directPredicateIdentifierHeader'));
        $modelBuilder->bind('predicateColumnReverseLabel', __('Keyword', 'predicate', 'reversePredicateLabelHeader'));
        $modelBuilder->bind('predicateColumnReverseRef', __('Keyword', 'predicate', 'reversePredicateIdentifierHeader'));
        $modelBuilder->bind('predicateColumnDescription', __('UI', 'name', 'description'));

        // Keywords
        $queryKeywordLabel = new \Core_Model_Query();
        $queryKeywordLabel->order->addOrder(Keyword::QUERY_LABEL);
        $modelBuilder->bind('keywords', Keyword::loadList($queryKeywordLabel));
        $modelBuilder->bind('keywordsSheetLabel', __('Keyword', 'name', 'keywords'));
        $modelBuilder->bind('keywordColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('keywordColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bind('keywordColumnAssociationsAsSubject', __('Keyword', 'export', 'keywordColumnAssociationsAsSubject'));
        $modelBuilder->bind('keywordColumnAssociationsAsObject', __('Keyword', 'export', 'keywordColumnAssociationsAsObject'));

        // Associations
        $queryAssociationLabels = new \Core_Model_Query();
        $queryAssociationLabels->order->addOrder(
            Keyword::QUERY_LABEL,
            \Core_Model_Order::ORDER_ASC,
            Keyword::getAliasAsSubject()
        );
        $queryAssociationLabels->order->addOrder(
            Keyword::QUERY_LABEL,
            \Core_Model_Order::ORDER_ASC,
            Keyword::getAliasAsObject()
        );
        $modelBuilder->bind('associations', Association::loadList($queryAssociationLabels));
        $modelBuilder->bind('associationsSheetLabel', __('Keyword', 'relation', 'pageTitle'));
        $modelBuilder->bind('associationColumnSubject', __('Keyword', 'name', 'subject'));
        $modelBuilder->bind('associationColumnPredicate', __('Keyword', 'predicate', 'predicate'));
        $modelBuilder->bind('associationColumnObject', __('Keyword', 'name', 'object'));


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