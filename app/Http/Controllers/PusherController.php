<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PusherController extends Controller
{
    public function auth(Request $request)
    {
        $options = array(
            'cluster' => 'eu',
            'useTLS' => true
        );
        $pusher = new \Pusher\Pusher(
            'a81a59481c18032691f8',
            'dcde495d1951c6f5e92f',
            '717322',
            $options
        );

        $json = (string) $pusher->socket_auth($request->input('channel_name'), $request->input('socket_id'));
        return json_decode($json, true);
    }
}
