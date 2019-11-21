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
    	$this->get('/api/v1/users')->assertSee(collect($this->users));
    }

    /** @test */
    function it_filters_users_by_provider()
    {
        $responseX = $this->get('/api/v1/users?provider=DataProviderX');
        $usersX = $this->applyFilters($this->files);
        $responseY = $this->get('/api/v1/users?provider=DataProviderY');
        $usersY = $this->applyFilters($this->files);

        $responseX->assertSee(collect($usersX));
        $responseX->assertDontSee(collect($usersY));
        $responseY->assertSee(collect($usersY));
        $responseY->assertDontSee(collect($usersX));
    }

    /** @test */
    function it_filters_users_by_status_code()
    {
        $status_codes = ['authorized', 'declined', 'refunded'];
        foreach ($status_codes as $code) {
            $this->get('/api/v1/users?statusCode=' . $code);
            $users = $this->applyFilters($this->files);
            $this->filterUsersByStatusCode($users);
        }
    }

    /** @test */
    function it_filters_users_by_minimum_balance()
    {
        $this->get('/api/v1/users?balanceMin=500');

        $users = $this->applyFilters($this->files);
        foreach ($users as $key => $user) {
            if (collect($user)->has('parentAmount')) {
                $this->assertGreaterThanOrEqual(request()->balanceMin, $user->parentAmount);
            }

            if (collect($user)->has('balance')) {
                $this->assertGreaterThanOrEqual(request()->balanceMin, $user->balance);
            }
        }
    }

    /** @test */
    function it_filters_users_by_maximum_balance()
    {
        $this->get('/api/v1/users?balanceMax=500');

        $users = $this->applyFilters($this->files);
        foreach ($users as $key => $user) {
            if (collect($user)->has('parentAmount')) {
                $this->assertLessThanOrEqual(request()->balanceMax, $user->parentAmount);
            }

            if (collect($user)->has('balance')) {
                $this->assertLessThanOrEqual(request()->balanceMax, $user->balance);
            }
        }
    }

    /** @test */
    function it_filters_users_by_minimum_and_maximum_balance()
    {
        $this->get('/api/v1/users?balanceMin=500&balanceMax=600');

        $users = $this->applyFilters($this->files);
        foreach ($users as $key => $user) {

            if (collect($user)->has('parentAmount')) {

                $this->assertThat(
                    $user->parentAmount,
                    $this->logicalAnd(
                        $this->greaterThanOrEqual(request()->balanceMin),
                        $this->lessThanOrEqual(request()->balanceMax)
                    )
                );

            }

            if (collect($user)->has('balance')) {

                $this->assertThat(
                    $user->balance,
                    $this->logicalAnd(
                        $this->greaterThanOrEqual(request()->balanceMin),
                        $this->lessThanOrEqual(request()->balanceMax)
                    )
                );

            }
        }
    }

    /** @test */
    function it_filters_users_by_currency()
    {
        $this->get('/api/v1/users?currency=eur');
        $users = $this->applyFilters($this->files);

        foreach ($users as $key => $user) {
            $this->assertTrue(
                    strtolower(
                        array_change_key_case(
                            collect($user)->toArray(), CASE_LOWER)['currency']) 
                        == 
                        strtolower(request()->currency));
        }
    }

    private function filterUsersByStatusCode($users) {
        foreach ($users as $key => $user) {
            $user = collect($user);

            if ($user->has('statusCode')) {
                $this->assertContains($user['statusCode'], $this->{request()->statusCode});
            };

            if ($user->has('status')) {
                $this->assertContains($user['status'], $this->{request()->statusCode});
            }
        }
    }
}
