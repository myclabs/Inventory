<?php

namespace Parameter\Domain\Family;

/**
 * Référence une famille de paramètres d'une bibliothèque.
 *
 * @author matthieu.napoli
 */
class FamilyReference
{
    /**
     * @var int
     */
    private $libraryId;

    /**
     * @var string
     */
    private $familyRef;

    /**
     * @param int    $libraryId
     * @param string $familyRef
     */
    public function __construct($libraryId, $familyRef)
    {
        $this->libraryId = $libraryId;
        $this->familyRef = $familyRef;
    }

    /**
     * @return int
     */
    public function getLibraryId()
    {
        return $this->libraryId;
    }

    /**
     * @return string
     */
    public function getFamilyRef()
    {
        return $this->familyRef;
    }

    public function __toString()
    {
        return $this->libraryId . '-' . $this->familyRef;
    }

    /**
     * @param string $str
     * @return FamilyReference
     */
    public static function fromString($str)
    {
        list ($libraryId, $familyRef) = explode('-', $str);

        return new self($libraryId, $familyRef);
    }
}
