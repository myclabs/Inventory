<?php
/**
 * Classe KeywordExport
 * @author valentin.claras
 * @package    Keyword
 * @subpackage Service
 */

namespace Keyword\Application\Service;

use Keyword\Domain\PredicateRepository;
use Keyword\Domain\KeywordRepository;
use Keyword\Domain\PredicateCriteria;
use Keyword\Domain\KeywordCriteria;
use Keyword\Domain\AssociationCriteria;
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
     * @var PredicateRepository
     */
    protected $predicateRepository;
    /**
     * @var KeywordRepository
     */
    protected $keywordRepository;

    /**
     * @param PredicateRepository $predicateRepository
     * @param KeywordRepository $keywordRepository
     */
    public function __construct(PredicateRepository $predicateRepository, KeywordRepository $keywordRepository)
    {
        $this->predicateRepository = $predicateRepository;
        $this->keywordRepository = $keywordRepository;
    }

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
        $predicateCriteria = new PredicateCriteria();
        $predicateCriteria->orderBy([$predicateCriteria->label->getField() => PredicateCriteria::ASC]);
        $modelBuilder->bind('predicates', $this->predicateRepository->matching($predicateCriteria));
        $modelBuilder->bind('predicatesSheetLabel', __('Keyword', 'predicate', 'predicates'));
        $modelBuilder->bind('predicateColumnDirectLabel', __('Keyword', 'predicate', 'directPredicateLabelHeader'));
        $modelBuilder->bind('predicateColumnDirectRef', __('Keyword', 'predicate', 'directPredicateIdentifierHeader'));
        $modelBuilder->bind('predicateColumnReverseLabel', __('Keyword', 'predicate', 'reversePredicateLabelHeader'));
        $modelBuilder->bind('predicateColumnReverseRef', __('Keyword', 'predicate', 'reversePredicateIdentifierHeader'));
        $modelBuilder->bind('predicateColumnDescription', __('UI', 'name', 'description'));

        // Keywords
        $keywordCriteria = new KeywordCriteria();
        $keywordCriteria->orderBy([$keywordCriteria->label->getField() => KeywordCriteria::ASC]);
        $modelBuilder->bind('keywords', $this->keywordRepository->matching($keywordCriteria));
        $modelBuilder->bind('keywordsSheetLabel', __('Keyword', 'name', 'keywords'));
        $modelBuilder->bind('keywordColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('keywordColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bind('keywordColumnAssociationsAsSubject', __('Keyword', 'export', 'keywordColumnAssociationsAsSubject'));
        $modelBuilder->bind('keywordColumnAssociationsAsObject', __('Keyword', 'export', 'keywordColumnAssociationsAsObject'));

        // Associations
        $associationCriteria = new AssociationCriteria();
        $associationCriteria->orderBy([
                $associationCriteria->subjectLabel->getField() => AssociationCriteria::ASC,
                $associationCriteria->objectLabel->getField() => AssociationCriteria::ASC
            ]);
        $modelBuilder->bind('associations', $this->keywordRepository->associationsMatching($associationCriteria));
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