<?php
/**
 * @author     joseph.rouffet
 * @author     matthieu.napoli
 * @package    Social
 * @subpackage Test
 */

/**
 * @package    Social
 * @subpackage Test
 */
class Social_Model_NewsTest extends PHPUnit_Framework_TestCase
{

    public function testInitialCommentCount()
    {
        $news = new Social_Model_News();
        $this->assertCount(0, $news->getComments());
    }

}
