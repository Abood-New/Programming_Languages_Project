<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;

class SendVerificationCode
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event)
    {
        $phoneNumber = $event->user->phone;
        $verificationCode = $event->verificationCode;

        // Send via UltraMsg
        Http::post("https://api.ultramsg.com/" . config('services.ultramsg.instance_id') . "/messages/chat", [
            'token' => config('services.ultramsg.token'),
            'to' => "+963" . substr($phoneNumber, 1, 10),
            'body' => "Your verification code is: {$verificationCode}",
        ]);
    }
}
