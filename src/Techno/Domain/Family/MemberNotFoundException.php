<?php

namespace Techno\Domain\Family;

use Core_Exception_NotFound;
use Exception;

/**
 * A member wasn't found in a dimension.
 */
class MemberNotFoundException extends Core_Exception_NotFound
{
    /**
     * @var string
     */
    private $family;

    /**
     * @var string
     */
    private $dimension;

    /**
     * @var string
     */
    private $member;

    /**
     * @param string $family
     * @param string $dimension
     * @param string $member
     * @return self
     */
    public static function create($family, $dimension, $member)
    {
        return new self(sprintf(
            'Unknown member "%s" in dimension "%s"',
            $member,
            $dimension
        ), $family, $dimension, $member);
    }

    /**
     * @param string         $message
     * @param string         $family
     * @param string         $dimension Dimension ID
     * @param string         $member    Member ID
     * @param Exception|null $previous  Previous exception
     */
    public function __construct($message, $family, $dimension, $member, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->family = $family;
        $this->dimension = $dimension;
        $this->member = $member;
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * @return string
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * @return string
     */
    public function getMember()
    {
        return $this->member;
    }
}
