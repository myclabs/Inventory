<?php

namespace Unit\Service;

use Doctrine\Common\Cache\Cache;
use MyCLabs\UnitAPI\Exception\UnknownUnitException;
use MyCLabs\UnitAPI\Operation\Operation;
use MyCLabs\UnitAPI\UnitOperationService;

/**
 * Proxy qui cache les appels au webservice des unités.
 */
class CachedUnitOperationService implements UnitOperationService
{
    /**
     * @var CachedUnitOperationService
     */
    private $wrappedService;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(UnitOperationService $wrappedService, Cache $cache)
    {
        $this->wrappedService = $wrappedService;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Operation $operation)
    {
        return $this->wrappedService->execute($operation);
    }

    /**
     * {@inheritdoc}
     */
    public function getConversionFactor($unit1, $unit2)
    {
        $cacheKey = __CLASS__ . ':conversion:' . $unit1 . ':' . $unit2;

        $factor = $this->cache->fetch($cacheKey);

        if ($factor === false) {
            $unit = $this->wrappedService->getConversionFactor($unit1, $unit2);
            $this->cache->save($cacheKey, $unit);
        }

        return $factor;
    }

    /**
     * {@inheritdoc}
     */
    public function areCompatible($unit1, $unit2)
    {
        $cacheKey = __CLASS__ . ':compatible:' . $unit1 . ':' . $unit2;

        $compatible = $this->cache->fetch($cacheKey);

        if ($compatible === false) {
            $compatible = $this->wrappedService->areCompatible($unit1, $unit2);
            $this->cache->save($cacheKey, serialize($compatible));
        } else {
            $compatible = unserialize($compatible);
        }

        return $compatible;
    }

    /**
     * {@inheritdoc}
     */
    public function inverse($unit)
    {
        $cacheKey = __CLASS__ . ':inverse:' . $unit;

        $inverse = $this->cache->fetch($cacheKey);

        if ($inverse === null) {
            throw UnknownUnitException::create($unit);
        }

        if ($inverse === false) {
            try {
                $inverse = $this->wrappedService->inverse($unit);
            } catch (UnknownUnitException $e) {
                $this->cache->save($cacheKey, null);
                throw $e;
            }
            $this->cache->save($cacheKey, $inverse);
        }

        return $inverse;
    }
}
