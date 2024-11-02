<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Website;

class ThemesController extends Controller
{
    public function delete(Website $website, $slug)
    {
        $response = Http::delete("{$website->full_url}/wp-json/wp-monitor-bridge/v1/themes/delete/{$slug}");

        return $response->successful()
            ? back()->with('success', 'Theme deleted successfully.')
            : back()->with('error', 'Failed to delete theme.');
    }
}
