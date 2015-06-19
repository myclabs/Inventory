<?php

namespace Tests\DW;

use Core\Translation\TranslatedString;
use Doctrine\ORM\EntityManager;
use DW\Application\Service\ReportService;
use DW\Domain\Axis;
use DW\Domain\Indicator;
use DW\Domain\Filter;
use DW\Domain\Member;
use DW\Domain\Report;
use DW\Domain\Cube;
use Core\Test\TestCase;
use Unit\UnitAPI;

/**
 * @covers \Classification\Domain\Report
 */
class ReportServiceTest extends TestCase
{
    /**
     * @Inject
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @Inject
     * @var ReportService
     */
    protected $reportService;
    /**
     * @var Cube
     */
    protected $cube1;
    /**
     * @var Cube
     */
    protected $cube2;
    /**
     * @var Report
     */
    protected $report;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();


        $this->cube1 = new Cube();
        
        $indicator1 = new Indicator($this->cube1);
        $indicator1->setRef('indicator1');
        $indicator1->setUnit(new UnitAPI('m'));
        $indicator1->setRatioUnit(new UnitAPI('m'));
        $indicator2 = new Indicator($this->cube1);
        $indicator2->setRef('indicator2');
        $indicator2->setUnit(new UnitAPI('m'));
        $indicator2->setRatioUnit(new UnitAPI('m'));
        $indicator3 = new Indicator($this->cube1);
        $indicator3->setRef('indicator3');
        $indicator3->setUnit(new UnitAPI('m'));
        $indicator3->setRatioUnit(new UnitAPI('m'));

        $axis1 = new Axis($this->cube1);
        $axis1->setRef('axis1');
        $member1A = new Member($axis1);
        $member1A->setRef('member1A');
        $member1B = new Member($axis1);
        $member1B->setRef('member1B');
        $axis2 = new Axis($this->cube1);
        $axis2->setRef('axis2');
        $member2A = new Member($axis2);
        $member2A->setRef('member2A');
        $member2B = new Member($axis2);
        $member2B->setRef('member2B');
        $axis3 = new Axis($this->cube1);
        $axis3->setRef('axis3');
        $member3A = new Member($axis3);
        $member3A->setRef('member3A');
        $member3B = new Member($axis3);
        $member3B->setRef('member3B');

        $this->cube1->save();


        $this->report = new Report($this->cube1);
        $this->report->setLabel(new TranslatedString('Test', 'fr'));
        $this->report->setNumeratorIndicator($indicator2);
        $this->report->setNumeratorAxis1($axis3);
        $this->report->setNumeratorAxis2($axis1);
        $this->report->setChartType(Report::CHART_HORIZONTAL_STACKED);
        $this->report->setSortType(Report::SORT_VALUE_INCREASING);
        $filter = new Filter($this->report, $axis2);
        $filter->addMember($member2B);


        $this->cube2 = new Cube();

        $indicator1 = new Indicator($this->cube2);
        $indicator1->setRef('indicator1');
        $indicator1->setUnit(new UnitAPI('m'));
        $indicator1->setRatioUnit(new UnitAPI('m'));
        $indicator2 = new Indicator($this->cube2);
        $indicator2->setRef('indicator2');
        $indicator2->setUnit(new UnitAPI('m'));
        $indicator2->setRatioUnit(new UnitAPI('m'));
        $indicator3 = new Indicator($this->cube2);
        $indicator3->setRef('indicator3');
        $indicator3->setUnit(new UnitAPI('m'));
        $indicator3->setRatioUnit(new UnitAPI('m'));

        $axis1 = new Axis($this->cube2);
        $axis1->setRef('axis1');
        $member1A = new Member($axis1);
        $member1A->setRef('member1A');
        $member1B = new Member($axis1);
        $member1B->setRef('member1B');
        $axis2 = new Axis($this->cube2);
        $axis2->setRef('axis2');
        $member2A = new Member($axis2);
        $member2A->setRef('member2A');
        $member2B = new Member($axis2);
        $member2B->setRef('member2B');
        $axis3 = new Axis($this->cube2);
        $axis3->setRef('axis3');
        $member3A = new Member($axis3);
        $member3A->setRef('member3A');
        $member3B = new Member($axis3);
        $member3B->setRef('member3B');

