<?php

declare(strict_types=1);

namespace HansDeBoeck\HansUi\Tests;

use FilesystemIterator;
use HansDeBoeck\HansUi\HansUiServiceProvider;
use Orchestra\Testbench\TestCase as Basis;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

abstract class TestCase extends Basis
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [HansUiServiceProvider::class];
    }

    /**
     * Een pad binnen het package zelf, niet binnen de testapplicatie.
     */
    protected function pakket(string $relatief): string
    {
        return dirname(__DIR__).'/'.$relatief;
    }

    /**
     * Alle Blade-bestanden van het package, hoe diep ook.
     *
     * Met de hand en niet met glob('views/**' . '/*.blade.php'): die vorm ziet
     * er recursief uit maar dekt precies EEN map diep, en dus stonden de
     * paginatieviews onder views/vendor/pagination/ buiten elke controle --
     * juist de bestanden die het meest met losse Tailwind-klassen werken.
     *
     * @return array<int, string>
     */
    protected function bladeBestanden(): array
    {
        $uit = [];

        $mappen = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->pakket('resources/views'), FilesystemIterator::SKIP_DOTS),
        );

        foreach ($mappen as $bestand) {
            if (str_ends_with($bestand->getFilename(), '.blade.php')) {
                $uit[] = $bestand->getPathname();
            }
        }

        sort($uit);

        return $uit;
    }
}
