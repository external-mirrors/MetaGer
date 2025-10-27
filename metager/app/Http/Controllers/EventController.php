<?php

namespace App\Http\Controllers;

use App\Events\UserLogin;
use Illuminate\Http\Request;

/**
 * Handles routes to trigger websocket events from external apps.
 * This controller is protected by the EventAuthorization middleware.
 * It ensures that only authorized requests can trigger events.
 * The authorization token is configured in the metager configuration file.
 */
class EventController extends Controller
{
    public static function middleware(): array
    {
        return ['auth.events'];
    }

    /**
     * Dispatches a Login event.
     * This method expects a request with a header 'X-Login-Token'
     * and a JSON body containing the 'key'.
     * 
     * The event is mostly used to notify webextension and android app users
     * about a successful login. It can also be used to update the user's key
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginEvent(Request $request)
    {
        $login_token = $request->header('X-Login-Token');
        $key = $request->json('key');
        if ($login_token !== null) {
            UserLogin::dispatch($login_token, $key);
            return response()->json(['status' => 'success', 'message' => 'Login event dispatched']);
        }

        return response()->json(['status' => 'error', 'message' => 'Login token is missing'], 400);
    }

    /**
     * Dispatches a KeyChanged event.
     * This method expects a JSON body with 'key', 'change', and 'new_charge'.
     * 
     * The event is used to notify clients about changes to a key,
     * such as a change in value or a new charge.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function keyUpdateEvent(Request $request)
    {
        $key = $request->json('key');
        $change = $request->json('change', 0);
        $new_charge = $request->json('new_charge', null);

        if ($new_charge === null) {
            return response()->json(['status' => 'error', 'message' => 'No new charge provided'], 400);
        }

        if ($key !== null) {
            event(new \App\Events\KeyChanged($key, $change, $new_charge));
            return response()->json(['status' => 'success', 'message' => 'Key update event dispatched']);
        }

        return response()->json(['status' => 'error', 'message' => 'Key is missing'], 400);
    }
}
