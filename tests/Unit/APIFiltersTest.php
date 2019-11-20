<?php

namespace Tests\Unit;

use Tests\TestCase;
use Symfony\Component\Finder\Finder;
use App\Http\Controllers\API\APIFilters;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class APIFiltersTest extends TestCase
{
	use APIFilters;
	protected $files;
	protected $users;

	public function setUp() :void
	{
		parent::setUp();

		$this->files = collect(Finder::create()->files()->name('*.json')->in(base_path() . '/seeded_data'));
		$this->users = [];
    	foreach ($this->files as $fileName => $file) {
			$jsonContents = json_decode(file_get_contents($file));
			$this->users = array_merge($this->users, $jsonContents->users);
    	}
	}

	/** @test */
    function it_returns_all_users_from_all_data_providers()
    {
    	/** Get a random user */
    	$user = collect(json_decode(json_encode($this->users[array_rand($this->users)]), true));

    	$this->get('/api/v1/users')->assertSee(collect($user));
    }

    /** @test */
    function it_filters_users_by_provider()
    {
    	$response = $this->get('/api/v1/users?provider=DataProviderX');

    	$users = $this->applyFilters($this->files);
    	$this->assertEquals(5, count($users));
    }
}
