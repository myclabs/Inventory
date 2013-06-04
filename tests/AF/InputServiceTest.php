<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

/**
 * @package AF
 */
class InputServiceTest extends Core_Test_TestCase
{
    /**
     * @var AF_Service_InputService
     */
    private $inputService;
    /**
     * @var AF_Model_AF
     */
    private $af;

    public function testEditInputSet()
    {
        $inputSet1 = new AF_Model_InputSet_Primary($this->af);
        $inputSet2 = new AF_Model_InputSet_Primary($this->af);

        $this->inputService->editInputSet($inputSet1, $inputSet2);
    }

    public function setUp()
    {
        parent::setUp();

        $this->inputService = AF_Service_InputService::getInstance();

        $this->af = new AF_Model_AF('test');
        $this->af->save();
        $this->entityManager->flush();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->af->delete();
        $this->entityManager->flush();
    }
}
