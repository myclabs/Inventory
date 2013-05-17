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
class Social_Model_UserGroupTest extends PHPUnit_Framework_TestCase
{

    public function testDefaultUsersEmpty()
    {
        $userGroup = new Social_Model_UserGroup('', '');
        $this->assertCount(0, $userGroup->getUsers());
    }

}
