<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\Services\OtpService;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function send(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'type' => 'required|string',
        ]);

        $otp = $this->otpService->generate($request->identifier, $request->type);

        // In a real application, you would send this via SMS or Email
        // For now, we'll return it in the response for testing purposes
        return response()->json([
            'message' => 'OTP sent successfully',
            'code' => $otp->code // REMOVE THIS IN PRODUCTION
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'code' => 'required|string',
            'type' => 'required|string',
        ]);

        $verified = $this->otpService->verify(
            $request->identifier,
            $request->code,
            $request->type
        );

        if (!$verified) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 422);
        }

        return response()->json([
            'message' => 'OTP verified successfully'
        ]);
    }
}
