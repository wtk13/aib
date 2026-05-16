<?php

use Symfony\Component\Finder\Finder;
use Tests\TestCase;

uses(TestCase::class);

it('contains no Polish characters in app code string literals', function () {
    $polishPattern = '/[ąćęłńóśźżĄĆĘŁŃÓŚŹŻ]/u';

    $finder = (new Finder)
        ->files()
        ->in([base_path('app'), base_path('database'), base_path('config')])
        ->name('*.php')
        ->notPath('lang/')
        ->notPath('tests/');

    $violations = [];

    foreach ($finder as $file) {
        $lines = file($file->getRealPath());
        foreach ($lines as $lineNo => $line) {
            // Only check string literals (inside quotes)
            if (preg_match_all('/(["\'])([^"\']*'.substr($polishPattern, 1, -2).'[^"\']*)\1/', $line, $matches)) {
                foreach ($matches[2] as $match) {
                    $violations[] = $file->getRelativePathname().':'.($lineNo + 1).' — "'.$match.'"';
                }
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Polish characters found in string literals:\n".implode("\n", $violations)
    );
});
