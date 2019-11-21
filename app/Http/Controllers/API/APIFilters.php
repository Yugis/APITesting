<?php

namespace App\Http\Controllers\API;

trait APIFilters
{
	private $authorized = [1, 100];
	private $declined = [2, 200];
	private $refunded = [3, 300];

	private function applyFilters($files)
    {
    	/** Compile a users array out of the json files */
		$users = [];
    	foreach ($files as $fileName => $file) {
			$jsonContents = json_decode(file_get_contents($file));
			$users = array_merge($users, $jsonContents->users);
    	}

    	foreach ($files as $fileName => $file) 
    	{
    		/** Filter by a provider */
	    	if (request()->has('provider')) {
	    		if ($this->checkIfProviderExists($fileName)) {
	    			$jsonContents = json_decode(file_get_contents($file));
	    			$users = $jsonContents->users;
	    		} else {
	    			continue;
	    		}
	    	}

	    	/** Filter by status code */
	    	if (request()->has('statusCode')) {
				$users = \Arr::where($users, function($value, $key) {
					return $this->filterByStatusCode(collect($value));
				});
	    	}

	    	/** Filter by minimum balance*/
	    	if (request()->has('balanceMin')) {
	    		$users = $this->filterByMinimumBalance($users);
	    	}

	    	/** Filter by maximum balance*/
	    	if (request()->has('balanceMax')) {
	    		$users = $this->filterByMaximumBalance($users);
	    	}

	    	/** Filter by currency*/
	    	if (request()->has('currency')) {
	    		$users = $this->filterByCurrency($users);
	    	}

	    	return $users;
    	}
    }

    private function checkIfProviderExists($fileName)
    {
    	return \Str::contains($fileName, request()->provider);
    }

    private function filterByStatusCode($user) 
    {
    	if ($user->has('statusCode') && in_array($user['statusCode'], $this->{request()->statusCode})) {
			return $user;
		}

		if ($user->has('status') && in_array($user['status'], $this->{request()->statusCode})) {
			return $user;
		}
    }

    private function filterByMinimumBalance($users)
    {
    	return \Arr::where($users, function($user, $key) {
					$user = collect($user);
					if ($user->has('parentAmount')) {
						return $user['parentAmount'] >= request()->balanceMin;
					}

					if ($user->has('balance')) {
						return $user['balance'] >= request()->balanceMin;
					}
				});
    }

    private function filterByMaximumBalance($users)
    {
    	return \Arr::where($users, function($user, $key) {
					$user = collect($user);
					if ($user->has('parentAmount')) {
						return $user['parentAmount'] <= request()->balanceMax;
					}
					if ($user->has('balance')) {
						return $user['balance'] <= request()->balanceMax;
					}
				});
    }

    private function filterByCurrency($users)
    {
    	return \Arr::where($users, function($user, $key) {
					$user = collect($user);
					return strtolower(array_change_key_case($user->toArray(), CASE_LOWER)['currency']) == strtolower(request()->currency);
				});
    }
}