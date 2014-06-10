<?php

namespace AF\Architecture\Service;

use AF\Domain\AF;
use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\Numeric\NumericAlgo;
use AF\Domain\InputSet\InputSet;
use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\Output\OutputElement;
use AF\Domain\Output\OutputIndex;
use AF\Domain\Output\OutputTotal;
use Calc_UnitValue;
use Calc_Value;
use Classification\Domain\Axis;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Indicator;
use Core\Translation\TranslatedString;
use Doctrine\ORM\EntityManager;
use Serializer\Serializer;
use Unit\UnitAPI;
use Zend_Session_Namespace;

/**
 * Service permettant de stocker un InputSet en session.
 *
 * @author matthieu.napoli
 */
class InputSetSessionStorage
{
    const SESSION_EXPIRATION = 3600;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer($this->getConfig());
    }

    /**
     * @param AF $af
     * @return PrimaryInputSet|null
     */
    public function getInputSet(AF $af)
    {
        // On cherche la saisie en session
        $session = $this->getSessionStorage();

        if (!isset($session->inputSet[$af->getId()])) {
            return null;
        }

        $json = $session->inputSet[$af->getId()];
        $inputSet = $this->serializer->unserialize($json);

        return reset($inputSet);
    }

    /**
     * @param AF              $af
     * @param PrimaryInputSet $inputSet
     */
    public function saveInputSet(AF $af, PrimaryInputSet $inputSet)
    {
        $session = $this->getSessionStorage();
        /** @noinspection PhpUndefinedFieldInspection */
        $session->inputSet[$af->getId()] = $this->serializer->serialize($inputSet);
    }

    /**
     * @return Zend_Session_Namespace
     */
    protected function getSessionStorage()
    {
        $session = new Zend_Session_Namespace(get_class());
        $session->setExpirationSeconds(self::SESSION_EXPIRATION);
        if (!is_array($session->inputSet)) {
            /** @noinspection PhpUndefinedFieldInspection */
            $session->inputSet = [];
        }
        return $session;
    }

    private function getConfig()
    {
        return [
            InputSet::class => [
                'properties' => [
                    'af' => [
                        'serialize' => function (AF $af) {
                            return $af->getId();
                        },
                        'unserialize' => function ($id) {
                            return $this->entityManager->find(AF::class, $id);
                        },
                    ],
                ],
            ],
            NumericAlgo::class => [
                'properties' => [
                    'contextIndicator' => [
                        'serialize' => function (ContextIndicator $contextIndicator = null) {
                            return $contextIndicator ? $contextIndicator->getId() : null;
                        },
                        'unserialize' => function ($id = null) {
                            return $id ? $this->entityManager->find(ContextIndicator::class, $id) : null;
                        },
                    ],
                ],
            ],
            OutputElement::class => [
                'properties' => [
                    'algo' => [
                        'serialize' => function (Algo $algo) {
                            return $algo->getId();
                        },
                        'unserialize' => function ($id) {
                            return $this->entityManager->find(Algo::class, $id);
                        },
                    ],
                ],
            ],
            OutputIndex::class => [
                'properties' => [
                    'axis' => [
                        'serialize' => function (Axis $axis) {
                            return $axis->getId();
                        },
                        'unserialize' => function ($id) {
                            return $this->entityManager->find(Axis::class, $id);
                        },
                    ],
                ],
            ],
            OutputTotal::class => [
                'properties' => [
                    'indicator' => [
                        'serialize' => function (Indicator $indicator) {
                            return $indicator->getId();
                        },
                        'unserialize' => function ($id) {
                            return $this->entityManager->find(Indicator::class, $id);
                        },
                    ],
                ],
            ],
            UnitAPI::class => [
                'serialize' => function (UnitAPI $unit) {
                    return $unit->getRef();
                },
                'unserialize' => function ($ref) {
                    return new UnitAPI($ref);
                },
            ],
            Calc_Value::class => [
                'serialize' => function (Calc_Value $object) {
                    return $object->exportToString();
                },
                'unserialize' => function ($str) {
                    return Calc_Value::createFromString($str);
                },
            ],
            Calc_UnitValue::class => [
                'serialize' => function (Calc_UnitValue $object) {
                    return $object->exportToString();
                },
                'unserialize' => function ($str) {
                    return Calc_UnitValue::createFromString($str);
                },
            ],
            TranslatedString::class => [
                'serialize' => function (TranslatedString $str) {
                    return serialize($str);
                },
                'unserialize' => function ($str) {
                    return unserialize($str);
                },
            ],
        ];
    }
}
