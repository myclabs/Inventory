<?php

namespace Unit\Service;

use Doctrine\Common\Cache\Cache;
use MyCLabs\UnitAPI\DTO\UnitDTO;
use MyCLabs\UnitAPI\Exception\UnknownUnitException;
use MyCLabs\UnitAPI\UnitService;

/**
 * Proxy qui cache les appels au webservice des unités.
 */
class CachedUnitService implements UnitService
{
    /**
     * @var UnitService
     */
    private $wrappedService;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(UnitService $wrappedService, Cache $cache)
    {
        $this->wrappedService = $wrappedService;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnits()
    {
        return $this->wrappedService->getUnits();
    }

    /**
     * {@inheritdoc}
     */
    public function getUnit($id)
    {
        $cacheKey = __CLASS__ . ':unit:' . $id;

        /** @var UnitDTO $unit */
        $unit = $this->cache->fetch($cacheKey);

        if ($unit === null) {
            throw UnknownUnitException::create($id);
        }

        if ($unit === false) {
            try {
                $unit = $this->wrappedService->getUnit($id);
            } catch (UnknownUnitException $e) {
                $this->cache->save($cacheKey, null);
                throw $e;
            }
            $this->cache->save($cacheKey, $unit);
        }

        return $unit;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitSystems()
    {
        return $this->wrappedService->getUnitSystems();
    }

    /**
     * {@inheritdoc}
     */
    public function getPhysicalQuantities()
    {
        return $this->wrappedService->getPhysicalQuantities();
    }

    /**
     * {@inheritdoc}
     */
    public function getCompatibleUnits($id)
    {
        $cacheKey = __CLASS__ . ':compatible-units:' . $id;

        /** @var UnitDTO[] $units */
        $units = $this->cache->fetch($cacheKey);

        if ($units === null) {
            throw UnknownUnitException::create($id);
        }

        if ($units === false) {
            try {
                $units = $this->wrappedService->getCompatibleUnits($id);
            } catch (UnknownUnitException $e) {
                $this->cache->save($cacheKey, null);
                throw $e;
            }
            $this->cache->save($cacheKey, $units);
        }

        return $units;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitOfReference($id)
    {
        $cacheKey = __CLASS__ . ':unit-of-reference:' . $id;

        /** @var UnitDTO $unit */
        $unit = $this->cache->fetch($cacheKey);

        if ($unit === null) {
            throw UnknownUnitException::create($id);
        }

        if ($unit === false) {
            try {
                $unit = $this->wrappedService->getUnit($id);
            } catch (UnknownUnitException $e) {
                $this->cache->save($cacheKey, null);
                throw $e;
            }
            $this->cache->save($cacheKey, $unit);
        }

        return $unit;
    }
}
