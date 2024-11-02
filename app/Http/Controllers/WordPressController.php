<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Website;
use Exception;

class WordPressController extends Controller
{
    public function fetchPluginsAndThemes(Website $website)
    {
        try {
            $apiKey = $website->api_key;
            \Log::info('Fetching plugins from URL:', ["url" => "{$website->full_url}/wp-json/wp-monitor-bridge/v1/plugins"]);
            // Fetch plugins from the WordPress API
            $pluginsResponse = Http::withHeaders([
                'User-Agent' => 'PostmanRuntime/7.42.0',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
                'Accept' => '*/*',
                'X-WPMB-API-Key' => $apiKey,
            ])->get("{$website->full_url}/wp-json/wp-monitor-bridge/v1/plugins", [
                '_t' => time(), // Cache-busting query parameter
            ]);

            \Log::info('Plugins Response Status:', ['status' => $pluginsResponse->status()]);
        \Log::info('Plugins Response Headers:', $pluginsResponse->headers());
        \Log::info('Plugins Response Body:', ['body' => $pluginsResponse->body()]);
        
            // If request fails, return an empty array
            if ($pluginsResponse->failed()) {
                throw new Exception("Failed to fetch plugins.");
            }

            $plugins = $pluginsResponse->json() ?? [];

        } catch (Exception $e) {
            // Handle exceptions and set plugins to null or an empty array to indicate an error
            $plugins = null;
        }

        try {
            // Fetch themes from the WordPress API
            $themesResponse = Http::get("{$website->full_url}/wp-json/wp-monitor-bridge/v1/themes");

            // If request fails, return an empty array
            if ($themesResponse->failed()) {
                throw new Exception("Failed to fetch themes.");
            }

            $themes = $themesResponse->json() ?? [];

        } catch (Exception $e) {
            // Handle exceptions and set themes to null or an empty array to indicate an error
            $themes = null;
        }

        return [
            'plugins' => $plugins,
            'themes' => $themes,
        ];
    }
}
