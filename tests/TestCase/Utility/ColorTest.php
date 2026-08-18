<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\Utility;

use BootstrapTools\Utility\Color;
use Cake\TestSuite\TestCase;

/**
 * ColorTest
 */
class ColorTest extends TestCase
{
    /**
     * @return array<string, array<string, string>>
     */
    public static function deduceProvider(): array
    {
        return [
            'bootstrap primary' => ['primary', Color::TYPE_BOOTSTRAP],
            'bootstrap success' => ['success', Color::TYPE_BOOTSTRAP],
            'hex 6 digits' => ['#F00BA1', Color::TYPE_HEX],
            'hex 3 digits' => ['#abc', Color::TYPE_HEX],
            'hex uppercase' => ['#ABCDEF', Color::TYPE_HEX],
            'rgb' => ['rgb(255, 0, 128)', Color::TYPE_RGB],
            'rgba alpha 0' => ['rgba(0, 0, 0, 0)', Color::TYPE_RGBA],
            'rgba alpha 1' => ['rgba(255, 255, 255, 1)', Color::TYPE_RGBA],
            'rgba alpha decimal' => ['rgba(10, 20, 30, 0.5)', Color::TYPE_RGBA],
            'unknown word' => ['tomato', Color::TYPE_UNKNOWN],
            'unknown short hex' => ['#GGGGGG', Color::TYPE_UNKNOWN],
            'unknown no spaces rgb' => ['rgb(255,0,0)', Color::TYPE_UNKNOWN],
            'unknown bad alpha' => ['rgba(1, 2, 3, 2)', Color::TYPE_UNKNOWN],
            'empty string' => ['', Color::TYPE_UNKNOWN],
        ];
    }

    /**
     * @dataProvider deduceProvider
     */
    public function testDeduceType(string $color, string $expectedType): void
    {
        $this->assertSame($expectedType, (new Color($color))->getType());
    }

    public function testGetColor(): void
    {
        $color = new Color('#123456');

        $this->assertSame('#123456', $color->getColor());
    }

    public function testExplicitTypeIsKept(): void
    {
        $color = new Color('primary', Color::TYPE_HEX);

        $this->assertSame(Color::TYPE_HEX, $color->getType());
        $this->assertSame('primary', $color->getColor());
    }

    public function testDefaultTypeIsBootstrapWhenConstructorTypeNull(): void
    {
        $color = new Color(Color::DANGER);

        $this->assertSame('danger', $color->getColor());
        $this->assertSame(Color::TYPE_BOOTSTRAP, $color->getType());
    }
}
