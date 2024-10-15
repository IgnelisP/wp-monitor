<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Website;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebsitesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all websites for the authenticated user, including their associated subscription
        $websites = auth()->user()
            ->websites()
            ->with('subscription') // Eager load the subscription relationship
            ->get()
            ->map(function ($website) {
                return [
                    'id' => $website->id,
                    'name' => $website->name,
                    'full_url' => $website->full_url, // Access the full URL using the accessor
                    'renewal_date' => $website->subscription ? $website->subscription->renews_at : null, // Access the renewal date using the accessor
                ];
            });

        // Return the websites to the Inertia view
        return Inertia::render('Websites/Index', [
            'websites' => $websites,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch the plans from config/plans.php
        $plans = config('plans.plans');

        // Pass the plans to the Inertia view
        return Inertia::render('Websites/Create', [
            'plans' => $plans,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the form input
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'plan' => 'required|string', // Stripe price_id for the selected plan
        ]);

        // Parse the URL to extract scheme, domain, and path
        $parsedUrl = parse_url($request->input('url'));
        $scheme = $parsedUrl['scheme'] ?? 'http';
        $domain = $parsedUrl['host'] ?? '';
        $path = $parsedUrl['path'] ?? '/';

        // Ensure the scheme includes the full "https://" or "http://"
        if (! str_contains($scheme, '://')) {
            $scheme .= '://';
        }

        // Normalize the path: 
        // 1. If it's empty, treat it as '/'
        // 2. Ensure a trailing slash for the root '/' or paths with directories, but not for non-directory paths.
        if ($path === '') {
            $path = '/';
        } elseif ($path !== '/' && ! str_ends_with($path, '/')) {
            $path .= '/';  // Add trailing slash to non-root paths
        }

        // Ensure that the website is unique across all users, by comparing normalized domain and path
        $existingWebsite = Website::where('domain', $domain)
            ->where('path', $path)
            ->first();

        if ($existingWebsite) {
            return redirect()->back()->withErrors(['url' => 'This website is already registered.']);
        }

        // Get the authenticated user
        $user = auth()->user();

        // Retrieve the plans from config
        $plans = config('plans.plans');
        $selectedPlanType = null;

        // Find the type corresponding to the selected plan (price_id)
        foreach ($plans as $plan) {
            foreach ($plan['stripe_prices'] as $price) {
                if ($price['price_id'] === $request->input('plan')) {
                    $selectedPlanType = $price['type'];  // Get the exact type (lite_1m, professional_12m, etc.)
                    break 2;
                }
            }
        }

        // If no plan type was found, handle the error (just in case)
        if (! $selectedPlanType) {
            return redirect()->back()->withErrors(['plan' => 'Invalid plan selected.']);
        }

        // Create a new Stripe subscription using Laravel Cashier
        try {
            $subscription = $user->newSubscription($selectedPlanType, $request->input('plan'))
                ->create($request->input('paymentMethodId'));
        } catch (\Exception $e) {
            // Handle Stripe error
            return redirect()->back()->withErrors(['stripe' => $e->getMessage()]);
        }

        // Generate a unique API key for the website
        $apiKey = Str::random(60);

        // Store the website in the database
        $website = Website::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'name' => $request->input('name'),
            'scheme' => $scheme,  // Scheme now has "http://" or "https://"
            'domain' => $domain,
            'path' => $path,  // Path is normalized
            'api_key' => $apiKey,
        ]);

        // Now, send the website's full URL to the external API
        try {
            $externalApiKey = 'your_secret_api_key';  // Replace with your actual API key
            $externalApiUrl = 'http://62.72.21.220:5000/add_target';  // Replace with your actual domain

            $response = Http::withHeaders([
                'x-api-key' => $externalApiKey,
                'Content-Type' => 'application/json',
            ])->post($externalApiUrl, [
                'url' => $website->full_url,
            ]);

            if ($response->failed()) {
                // Handle API error
                return redirect()->back()->withErrors(['api' => 'Failed to add website to external server.']);
            }

        } catch (\Exception $e) {
            // Handle exception
            return redirect()->back()->withErrors(['api' => 'An error occurred while adding the website to the external server: ' . $e->getMessage()]);
        }

        // Redirect to the websites list with a success message
        return redirect()->route('websites.index')->with('success', 'Website added successfully!');
    }



    /**
     * Display the specified resource.
     */
    public function show(Website $website)
{
    // Ensure the authenticated user owns the website
    if ($website->user_id !== auth()->id()) {
        abort(403); // Forbidden
    }

    // Load any necessary relationships or attributes
    $website->load('subscription');

    // Prepare the website data
    $websiteData = [
        'id' => $website->id,
        'name' => $website->name,
        'full_url' => $website->full_url,
        'renewal_date' => $website->subscription ? $website->subscription->renews_at : null,
    ];

    // Fetch the last 20 status codes from Prometheus
    try {
        $prometheusBaseUrl = 'http://62.72.21.220:9090/api/v1/query';

        // Use the same instant query as in your PHP project
        $query = 'probe_http_status_code{instance="' . $website->full_url . '"}[1h]';

        // Make the HTTP request to Prometheus API
        $response = Http::get($prometheusBaseUrl, [
            'query' => $query
        ]);

        if ($response->failed()) {
            // Handle API error
            $statusData = [];
        } else {
            $data = $response->json();

            // Log raw Prometheus response for debugging
            Log::info('Prometheus raw response:', $data);

            // Check if data is present
            if (isset($data['data']['result'][0]['values'])) {
                $values = $data['data']['result'][0]['values'];

                // Log raw values to ensure the timestamps are correct
                Log::info('Prometheus values:', $values);

                // Map the values to a more usable format (with UNIX timestamp)
                $statusData = collect($values)->map(function ($item) {
                    // Log the exact timestamp and status code for debugging
                    Log::info('Processing Prometheus value:', ['timestamp' => $item[0], 'status_code' => $item[1]]);

                    return [
                        'time' => \Carbon\Carbon::createFromTimestampMs($item[0] * 1000)->toDateTimeString(), // Human-readable format
                        'unix_time' => $item[0],  // UNIX timestamp as it is from Prometheus
                        'status_code' => intval($item[1]), // Status code
                    ];
                })->reverse()->values()->take(20)->all(); // Get the last 20 entries
            } else {
                $statusData = [];
            }
        }
    } catch (\Exception $e) {
        // Handle exception
        $statusData = [];
        Log::error('Error fetching data from Prometheus:', ['error' => $e->getMessage()]);
    }

    // Return the Inertia view with the website data and status data
    return Inertia::render('Websites/Show', [
        'website' => $websiteData,
        'statusData' => $statusData,
    ]);
}

    
    
    



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
