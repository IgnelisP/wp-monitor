<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Website;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\PrometheusService;

class WebsitesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch the authenticated user's websites without additional data
        $websites = auth()->user()
            ->websites()
            ->get()
            ->map(function ($website) {
                return [
                    'id' => $website->id,
                    'name' => $website->name,
                    'full_url' => $website->full_url, // Access the full URL using the accessor
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
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PrometheusService $prometheusService)
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

        // Now, use the PrometheusService to add the website's full URL
        $added = $prometheusService->addTarget($website->full_url);

        if (! $added) {
            return redirect()->back()->withErrors(['api' => 'Failed to add website to external server.']);
        }

        // Redirect to the websites list with a success message
        return redirect()->route('websites.index')->with('success', 'Website added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Website $website, PrometheusService $prometheusService)
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

        // Get the end time from the request, default to now
        $endTimeInput = $request->input('end_time');
        $endTime = $endTimeInput ? Carbon::parse($endTimeInput) : Carbon::now();

        // Get the start time from the request, default to one hour before end time
        $startTimeInput = $request->input('start_time');
        $startTime = $startTimeInput ? Carbon::parse($startTimeInput) : $endTime->copy()->subHour();

        // Ensure startTime is before endTime
        if ($startTime->greaterThan($endTime)) {
            $startTime = $endTime->copy()->subHour();
        }

        // Now, use the PrometheusService to fetch the status data
        $statusData = $prometheusService->fetchStatusData($website->full_url, $startTime, $endTime);

        // Return the Inertia view with the website data and status data
        return Inertia::render('Websites/Show', [
            'website' => $websiteData,
            'statusData' => $statusData,
            'startTime' => $startTime->format('Y-m-d\TH:i'),
            'endTime' => $endTime->format('Y-m-d\TH:i'),
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
