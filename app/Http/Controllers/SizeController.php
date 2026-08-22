<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
require(public_path('firebase/jwt.php'));
class SizeController extends Controller
{
    public function verifyGST(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'gst_number' => 'required|string|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', // Basic GST regex validation
        ]);

        $gstNumber = $request->input('gst_number');
        $apiKey = 'TVRJek5EVTJOelUwTnpKRFQxSlFNREF3TURFPQ==';//env('SPRINTVERIFY_API_KEY'); // Store API key in .env file
        $apiUrl = 'https://uat.paysprint.in/sprintverify-uat/api/v1/verification/gst_verify'; // Replace with actual API endpoint if different

        // Initialize cURL
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'id_number' => $gstNumber,'refid'=>rand(10000,9999999),'filing_status'=>true
            // Add other required fields as per API documentation
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'authorisedkey: '.$apiKey, // Adjust header as per API docs (e.g., API-Key or Bearer)
            'Token:'.Jwt::generateToken()//eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ0aW1lc3RhbXAiOjE2ODAwNjc3MjcsInBhcnRuZXJJZCI6IkNPUlAwMDAwMSIsInJlcWlkIjoia2V5NTg3MDQwIn0.uSFJwpuFC2a0vaybRHGZ2RI1C9fvzF2pqJ0Qr7qa1Nk'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Enable SSL verification for security
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set timeout

        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for errors
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            Log::error('GST Verification API Error: ' . $error);
            return response()->json(['error' => 'API request failed: ' . $error], 500);
        }

        curl_close($ch);

        // Decode the response
        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            Log::error('GST Verification API Error: HTTP ' . $httpCode . ' - ' . $response);
            return response()->json(['error' => 'API returned error', 'details' => $data], $httpCode);
        }

        // Return the API response
        return response()->json($data);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Size  $size
     * @return \Illuminate\Http\Response
     */
    public function show(Size $size)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Size  $size
     * @return \Illuminate\Http\Response
     */
    public function edit(Size $size)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Size  $size
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Size $size)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Size  $size
     * @return \Illuminate\Http\Response
     */
    public function destroy(Size $size)
    {
        //
    }
}
