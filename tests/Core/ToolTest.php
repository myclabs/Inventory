<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Test
 */

/**
 * Test de la classe Core_Tools
 * @package    Core
 * @subpackage Test
 */
class Core_Test_ToolTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test de genererChaine.
     */
    public function testGenererChaine()
    {
        // Taille fixe.
        $tabChaines = array();
        // Répète le test 500 fois car c'est aléatoire.
        for ($i = 0; $i < 500; $i++) {
            $taille = (int) rand(10, 50);
            $chaine = Core_Tools::generateString($taille);
            $this->assertEquals($taille, strlen($chaine));
            $this->assertEquals(1, preg_match('/^[a-zA-Z0-9]+$/', $chaine));
            $this->assertFalse(in_array($chaine, $tabChaines),
                "Doublon de genererChaine() : $chaine dans ".print_r($tabChaines, true));
            $tabChaines[] = $chaine;
        }
    }

    /**
     * Vérifie qu'une ref vide génère une exception.
     *
     * @expectedException Core_Exception_User
     */
    public function testCheckRefEmpty()
    {
        try {
            Core_Tools::checkRef('');
        } catch (Core_Exception_User $e) {
            if ($e->getMessage() === __('Core', 'exception', 'emptyRequiredField')) {
                throw $e;
            }
        }
        $this->fail();
    }

    /**
     * Vérifie qu'une ref incorecte génère une exception.
     *
     * @expectedException Core_Exception_User
     */
    public function testCheckRefUnauthorized()
    {
        try {
            Core_Tools::checkRef('a5b_-e');
        } catch (Core_Exception_User $e) {
            if ($e->getMessage() === __('Core', 'exception', 'unauthorizedRef')) {
                throw $e;
            }
        }
        $this->fail();
    }

    /**
     * Vérifie qu'une ref incorecte génère une exception.
     */
    public function testCheckRefValid()
    {
        $this->assertTrue(Core_Tools::checkRef('a5b_e'));
    }

    /**
     * Vérifie que l'ensemble des caractères sont bien convertis.
     */
    public function testRefactor()
    {
        $oldString = '__'.
                     'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏ'.
                     'ÐÑÒÓÔÕÖØÙÚÛÜÝßàá'.
                     'âãäåæçèéêëìíîïñò'.
                     'óôõöøùúûüýÿĀāĂăĄ'.
                     'ąĆćĈĉĊċČčĎďĐđĒēĔ'.
                     'ĕĖėĘęĚěĜĝĞğĠġĢģĤ'.
                     'ĥĦħĨĩĪīĬĭĮįİıĲĳĴ'.
                     'ĵĶķĹĺĻļĽľĿŀŁłŃńŅ'.
                     'ņŇňŉŌōŎŏŐőŒœŔŕŖŗ'.
                     'ŘřŚśŜŝŞşŠšŢţŤťŦŧ'.
                     'ŨũŪūŬŭŮůŰűŲųŴŵŶŷ'.
                     'ŸŹźŻżŽžſƒƠơƯưǍǎǏ'.
                     'ǐǑǒǓǔǕǖǗǘǙǚǛǜǺǻǼ'.
                     'ǽǾǿ'.' .,:;!?/\\^\'"()#'.PHP_EOL.'-'.
                     'abc$#def ghi:?kl'.
                     '___';

        $newString = 'AAAAAAAECEEEEIIII'.
                     'DNOOOOOOUUUUYsaa'.
                     'aaaaaeceeeeiiiino'.
                     'ooooouuuuyyAaAaA'.
                     'aCcCcCcCcDdDdEeE'.
                     'eEeEeEeGgGgGgGgH'.
                     'hHhIiIiIiIiIiIJijJ'.
                     'jKkLlLlLlLlllNnN'.
                     'nNnnOoOoOoOEoeRrRr'.
                     'RrSsSsSsSsTtTtTt'.
                     'UuUuUuUuUuUuWwYy'.
                     'YZzZzZzsfOoUuAaI'.
                     'iOoUuUuUuUuUuAaAE'.
                     'aeOo'.
                     '_'.
                     'abcdef_ghikl';

        $this->assertEquals(Core_Tools::refactor(null), '');
        $this->assertEquals(Core_Tools::refactor($oldString), strtolower($newString));
    }

    /**
     * Vérifie que l'ensemble des caractères sont bien convertis.
     */
    public function testTextile()
    {
        $textileString = 'Un _rapide_ -test- pour vérifier l\'inclusion de la classe **Textile**';

        $htmlString = '	<p>Un <em>rapide</em> <del>test</del>'.
            ' pour vérifier l&#8217;inclusion de la classe <b>Textile</b></p>';

        $this->assertEquals(Core_Tools::textile($textileString), $htmlString);
    }

    public function testUCFirst()
    {
        $strings = [
            'test'   => 'Test',
            'état'   => 'État',
            'ça'     => 'Ça',
            '2 fois' => '2 fois',
        ];

        foreach ($strings as $min => $maj) {
            $this->assertEquals($maj, Core_Tools::ucFirst($min));
        }
    }

}
