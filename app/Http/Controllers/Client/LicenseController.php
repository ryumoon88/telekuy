<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Telegram\BotLicense;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function validate(Request $request) {
        $license = $request->get('license');
        $botLicense = BotLicense::where('license', $license)->first();

        if(!$botLicense)
            return response()->json(['message' => 'Invalid license key']);

        if(!$botLicense->expired_at)
        {
            $botLicense->expired_at = Carbon::now()->add($botLicense->duration);
            $botLicense->active = true;
            $botLicense->save();
        }

        if(Carbon::now()->diffInMilliseconds($botLicense->expired_at) < 1) {
            if($botLicense->active){
                $botLicense->active = false;
                $botLicense->save();
            }
            return response()->json(['message' => 'Your license key has expired']);
        }

        return response()->json(['status' => 'ok']);
    }
}
