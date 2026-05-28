<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\OtpVerification;
use Carbon\Carbon;

class OtpService
{
    public function generate($identifier, $type, $expiryMinutes = 10)
    {
        // Invalidate previous OTPs of same type for this identifier
        OtpVerification::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->delete();

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        return OtpVerification::create([
            'identifier' => $identifier,
            'code' => $code,
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
        ]);
    }

    public function verify($identifier, $code, $type)
    {
        $otp = OtpVerification::where('identifier', $identifier)
            ->where('code', $code)
            ->where('type', $type)
            ->where('expires_at', '>', Carbon::now())
            ->whereNull('verified_at')
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->update(['verified_at' => Carbon::now()]);

        return true;
    }
}
