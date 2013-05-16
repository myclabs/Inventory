<?php
/**
 * @package    Export
 * @subpackage Test
 */

class PdfTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('PdfOtherTest');
        return ($suite);
    }
}

class PdfOtherTest extends PHPUnit_Framework_TestCase
{
    protected $_pdf;

    /**
     * Function called before each test
     */
    protected function setUp()
    {
        // Create a test object
        $this->_pdf = new Export_Pdf();
        $this->_pdf->html = "<html><b><i>This is a test.</i></b></html>";
        $this->_pdf->fileName = "OneTestPdf";
    }

    /**
     * Test of render
     */
    function testRender()
    {
        //on cree un pdf qu'on va enregistrer sur le disque
        $dompdf = $this->_pdf->render();
        $firstPdfData = $dompdf->output();
        file_put_contents('FileTest.pdf', $firstPdfData);

        //on charge le fichier pdf que l'on vient de creer
        $dataFromPdf = file_get_contents('FileTest.pdf');

        //on compare les données lues à celle de départ
        $this->assertEquals($dataFromPdf, $firstPdfData);
    }
}