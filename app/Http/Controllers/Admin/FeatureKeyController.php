<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureKey;

class FeatureKeyController extends Controller
{
    public function index()
    {
        return view('admin.feature-keys.index', ['features' => FeatureKey::orderBy('code')->get()]);
    }
}
