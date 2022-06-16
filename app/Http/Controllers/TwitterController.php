<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterController extends Controller
{
    /**
     * Create a login url and redirect
     * Try to add the Scope into the url
     */
    public function loginwithTwitter(Request $request)
    {
        // now Start to generate a request token (temporary access to more data)
        $connection = new TwitterOAuth(config('twitter.consumer_key'), config('twitter.consumer_secret'));
        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => config('twitter.redirect')));

        // Check for oauth_callback_confirmed as true
        if (isset($request_token['oauth_callback_confirmed']) && $request_token['oauth_callback_confirmed'] == true) {

            // Save the request token and secret on session
            $request->session()->put('oauth_token', $request_token['oauth_token']);
            $request->session()->put('oauth_token_secret', $request_token['oauth_token_secret']);

            // Build the authorization URL with the corresponding scope
            $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

            // Now redirect to the $url
            return redirect($url);

        } else {
            echo "Error";
        }

    }

    // Receive callback data
    public function cbTwitter(Request $request)
    {
        // Get the request token
        $oauth_token = $request->session()->get('oauth_token');
        $oauth_token_secret = $request->session()->get('oauth_token_secret');

        // Get the access token
        $oauth_verifier = $request->input('oauth_verifier');

        // verify if oauth_token == request['oauth_token']
        if ($oauth_token == $request->input('oauth_token')) {

            // Now do another request for access token
            $connection = new TwitterOAuth(config('twitter.consumer_key'), config('twitter.consumer_secret'));
            $access_token = $connection->oauth("oauth/access_token", ["oauth_token" => $oauth_token, "oauth_verifier" => $oauth_verifier]);

            dd($access_token);

        } else {
            dd("Nothing, this blow up");
        }
    }
}
