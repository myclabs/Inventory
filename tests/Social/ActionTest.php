<?php
/**
 * @author     matthieu.napoli
 * @package    Social
 * @subpackage Test
 */

/**
 * @package    Social
 * @subpackage Test
 */
class Social_Model_ActionTest extends PHPUnit_Framework_TestCase
{

    public function testInitialCommentCount()
    {
        /** @var $action Social_Model_Action */
        $action = $this->getMockForAbstractClass('Social_Model_Action');
        $this->assertCount(0, $action->getComments());
    }

}
