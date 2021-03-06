<?php

namespace AF\Domain;

use AF\Domain\Algorithm\Index\AlgoResultIndex;
use AF\Domain\Algorithm\Index\FixedIndex;
use AF\Domain\Algorithm\Numeric\NumericAlgo;
use AF\Domain\Component\SubAF;
use Core\Translation\TranslatedString;
use DeepCopy\DeepCopy;
use DeepCopy\Filter\Doctrine\DoctrineCollectionFilter;
use DeepCopy\Filter\KeepFilter;
use DeepCopy\Filter\SetNullFilter;
use DeepCopy\Matcher\PropertyMatcher;
use DeepCopy\Matcher\PropertyNameMatcher;
use DeepCopy\Matcher\PropertyTypeMatcher;
use Doctrine\Common\Collections\Collection;

/**
 * Service de copie d'un AF.
 *
 * @author matthieu.napoli
 */
class AFCopyService
{
    /**
     * @param AF               $af
     * @param TranslatedString $newLabel
     * @return AF
     */
    public function copyAF(AF $af, TranslatedString $newLabel)
    {
        $deepCopy = new DeepCopy();

        // ID null
        $deepCopy->addFilter(new SetNullFilter(), new PropertyNameMatcher('id'));
        // Position
        $deepCopy->addFilter(new SetNullFilter(), new PropertyNameMatcher('position'));
        // Keep AF library
        $deepCopy->addFilter(new KeepFilter(), new PropertyMatcher(AF::class, 'library'));
        // Keep AF category
        $deepCopy->addFilter(new KeepFilter(), new PropertyMatcher(AF::class, 'category'));
        // Doctrine collections
        $deepCopy->addFilter(new DoctrineCollectionFilter(), new PropertyTypeMatcher(Collection::class));
        // SubAF
        $deepCopy->addFilter(new KeepFilter(), new PropertyMatcher(SubAF::class, 'calledAF'));
        // Indexation
        $deepCopy->addFilter(new KeepFilter(), new PropertyMatcher(NumericAlgo::class, 'contextIndicator'));
        $deepCopy->addFilter(new KeepFilter(), new PropertyMatcher(AlgoResultIndex::class, 'axis'));
        $deepCopy->addFilter(new KeepFilter(), new PropertyMatcher(FixedIndex::class, 'axis'));

        /** @var AF $newAF */
        $newAF = $deepCopy->copy($af);

        $newAF->setLabel($newLabel);

        return $newAF;
    }
}
