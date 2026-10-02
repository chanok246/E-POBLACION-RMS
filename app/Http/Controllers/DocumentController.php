<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Display the documents page.
     */
    public function index()
    {
        return view('documents.index');
    }
}
