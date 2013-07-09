<?php
/**
 * @author matthieu.napoli
 */

/**
 * Test pour Core_Locale
 */
class Core_Test_LocaleTest extends Core_Test_TestCase
{
    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage Locale inconnue
     */
    public function testLoadUnknown()
    {
        Core_Locale::load('foo');
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage Locale non supportée
     */
    public function testLoadUnsupported()
    {
        Core_Locale::load('is');
    }

    /**
     * @dataProvider localeProvider
     * @param Core_Locale $locale
     */
    public function testReadInteger(Core_Locale $locale)
    {
        $this->assertNull($locale->readInteger(""));
        $this->assertSame(10, $locale->readInteger("10"));
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @dataProvider localeProvider
     * @param Core_Locale $locale
     */
    public function testReadIntegerExceptionFloat(Core_Locale $locale)
    {
        $this->assertSame(10, $locale->readInteger("10."));
    }

    /**
     * @dataProvider localeProvider
     * @param Core_Locale $locale
     */
    public function testReadNumber(Core_Locale $locale)
    {
        $this->assertNull($locale->readNumber(""));
        $this->assertSame(10., $locale->readNumber("10"));
    }

    public function testReadNumberEn()
    {
        $locale = Core_Locale::load('en');
        $this->assertSame(10., $locale->readNumber("10.0"));
        $this->assertSame(10.1, $locale->readNumber("10.1"));
    }

    public function testReadNumberFr()
    {
        $locale = Core_Locale::load('fr');
        $this->assertSame(10., $locale->readNumber("10,0"));
        $this->assertSame(10.1, $locale->readNumber("10,1"));
    }

    /**
     * @dataProvider localeProvider
     * @param Core_Locale $locale
     */
    public function testFormatUncertainty(Core_Locale $locale)
    {
        $this->assertSame('0 %', $locale->formatUncertainty(null));
        $this->assertSame('0 %', $locale->formatUncertainty(0));
        $this->assertSame('10 %', $locale->formatUncertainty(10));
        $this->assertSame('10 %', $locale->formatUncertainty(10.0));
        $this->assertSame('10 %', $locale->formatUncertainty(10.4));
        $this->assertSame('11 %', $locale->formatUncertainty(10.6));
        $this->assertSame('130 %', $locale->formatUncertainty(130));
    }

    /**
     * @dataProvider localeProvider
     * @param Core_Locale $locale
     */
    public function testFormatNumber(Core_Locale $locale)
    {
        $this->assertSame('', $locale->formatNumber(null));
        $this->assertSame('0', $locale->formatNumber(0));
        $this->assertSame('10', $locale->formatNumber(10));
    }

    public function testFormatNumberEn()
    {
        $locale = Core_Locale::load('en');
        $this->assertSame('10.1', $locale->formatNumber(10.1));
        $this->assertSame('1,000', $locale->formatNumber(1000));
        $this->assertSame('1,000.1', $locale->formatNumber(1000.1));
    }

    public function testFormatNumberFr()
    {
        $locale = Core_Locale::load('fr');
        $this->assertSame('10,1', $locale->formatNumber(10.1));
        $this->assertSame('1 000', $locale->formatNumber(1000));
        $this->assertSame('1 000,1', $locale->formatNumber(1000.1));
    }

    /**
     * @dataProvider localeProvider
     * @param Core_Locale $locale
     */
    public function testFormatNumberForInput(Core_Locale $locale)
    {
        $this->assertSame('', $locale->formatNumberForInput(null));
        $this->assertSame('0', $locale->formatNumberForInput(0));
        $this->assertSame('10', $locale->formatNumberForInput(10));
    }

    public function testFormatNumberForInputEn()
    {
        $locale = Core_Locale::load('en');
        $this->assertSame('10.1', $locale->formatNumberForInput(10.1));
        $this->assertSame('1000', $locale->formatNumberForInput(1000));
        $this->assertSame('1000.1', $locale->formatNumberForInput(1000.1));
    }

    public function testFormatNumberForInputFr()
    {
        $locale = Core_Locale::load('fr');
        $this->assertSame('10,1', $locale->formatNumberForInput(10.1));
        $this->assertSame('1000', $locale->formatNumberForInput(1000));
        $this->assertSame('1000,1', $locale->formatNumberForInput(1000.1));
    }

    /**
     * @return Core_Locale[]
     */
    public function localeProvider()
    {
        return [
            'fr' => [Core_Locale::load('fr')],
            'en' => [Core_Locale::load('en')],
        ];
    }
}
