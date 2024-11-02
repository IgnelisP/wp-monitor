<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrometheusController extends Controller
{
    public function getStatusData(Request $request, Website $website, PrometheusService $prometheusService)
    {
        // Ensure the authenticated user owns the website
        if ($website->user_id !== auth()->id()) {
            abort(403); // Forbidden
        }

        // Validate the input dates
        $request->validate([
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
        ]);

        // Parse the start and end times
        $endTime = $request->input('end_time') ? Carbon::parse($request->input('end_time')) : Carbon::now();
        $startTime = $request->input('start_time') ? Carbon::parse($request->input('start_time')) : $endTime->copy()->subHour();

        // Ensure startTime is before endTime
        if ($startTime->greaterThan($endTime)) {
            $startTime = $endTime->copy()->subHour();
        }

        // Fetch status data using the PrometheusService
        $statusData = $prometheusService->fetchStatusData($website->full_url, $startTime, $endTime);

        return response()->json([
            'statusData' => $statusData,
        ]);
    }
}
