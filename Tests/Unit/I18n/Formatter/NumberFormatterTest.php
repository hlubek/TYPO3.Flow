<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\I18n\Formatter;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for the NumberFormatter
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class NumberFormatterTest extends \F3\Testing\BaseTestCase {

	/**
	 * @var \F3\FLOW3\I18n\Locale
	 */
	protected $dummyLocale;

	/**
	 * Localized symbols array used during formatting.
	 *
	 * @var array
	 */
	protected $mockLocalizedSymbols = array(
		'decimal' => ',',
		'group' => ' ',
		'percentSign' => '%',
		'minusSign' => '-',
		'perMille' => '‰',
		'infinity' => '∞',
		'nan' => 'NaN',
	);

	/**
	 * A template array of parsed format. Used as a base in order to not repeat
	 * same fields everywhere.
	 *
	 * @var array
	 */
	protected $templateFormat = array(
		'positivePrefix' => '',
		'positiveSuffix' => '',
		'negativePrefix' => '-',
		'negativeSuffix' => '',

		'multiplier' => 1,

		'minDecimalDigits' => 0,
		'maxDecimalDigits' => 0,

		'minIntegerDigits' => 1,

		'primaryGroupingSize' => 0,
		'secondaryGroupingSize' => 0,

		'rounding' => 0,
	);

	/**
	 * @return void
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function setUp() {
		$this->dummyLocale = new \F3\FLOW3\I18n\Locale('en');
	}

	/**
	 * @test
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function formatWorks() {
		$dummyNumber = 123.456;

		$formatter = $this->getAccessibleMock('F3\FLOW3\I18n\Formatter\NumberFormatter', array('formatDecimalNumber', 'formatPercentNumber'));
		$formatter->expects($this->at(0))->method('formatDecimalNumber')->with($dummyNumber, $this->dummyLocale, 'default')->will($this->returnValue('bar1'));
		$formatter->expects($this->at(1))->method('formatPercentNumber')->with($dummyNumber, $this->dummyLocale, 'default')->will($this->returnValue('bar2'));

		$result = $formatter->format($dummyNumber, $this->dummyLocale);
		$this->assertEquals('bar1', $result);

		$result = $formatter->format($dummyNumber, $this->dummyLocale, array('percent'));
		$this->assertEquals('bar2', $result);
	}

	/**
	 * Data provider with example numbers, parsed formats, and expected results.
	 *
	 * Note: order of elements in returned array is actually different (sample
	 * number, expected result, and parsed format to use), in order to make it
	 * more readable.
	 *
	 * @return array
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function sampleNumbersAndParsedFormats() {
		return array(
			array(1234.567, '01234,5670', array_merge($this->templateFormat, array('minDecimalDigits' => 4, 'maxDecimalDigits' => 5, 'minIntegerDigits' => 5))),
			array(0.10004, '0,1', array_merge($this->templateFormat, array('minDecimalDigits' => 1, 'maxDecimalDigits' => 4))),
			array(1000.23, '1 000,25', array_merge($this->templateFormat, array('maxDecimalDigits' => 2, 'primaryGroupingSize' => 3, 'secondaryGroupingSize' => 3, 'rounding' => 0.05)))
		);
	}

	/**
	 * @test
	 * @dataProvider sampleNumbersAndParsedFormats
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function parsedFormatsAreUsedCorrectly($number, $expectedResult, $parsedFormat) {
		$formatter = $this->getAccessibleMock('F3\FLOW3\I18n\Formatter\NumberFormatter', array('dummy'));
		$result = $formatter->_call('doFormattingWithParsedFormat', $number, $parsedFormat, $this->mockLocalizedSymbols);
		$this->assertEquals($expectedResult, $result);
	}

	/**
	 * Data provider with example numbers, parsed formats, and expected results.
	 *
	 * @return array
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function customFormatsAndFormatterNumbers() {
		return array(
			array(
				1234.567, '00000.0000',
				array_merge($this->templateFormat, array('minDecimalDigits' => 4, 'maxDecimalDigits' => 4, 'minIntegerDigits' => 5)),
				'01234,5670',
			),
			array(
				0.10004, '0.0###',
				array_merge($this->templateFormat, array('minDecimalDigits' => 1, 'maxDecimalDigits' => 4)),
				'0,1',
			),
			array(
				-1099.99, '#,##0.0;(#)',
				array_merge($this->templateFormat, array('minDecimalDigits' => 1, 'primaryGroupingSize' => 3, 'secondaryGroupingSize' => 3, 'negativePrefix' => '(', 'negativeSuffix' => ')')),
				'(1 100,0)'
			),
		);
	}

	/**
	 * @test
	 * @dataProvider customFormatsAndFormatterNumbers
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function formattingUsingCustomPatternWorks($number, $format, $parsedFormat, $expectedResult) {
		$mockNumbersReader = $this->getMock('F3\FLOW3\I18n\Cldr\Reader\NumbersReader');
		$mockNumbersReader->expects($this->once())->method('parseCustomFormat')->with($format)->will($this->returnValue($parsedFormat));
		$mockNumbersReader->expects($this->once())->method('getLocalizedSymbolsForLocale')->with($this->dummyLocale)->will($this->returnValue($this->mockLocalizedSymbols));

		$formatter = new \F3\FLOW3\I18n\Formatter\NumberFormatter();
		$formatter->injectNumbersReader($mockNumbersReader);

		$result = $formatter->formatNumberWithCustomPattern($number, $format, $this->dummyLocale);
		$this->assertEquals($expectedResult, $result);
	}

	/**
	 * Data provider with numbers, parsed formats, expected results, format types
	 * (decimal, percent or currency) and currency sign if applicable.
	 *
	 * @return array
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function sampleDataForSpecificFormattingMethods() {
		return array(
			array(
				9999.9,
				array_merge($this->templateFormat, array('maxDecimalDigits' => 3, 'primaryGroupingSize' => 3, 'secondaryGroupingSize' => 3)),
				'9 999,9', 'decimal'
			),
			array(
				0.85,
				array_merge($this->templateFormat, array('multiplier' => 100, 'positiveSuffix' => '%', 'negativeSuffix' => '%')),
				'85%', 'percent'),
			array(
				5.5,
				array_merge($this->templateFormat, array('minDecimalDigits' => 2, 'maxDecimalDigits' => 2, 'primaryGroupingSize' => 3, 'secondaryGroupingSize' => 3, 'positiveSuffix' => ' ¤', 'negativeSuffix' => ' ¤')),
				'5,50 zł', 'currency', 'zł'
			),
			array(
				acos(8),
				array_merge($this->templateFormat, array('minDecimalDigits' => 2, 'maxDecimalDigits' => 2, 'primaryGroupingSize' => 3, 'secondaryGroupingSize' => 3)),
				'NaN', 'decimal'
			),
			array(
				log(0),
				array_merge($this->templateFormat, array('minDecimalDigits' => 2, 'maxDecimalDigits' => 2, 'primaryGroupingSize' => 3, 'secondaryGroupingSize' => 3)),
				'-∞', 'percent'
			),
			array(
				-log(0),
				array_merge($this->templateFormat, array('minDecimalDigits' => 2, 'maxDecimalDigits' => 2, 'primaryGroupingSize' => 3, 'secondaryGroupingSize' => 3)),
				'∞', 'currency'
			),
		);
	}

	/**
	 * @test
	 * @dataProvider sampleDataForSpecificFormattingMethods
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function specificFormattingMethodsWork($number, $parsedFormat, $expectedResult, $formatType, $currencySign = NULL) {
		$mockNumbersReader = $this->getMock('F3\FLOW3\I18n\Cldr\Reader\NumbersReader');
		$mockNumbersReader->expects($this->once())->method('parseFormatFromCldr')->with($this->dummyLocale, $formatType, 'default')->will($this->returnValue($parsedFormat));
		$mockNumbersReader->expects($this->once())->method('getLocalizedSymbolsForLocale')->with($this->dummyLocale)->will($this->returnValue($this->mockLocalizedSymbols));

		$formatter = new \F3\FLOW3\I18n\Formatter\NumberFormatter();
		$formatter->injectNumbersReader($mockNumbersReader);

		if ($formatType === 'currency') {
			$result = $formatter->formatCurrencyNumber($number, $this->dummyLocale, $currencySign);
		} else {
			$methodName = 'format' . ucfirst($formatType) . 'Number';
			$result = $formatter->$methodName($number, $this->dummyLocale);
		}
		$this->assertEquals($expectedResult, $result);
	}
}

?>