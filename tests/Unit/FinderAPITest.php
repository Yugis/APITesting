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
    	$files = Finder::create()
    					->files()
    					->in(base_path() . '/seeded_data');

    	$jsonFilesCount = collect($files)
    						->filter(function ($value, $key) {
    							return \Str::is('*.json', $value);
    						})->count();

    	$filteredFiles = Finder::create()
						    	->files()
						    	->name('*.json')
						    	->in(base_path() . '/seeded_data');

	    $this->assertEquals(collect($filteredFiles)->count(), $jsonFilesCount);
    }
}
