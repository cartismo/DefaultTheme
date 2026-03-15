<?php

namespace Modules\DefaultTheme\Tests\Unit;

use PHPUnit\Framework\TestCase;

class DefaultThemeMetadataTest extends TestCase
{
    public function test_default_theme_module_metadata_matches_theme_contract(): void
    {
        $modulePath = dirname(__DIR__, 2);
        $moduleJson = json_decode(file_get_contents($modulePath . '/module.json'), true, 512, JSON_THROW_ON_ERROR);
        $composerJson = json_decode(file_get_contents($modulePath . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('theme', $moduleJson['type'] ?? null);
        $this->assertTrue((bool) ($moduleJson['is_core'] ?? false));
        $this->assertSame(
            'Modules\\DefaultTheme\\Providers\\DefaultThemeServiceProvider',
            $moduleJson['providers'][0] ?? null
        );
        $this->assertSame(
            'Modules\\DefaultTheme\\Providers\\DefaultThemeServiceProvider',
            $composerJson['extra']['laravel']['providers'][0] ?? null
        );
        $this->assertFileExists($modulePath . '/Providers/DefaultThemeServiceProvider.php');
        $this->assertFileExists($modulePath . '/Routes/web.php');
        $this->assertFileExists($modulePath . '/Routes/admin.php');
    }
}
