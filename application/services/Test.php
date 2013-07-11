<?php
/**
 * @subpackage Test
 */

/**
 * Test fixture
 * @subpackage Test
 */
class Inventory_Service_Test
{

    /**
     * @param mixed $param
     * @return mixed
     */
    public function doSomething($param)
    {
        return [
            'value' => $param,
            'locale' => Core_Locale::loadDefault()->getId(),
        ];
    }

}
