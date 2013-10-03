<?php

use DeepCopy\DeepCopy;
use DeepCopy\Filter\Doctrine\DoctrineCollectionFilter;
use DeepCopy\Filter\KeepFilter;
use DeepCopy\Filter\SetNullFilter;
use DeepCopy\Matcher\PropertyMatcher;
use DeepCopy\Matcher\PropertyNameMatcher;
use DeepCopy\Matcher\PropertyTypeMatcher;

/**
 * AF copy service
 */
class AF_Service_AFCopyService
{
    public function copyAF(AF_Model_AF $af, $newRef)
    {
        $deepCopy = new DeepCopy();

        // ID null
        $deepCopy->addFilter(new SetNullFilter(), new PropertyNameMatcher('id'));
        // Position
        $deepCopy->addFilter(new SetNullFilter(), new PropertyNameMatcher('position'));
        // Keep AF category
        $deepCopy->addFilter(new KeepFilter(), new PropertyMatcher('AF_Model_AF', 'category'));
        // Doctrine collections
        $deepCopy->addFilter(new DoctrineCollectionFilter(), new PropertyTypeMatcher('Doctrine\Common\Collections\Collection'));
        // SubAF
        $deepCopy->addFilter(new KeepFilter(), new PropertyMatcher('AF_Model_Component_SubAF', 'calledAF'));

        $newAF = $deepCopy->copy($af);

        $newAF->setRef($newRef);

        return $newAF;
    }
}
