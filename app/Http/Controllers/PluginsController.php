<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Website;

class PluginsController extends Controller
{
    public function enable(Website $website, $slug)
    {
        $response = Http::post("{$website->full_url}/wp-json/wp-monitor-bridge/v1/plugin/enable/{$slug}");

        if ($response->successful()) {
            return response()->json(['message' => 'Plugin enabled successfully.']);
        }

        return response()->json(['message' => 'Failed to enable plugin.'], 500);
    }

    public function disable(Website $website, $slug)
    {
        $response = Http::post("{$website->full_url}/wp-json/wp-monitor-bridge/v1/plugin/disable/{$slug}");

        if ($response->successful()) {
            return response()->json(['message' => 'Plugin disabled successfully.']);
        }

        return response()->json(['message' => 'Failed to disable plugin.'], 500);
    }
}