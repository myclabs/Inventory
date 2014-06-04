<?php

namespace Unit\Mock;

use Core\Translation\TranslatedString;
use MyCLabs\UnitAPI\DTO\UnitDTO;
use MyCLabs\UnitAPI\Exception\UnknownUnitException;
use MyCLabs\UnitAPI\UnitService;

/**
 * Mock pour UnitService pour les tests.
 */
class FakeUnitService implements UnitService
{
    private $units;

    public function __construct()
    {
        $m = new UnitDTO();
        $m->id = 'm';
        $m->label = new TranslatedString('meter', 'en');
        $m->symbol = 'm';

        $km = new UnitDTO();
        $km->id = 'km';
        $km->label = new TranslatedString('kilometer', 'en');
        $km->symbol = 'km';

        $centkm = new UnitDTO();
        $centkm->id = '100km';
        $centkm->label = new TranslatedString('100 kilometers', 'en');
        $centkm->symbol = '100km';

        $g = new UnitDTO();
        $g->id = 'g';
        $g->label = new TranslatedString('gram', 'en');
        $g->symbol = 'g';

        $kg = new UnitDTO();
        $kg->id = 'kg';
        $kg->label = new TranslatedString('kilogram', 'en');
        $kg->symbol = 'kg';

        $this->units = [
            'm'     => $m,
            'km'    => $km,
            '100km' => $centkm,
            'g'     => $g,
            'kg'    => $kg,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUnits()
    {
        return array_values($this->units);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnit($id)
    {
        if (isset($this->units[$id])) {
            return $this->units[$id];
        }

        // Cas particuliers d'unités composées utilisées dans les tests
        if ($id === 'g.j') {
            $unit = new UnitDTO();
            $unit->id = 'g.j';
            $unit->symbol = TranslatedString::untranslated('g.j');
            return $unit;
        }
        if ($id === 'kg.j') {
            $unit = new UnitDTO();
            $unit->id = 'kg.j';
            $unit->symbol = TranslatedString::untranslated('kg.j');
            return $unit;
        }
        if ($id === 'm^2.kg^2.s^-2') {
            $unit = new UnitDTO();
            $unit->id = 'm^2.kg^2.s^-2';
            $unit->symbol = TranslatedString::untranslated('m^2.kg^2.s^-2');
            return $unit;
        }

        throw UnknownUnitException::create($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitSystems()
    {
        throw new \Exception("getUnitSystems not implemented yet");
    }

    /**
     * {@inheritdoc}
     */
    public function getPhysicalQuantities()
    {
        throw new \Exception("getPhysicalQuantities not implemented yet");
    }

    /**
     * {@inheritdoc}
     */
    public function getCompatibleUnits($id)
    {
        switch ($id) {
            case 'm':
                return [];
        }

        throw UnknownUnitException::create($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitOfReference($id)
    {
        throw new \Exception("getUnitOfReference not implemented yet");
    }
}
