<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\Finder\Finder;

class APIController extends Controller
{
	use APIFilters;

    public function index()
    {
    	$files = collect(Finder::create()->files()->name('*.json')->in(base_path() . '/seeded_data'));

    	return $this->applyFilters($files);
    }
}
