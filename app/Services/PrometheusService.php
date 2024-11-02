<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PrometheusService
{
    protected $apiBaseUrl;
    protected $queryBaseUrl;
    protected $apiKey;

    public function __construct()
    {
        // For now, hardcoding the base URLs and API key.
        $this->apiBaseUrl = 'http://62.72.21.220:5000/'; // Base URL for adding/deleting targets
        $this->queryBaseUrl = 'http://62.72.21.220:9090/api/v1/'; // Base URL for querying Prometheus
        $this->apiKey = 'your_secret_api_key'; // Replace with your actual API key
    }

    /**
     * Add a target to Prometheus for monitoring.
     *
     * @param string $url The full URL of the website to monitor.
     * @return bool True on success, false on failure.
     */
    public function addTarget(string $url): bool
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiBaseUrl . 'add_target', [
                'url' => $url,
            ]);

            if ($response->failed()) {
                // Log the error for debugging
                Log::error('Failed to add website to external server.', [
                    'url' => $url,
                    'response' => $response->body(),
                ]);
                return false;
            }

            return true;

        } catch (\Exception $e) {
            // Log the exception
            Log::error('An error occurred while adding the website to the external server.', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Fetch status data from Prometheus for a given website and time range.
     *
     * @param string $fullUrl The full URL of the website.
     * @param Carbon $startTime The start time for the data range.
     * @param Carbon $endTime The end time for the data range.
     * @return array The status data.
     */
    public function fetchStatusData(string $fullUrl, Carbon $startTime, Carbon $endTime): array
    {
        try {
            $prometheusQueryUrl = $this->queryBaseUrl . 'query_range';

            // Define the query resolution step (e.g., 1 minute)
            $step = '300s';

            // Use the range query
            $query = 'probe_http_status_code{instance="' . $fullUrl . '"}';

            // Make the HTTP request to Prometheus API
            $response = Http::get($prometheusQueryUrl, [
                'query' => $query,
                'start' => $startTime->timestamp,
                'end' => $endTime->timestamp,
                'step' => $step,
            ]);

            if ($response->failed()) {
                // Log the error
                Log::error('Error fetching data from Prometheus.', [
                    'url' => $fullUrl,
                    'response' => $response->body(),
                ]);
                return [];
            } else {
                $data = $response->json();

                // Check if data is present
                if (isset($data['data']['result'][0]['values'])) {
                    $values = $data['data']['result'][0]['values'];

                    // Map the values to a more usable format (with UNIX timestamp)
                    $statusData = collect($values)->map(function ($item) {
                        return [
                            'time' => Carbon::createFromTimestamp($item[0])->toDateTimeString(), // Human-readable format
                            'unix_time' => $item[0],  // UNIX timestamp
                            'status_code' => intval($item[1]), // Status code
                        ];
                    })->all();

                    return $statusData;
                } else {
                    return [];
                }
            }

        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while fetching data from Prometheus.', [
                'url' => $fullUrl,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
