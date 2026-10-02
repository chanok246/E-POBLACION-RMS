<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BlotterController extends Controller
{
    /**
     * Display the blotter records.
     */
    public function index()
    {
        return view('blotters.index');
    }
}
