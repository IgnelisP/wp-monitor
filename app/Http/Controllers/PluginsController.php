<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Website;

class PluginsController extends Controller
{
    public function enable(Website $website, $slug)
    {
        $apiKey = $website->api_key;
        $response = Http::withHeaders([
            'X-WPMB-API-Key' => $apiKey,
        ])->post("{$website->full_url}/wp-json/wp-monitor-bridge/v1/plugin/enable/{$slug}");

        if ($response->successful()) {
            $message = $response->json('status', 'Plugin enabled successfully.');
            return response()->json(['message' => $message]);
        }

        $error = $response->json('error', 'Failed to enable plugin.');
        return response()->json(['message' => $error], 500);
    }

    public function disable(Website $website, $slug)
    {
        $apiKey = $website->api_key;
        $response = Http::withHeaders([
            'X-WPMB-API-Key' => $apiKey,
        ])->post("{$website->full_url}/wp-json/wp-monitor-bridge/v1/plugin/disable/{$slug}");

        if ($response->successful()) {
            $message = $response->json('status', 'Plugin disabled successfully.');
            return response()->json(['message' => $message]);
        }

        $error = $response->json('error', 'Failed to disable plugin.');
        return response()->json(['message' => $error], 500);
    }
}