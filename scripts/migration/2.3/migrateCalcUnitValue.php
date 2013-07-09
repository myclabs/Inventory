<?php

// BDD
$connectionSettings = Zend_Registry::get('configuration')->doctrine->default->connection;
$host = $connectionSettings->host;
$user = $connectionSettings->user;
$password = $connectionSettings->password;
$dbName = $connectionSettings->dbname;
$url = "mysql:host=$host;dbname=$dbName";
$options = [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"];
$connection = new PDO($url, $user, $password, $options);
$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$fields = [
    'Techno_Element_Coeff' => 'value',
    'Techno_Element_Process' => 'value',
    'Algo_Numeric_Constant' => 'unitValue',
    'AF_Input_Numeric' => 'value',
    'AF_Action_SetValue_Numeric' => 'value',
    'AF_Component_Numeric' => 'defaultValue',
    'AF_Output_Element' => 'value',
    'AF_Output_Total' => 'value',
    'DW_Result' => 'value',
];

foreach ($fields as $table => $column) {

    echo $table . PHP_EOL;

    $select = $connection->query("SELECT id, $column FROM $table");

    $i = 1;
    /** @noinspection PhpAssignmentInConditionInspection */
    while ($row = $select->fetch()) {
        if ($row[$column] === null) {
            continue;
        }

        $id = $row['id'];

        $object = unserialize($row[$column]);

        if ($object === false) {
            die('Error while deserializing');
        }

        if ($object instanceof Calc_Value) {
            $str = $object->digitalValue . ';' . $object->relativeUncertainty;
        }

        if ($object instanceof Calc_UnitValue) {
            $str = $object->value->digitalValue . ';' . $object->value->relativeUncertainty
                . '|' . $object->unit->getRef();
        }

        // Update
        $sql = "UPDATE $table SET $column = ? WHERE id = ?";
        $q = $connection->prepare($sql);
        $q->execute(array($str, $id));

        echo $i . " - $str" . PHP_EOL;
        $i++;
    }

    $select->closeCursor();
    echo PHP_EOL;

}


class Calc_Value
{
    public $digitalValue;
    public $relativeUncertainty;
}

class Calc_UnitValue
{
    public $unit;
    public $value;
}