        $this->cube2->save();

        $this->entityManager->flush();
    }

    /**
     * Tear down
     */
    public function tearDown()
    {
        parent::tearDown();

        $this->report->delete();
        $this->cube1->delete();
        $this->cube2->delete();
        $this->entityManager->flush();
    }

    public function testGetReportAsJson()
    {
        $this->assertEquals(
            '{"id":1,"idCube":1,"label":{"fr":"Test","en":null},'.
                '"refNumeratorIndicator":"indicator2","refNumeratorAxis1":"axis3","refNumeratorAxis2":"axis1",'.
                '"refDenominatorIndicator":null,"refDenominatorAxis1":null,"refDenominatorAxis2":null,'.
                '"chartType":"horizontally_stacked_chart","sortType":"orderResultByIncreasingValue",'.
                '"withUncertainty":false,"filters":[{"refAxis":"axis2","refMembers":["member2B"]}]}',
            $this->reportService->getReportAsJson($this->report)
        );
    }

    public function testDuplicateReport()
    {
        $duplicatedReport = $this->reportService->duplicateReport($this->report);

        $this->assertNull($duplicatedReport->getId());
        $this->assertSame($this->cube1, $duplicatedReport->getCube());
        $this->assertEquals('Test', $duplicatedReport->getLabel()->get('fr'));
        $this->assertNull($duplicatedReport->getLabel()->get('en'));
        $this->assertSame($this->cube1->getIndicatorByRef('indicator2'), $duplicatedReport->getNumeratorIndicator());
        $this->assertSame($this->cube1->getAxisByRef('axis3'), $duplicatedReport->getNumeratorAxis1());
        $this->assertSame($this->cube1->getAxisByRef('axis1'), $duplicatedReport->getNumeratorAxis2());
        $this->assertNull($duplicatedReport->getDenominatorIndicator());
        $this->assertNull($duplicatedReport->getDenominatorAxis1());
        $this->assertNull($duplicatedReport->getDenominatorAxis2());
        $this->assertEquals(Report::CHART_HORIZONTAL_STACKED, $duplicatedReport->getChartType());
        $this->assertEquals(Report::SORT_VALUE_INCREASING, $duplicatedReport->getSortType());
        $this->assertFalse($duplicatedReport->getWithUncertainty());
        $this->assertCount(1, $duplicatedReport->getFilters());
        $this->assertNull($duplicatedReport->getFilterForAxis($this->cube1->getAxisByRef('axis1')));
        $this->assertNull($duplicatedReport->getFilterForAxis($this->cube1->getAxisByRef('axis3')));
        $this->assertSame([$this->cube1->getAxisByRef('axis2')->getMemberByRef('member2B')], $duplicatedReport->getFilterForAxis($this->cube1->getAxisByRef('axis2'))->getMembers()->toArray());
    }

    public function testCopyReportToCube()
    {
        $duplicatedReport = $this->reportService->copyReportToCube($this->report, $this->cube2);

        $this->assertNull($duplicatedReport->getId());
        $this->assertSame($this->cube2, $duplicatedReport->getCube());
        $this->assertEquals('Test', $duplicatedReport->getLabel()->get('fr'));
        $this->assertNull($duplicatedReport->getLabel()->get('en'));
        $this->assertSame($this->cube2->getIndicatorByRef('indicator2'), $duplicatedReport->getNumeratorIndicator());
        $this->assertSame($this->cube2->getAxisByRef('axis3'), $duplicatedReport->getNumeratorAxis1());
        $this->assertSame($this->cube2->getAxisByRef('axis1'), $duplicatedReport->getNumeratorAxis2());
        $this->assertNull($duplicatedReport->getDenominatorIndicator());
        $this->assertNull($duplicatedReport->getDenominatorAxis1());
        $this->assertNull($duplicatedReport->getDenominatorAxis2());
        $this->assertEquals(Report::CHART_HORIZONTAL_STACKED, $duplicatedReport->getChartType());
        $this->assertEquals(Report::SORT_VALUE_INCREASING, $duplicatedReport->getSortType());
        $this->assertFalse($duplicatedReport->getWithUncertainty());
        $this->assertCount(1, $duplicatedReport->getFilters());
        $this->assertNull($duplicatedReport->getFilterForAxis($this->cube2->getAxisByRef('axis1')));
        $this->assertNull($duplicatedReport->getFilterForAxis($this->cube2->getAxisByRef('axis3')));
        $this->assertSame([$this->cube2->getAxisByRef('axis2')->getMemberByRef('member2B')], $duplicatedReport->getFilterForAxis($this->cube2->getAxisByRef('axis2'))->getMembers()->toArray());
    }

    public function testUpdateReportFromAnother()
    {
        $newReport = new Report($this->cube2);

        $this->assertNull($newReport->getId());
        $this->assertSame($this->cube2, $newReport->getCube());
        $this->assertNull($newReport->getLabel()->get('fr'));
        $this->assertNull($newReport->getLabel()->get('en'));
        $this->assertNull($newReport->getNumeratorIndicator());
        $this->assertNull($newReport->getNumeratorAxis1());
        $this->assertNull($newReport->getNumeratorAxis2());
        $this->assertNull($newReport->getDenominatorIndicator());
        $this->assertNull($newReport->getDenominatorAxis1());
        $this->assertNull($newReport->getDenominatorAxis2());
        $this->assertNull($newReport->getChartType());
        $this->assertEquals(Report::SORT_CONVENTIONAL, $newReport->getSortType());
        $this->assertFalse($newReport->getWithUncertainty());
        $this->assertCount(0, $newReport->getFilters());
        $this->assertNull($newReport->getFilterForAxis($this->cube2->getAxisByRef('axis1')));
        $this->assertNull($newReport->getFilterForAxis($this->cube2->getAxisByRef('axis3')));
        $this->assertNULL($newReport->getFilterForAxis($this->cube2->getAxisByRef('axis2')));
            
        $newReport = $this->reportService->updateReportFromAnother($newReport, $this->report);

        $this->assertNull($newReport->getId());
        $this->assertSame($this->cube2, $newReport->getCube());
        $this->assertEquals('Test', $newReport->getLabel()->get('fr'));
        $this->assertNull($newReport->getLabel()->get('en'));
        $this->assertSame($this->cube2->getIndicatorByRef('indicator2'), $newReport->getNumeratorIndicator());
        $this->assertSame($this->cube2->getAxisByRef('axis3'), $newReport->getNumeratorAxis1());
        $this->assertSame($this->cube2->getAxisByRef('axis1'), $newReport->getNumeratorAxis2());
        $this->assertNull($newReport->getDenominatorIndicator());
        $this->assertNull($newReport->getDenominatorAxis1());
        $this->assertNull($newReport->getDenominatorAxis2());
        $this->assertEquals(Report::CHART_HORIZONTAL_STACKED, $newReport->getChartType());
        $this->assertEquals(Report::SORT_VALUE_INCREASING, $newReport->getSortType());
        $this->assertFalse($newReport->getWithUncertainty());
        $this->assertCount(1, $newReport->getFilters());
        $this->assertNull($newReport->getFilterForAxis($this->cube2->getAxisByRef('axis1')));
        $this->assertNull($newReport->getFilterForAxis($this->cube2->getAxisByRef('axis3')));
        $this->assertSame([$this->cube2->getAxisByRef('axis2')->getMemberByRef('member2B')], $newReport->getFilterForAxis($this->cube2->getAxisByRef('axis2'))->getMembers()->toArray());
    }

}
