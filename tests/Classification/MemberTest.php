<?php

namespace Tests\Classification;

use Classification\Domain\Member;
use Core\Test\TestCase;

/**
 * @covers \Classification\Domain\Member
 */
class MemberTest extends TestCase
{
    /**
     * Test d'ajout d'un parent
     */
    public function testManageChild()
    {
        $member = new Member();

        $child1 = new Member();
        $child11 = new Member();
        $child2 = new Member();
        $child3 = new Member();

        $this->assertFalse($member->hasDirectChildren());
        $this->assertFalse($member->hasDirectChild($child1));
        $this->assertFalse($member->hasDirectChild($child11));
        $this->assertFalse($member->hasDirectChild($child2));
        $this->assertFalse($member->hasDirectChild($child3));
        $this->assertEmpty($member->getDirectChildren());
        $this->assertEmpty($member->getAllChildren());

        $member->addDirectChild($child1);
        $member->addDirectChild($child2);

        $this->assertTrue($member->hasDirectChildren());
        $this->assertTrue($member->hasDirectChild($child1));
        $this->assertFalse($member->hasDirectChild($child11));
        $this->assertTrue($member->hasDirectChild($child2));
        $this->assertFalse($member->hasDirectChild($child3));
        $this->assertEquals([$child1, $child2], $member->getDirectChildren());
        $this->assertEquals([$child1, $child2], $member->getAllChildren());

        $child1->addDirectChild($child11);

        $this->assertTrue($member->hasDirectChildren());
        $this->assertTrue($member->hasDirectChild($child1));
        $this->assertFalse($member->hasDirectChild($child11));
        $this->assertTrue($member->hasDirectChild($child2));
        $this->assertFalse($member->hasDirectChild($child3));
        $this->assertEquals([$child1, $child2], $member->getDirectChildren());
        $this->assertEquals([$child1, $child2, $child11], $member->getAllChildren());

        $member->removeDirectChild($child2);

        $this->assertTrue($member->hasDirectChildren());
        $this->assertTrue($member->hasDirectChild($child1));
        $this->assertFalse($member->hasDirectChild($child11));
        $this->assertFalse($member->hasDirectChild($child2));
        $this->assertFalse($member->hasDirectChild($child3));
        $this->assertEquals([$child1], $member->getDirectChildren());
        $this->assertEquals([$child1, $child11], $member->getAllChildren());
    }

    /**
     * Test d'ajout d'un parent
     */
    public function testManageParents()
    {
        $member = new Member();

        $parent1 = new Member();
        $parent11 = new Member();
        $parent2 = new Member();
        $parent3 = new Member();

        $this->assertFalse($member->hasDirectParents());
        $this->assertFalse($member->hasDirectParent($parent1));
        $this->assertFalse($member->hasDirectParent($parent11));
        $this->assertFalse($member->hasDirectParent($parent2));
        $this->assertFalse($member->hasDirectParent($parent3));
        $this->assertEmpty($member->getDirectParents());
        $this->assertEmpty($member->getAllParents());

        $member->addDirectParent($parent1);
        $member->addDirectParent($parent2);

        $this->assertTrue($member->hasDirectParents());
        $this->assertTrue($member->hasDirectParent($parent1));
        $this->assertFalse($member->hasDirectParent($parent11));
        $this->assertTrue($member->hasDirectParent($parent2));
        $this->assertFalse($member->hasDirectParent($parent3));
        $this->assertEquals([$parent1, $parent2], $member->getDirectParents());
        $this->assertEquals([$parent1, $parent2], $member->getAllParents());

        $parent1->addDirectParent($parent11);

        $this->assertTrue($member->hasDirectParents());
        $this->assertTrue($member->hasDirectParent($parent1));
        $this->assertFalse($member->hasDirectParent($parent11));
        $this->assertTrue($member->hasDirectParent($parent2));
        $this->assertFalse($member->hasDirectParent($parent3));
        $this->assertEquals([$parent1, $parent2], $member->getDirectParents());
        $this->assertEquals([$parent1, $parent2, $parent11], $member->getAllParents());

        $member->removeDirectParent($parent2);

        $this->assertTrue($member->hasDirectParents());
        $this->assertTrue($member->hasDirectParent($parent1));
        $this->assertFalse($member->hasDirectParent($parent11));
        $this->assertFalse($member->hasDirectParent($parent2));
        $this->assertFalse($member->hasDirectParent($parent3));
        $this->assertEquals([$parent1], $member->getDirectParents());
        $this->assertEquals([$parent1, $parent11], $member->getAllParents());
    }
}
