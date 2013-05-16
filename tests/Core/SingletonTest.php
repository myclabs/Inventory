<?php
/**
 * Test de la classe Mycsense_Singleton
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Test
 */

/**
 * Test de la classe Mycsense_Singleton
 * @package    Core
 * @subpackage Test
 */
class Core_Test_SingletonTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test de getInstance
     */
    public function testGetInstance()
    {
        $o = Test1Singleton::getInstance();
        $this->assertInstanceOf('Test1Singleton', $o);
    }

    /**
     * Vérifie que getInstance renvoie toujours la même instance
     */
    public function testInstancesIdentiques()
    {
        $o = Test1Singleton::getInstance();
        $o2 = Test1Singleton::getInstance();
        $this->assertSame($o, $o2);
    }

    /**
     * Vérifie que getInstance de 2 classes différentes
     * renvoie des instances différentes
     */
    public function testGetInstanceClassesDifferentes()
    {
        $o = Test1Singleton::getInstance();
        $o2 = Test2Singleton::getInstance();
        $this->assertNotSame($o, $o2);
    }

}

/**
 * Classe utilisée pour les tests
 * @ignore
 * @package    Core
 * @subpackage Test
 */
class Test1Singleton extends Core_Singleton
{
}

/**
 * Classe utilisée pour les tests
 * @ignore
 * @package    Core
 * @subpackage Test
 */
class Test2Singleton extends Core_Singleton
{
}
