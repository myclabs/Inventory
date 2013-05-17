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
class Social_Model_TextTest extends PHPUnit_Framework_TestCase
{

    public function testDefaultCreationDate()
    {
        /** @var $text Social_Model_Text */
        $text = $this->getMockForAbstractClass('Social_Model_Text');
        $this->assertEquals(new DateTime(), $text->getCreationDate(), '', 5);
    }

    public function testSetAuthorInConstructor()
    {
        $author = $this->getMockForAbstractClass('User_Model_User');
        /** @var $text Social_Model_Text */
        $text = $this->getMockForAbstractClass('Social_Model_Text', [$author]);
        $this->assertEquals($author, $text->getAuthor());
    }

}
