<?php
/**
 * @package    Export
 * @subpackage Test
 */

class ExcelTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('ExcelOtherTest');
        return ($suite);
    }
}

class ExcelOtherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Export_Excel
     */
    protected $_excel;
    /**
     * @var Export_Excel_Stylized
     */
    protected $_excelStylised;

    /**
     * Function called before each test
     */
    protected function setUp()
    {
        try {
            // Create a test object
            $this->_excel = new Export_Excel();
            $array = array(
                'ligne1' => array(
                    'colonne1' => 'info11',
                    'colonne2' => 'info12'
                ),
                'ligne2' => array(
                    'colonne1' => 'info21',
                    'colonne2' => 'info22'
                )
            );
            $this->_excel->body = $array;
            $this->_excel->fileName = "MyTestExcel";
            $this->_excel->subject = "Test";
            $this->_excel->description = "Ceci est un fichier de test.";

            $this->_excelStylised = new Export_Excel_Stylized();
            $array = array(
                'ligne1' => array(
                    'colonne1' => 'info11',
                    'colonne2' => 'info12'
                ),
                'ligne2' => array(
                    'colonne1' => 'info21',
                    'colonne2' => 'info22'
                )
            );
            $this->_excelStylised->body = $array;
            $this->_excelStylised->fileName = "MyTestExcelStylised";
            $this->_excelStylised->subject = "Test Stylised";
            $this->_excelStylised->description = "Ceci est un fichier de test stylisé.";
            $this->_excelStylised->institute = array(
                                        '0' => array(
                                            '0' => 'Exemple d\'export Excel',
                                        ));
            $this->_excelStylised->information = array(
                                        '0' => array(
                                            '0' => 'Sous-titre 1',
                                            '1' => 'Contenu sous-titre 1',
                                        ),
                                        '1' => array(
                                            '0' => 'Sous-titre 2',
                                            '1' => 'Contenu sous-titre 2',
                                        ),
                                        '2' => array(
                                            '0' => 'Sous-titre 3',
                                            '1' => 'Contenu sous-titre 3',
                                        )
                                    );
            $this->_excelStylised->result = array(
                                        '0' => array(
                                            'data1' => 'Données 1',
                                            'data2' => 'Données 2',
                                            'data3' => 'Données 3',
                                            'data4' => 'Données 4'
                                        ),
                                        '1' => array(
                                            'data1' => 'exemple',
                                            'data2' => 'exemple2',
                                            'data3' => '40 000',
                                            'data4' => '10'
                                        ),
                                        '2' => array(
                                            'data1' => 'exemple',
                                            'data2' => 'exemple2',
                                            'data3' => '1 000',
                                            'data4' => '90'
                                        ),
                                        '3' => array(
                                            'data1' => 'exemple',
                                            'data2' => 'exemple2',
                                            'data3' => '52 000',
                                            'data4' => '65'
                                        ),
                                        '4' => array(
                                            'data1' => 'exemple',
                                            'data2' => 'exemple2',
                                            'data3' => '11 000',
                                            'data4' => '910'
                                        ),
                                        '5' => array(
                                            'data1' => 'exemple',
                                            'data2' => 'exemple2',
                                            'data3' => '31 000',
                                            'data4' => '75'
                                        )
                                    );
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    /**
     * Test of ConvertColumnNumber
     */
    function testConvertColumnNumber()
    {
       $number = $this->_excel->convertColumnNumber(5);
       $this->assertEquals($number, "E");
       $number = $this->_excel->convertColumnNumber(32);
       $this->assertEquals($number, "F");
    }

    /**
     * Test of render
     * @depends testConvertColumnNumber
     */
    function testRender()
    {
        // On crée le fichier excel.
        $phpExcel = $this->_excel->render(false);
        $phpExcel->save("ExcelFileTest.xls");

        // On lit le fichier excel créé.
        $objReader = PHPExcel_IOFactory::createReaderForFile("ExcelFileTest.xls");
        $objReader->setReadDataOnly(true);
        $excel = $objReader->load("ExcelFileTest.xls");

        //on compare les données lues avec celles de départ
        $this->assertEquals($excel->getActiveSheet()->getCell('A1')->getValue(), 'info11');
        $this->assertEquals($excel->getActiveSheet()->getCell('A2')->getValue(), 'info21');
        $this->assertEquals($excel->getActiveSheet()->getCell('B1')->getValue(), 'info12');
        $this->assertEquals($excel->getActiveSheet()->getCell('B2')->getValue(), 'info22');
    }

    /**
     * Test of renderAvecStyle
     * @depends testRender
     */
    function testRenderAvecStyle()
    {
        //on crée le fichier excel
        $phpExcel = $this->_excelStylised->render(false);
        $phpExcel->save("ExcelFileTestStylised.xls");

        //on lit le fichier excel crée
        $excel = PHPExcel_IOFactory::load("ExcelFileTestStylised.xls");

        //on compare les données lues avec celles de départ
        for ($i = 1; $i < 6; $i++) {
            for ($j = 1; $j < 5; $j++) {
                $this->assertEquals($excel->getActiveSheet()->getCell(chr(64 + $j).$i)->getValue(),
                    $this->_excelStylised->result[$i - 1]['data'.$j]);
            }
        }
    }
}