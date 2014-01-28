<?php

require_once __DIR__ . '/User/populate.php';

class Inventory_Populate extends Core_Script_Populate
{
    public function populateEnvironment($environment)
    {
        $populateUnit = new User_Populate();
        $populateUnit->populateEnvironment($environment);
    }
}
