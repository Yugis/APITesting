<?php

namespace Tests\Unit;

use Tests\TestCase;
use Symfony\Component\Finder\Finder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinderAPITest extends TestCase
{
    /** @test */
    function it_finds_json_files_in_a_given_folder()
    {
        mkdir(base_path() . '/seeded_data/temp');

        $jsonFile = fopen(base_path() . '/seeded_data/temp/jsonFile.json', 'w');
        fclose($jsonFile);
        
        $nonJsonFile = fopen(base_path() . '/seeded_data/temp/nonJsonFile.txt', 'w');
        fclose($nonJsonFile);

        $jsonFiles = Finder::create()
                        ->files()
                        ->name('*.json')
                        ->in(base_path() . '/seeded_data/temp');

        $jsonFilesCount = collect($jsonFiles)->count();

        $this->assertEquals(1, $jsonFilesCount);

        unlink(base_path() . '/seeded_data/temp/jsonFile.json');
        unlink(base_path() . '/seeded_data/temp/nonJsonFile.txt');

        rmdir(base_path() . '/seeded_data/temp');
    }
}
