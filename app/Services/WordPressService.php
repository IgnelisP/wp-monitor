<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WordPressService
{
    // Existing methods...

    /**
     * Enable a theme on the WordPress site.
     */
    public function enableTheme(string $websiteUrl, string $slug): bool
    {
        $url = rtrim($websiteUrl, '/') . '/wp-json/wp-monitor-bridge/v1/themes/' . $slug . '/enable';

        try {
            $response = Http::post($url);

            if ($response->successful()) {
                return true;
            }

            Log::error('Failed to enable theme.', [
                'url' => $url,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception while enabling theme.', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Disable a theme on the WordPress site.
     */
    public function disableTheme(string $websiteUrl, string $slug): bool
    {
        $url = rtrim($websiteUrl, '/') . '/wp-json/wp-monitor-bridge/v1/themes/' . $slug . '/disable';

        try {
            $response = Http::post($url);

            if ($response->successful()) {
                return true;
            }

            Log::error('Failed to disable theme.', [
                'url' => $url,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception while disabling theme.', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete a theme on the WordPress site.
     */
    public function deleteTheme(string $websiteUrl, string $slug): bool
    {
        $url = rtrim($websiteUrl, '/') . '/wp-json/wp-monitor-bridge/v1/themes/' . $slug . '/delete';

        try {
            $response = Http::delete($url);

            if ($response->successful()) {
                return true;
            }

            Log::error('Failed to delete theme.', [
                'url' => $url,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception while deleting theme.', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
