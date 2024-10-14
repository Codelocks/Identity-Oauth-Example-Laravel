<?php

namespace Codelocks\Identity\Client;

use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function redirect(Request $request)
    {
        $request->session()->put('state', $state = Str::random(40));
        // build redirect url
        $query = http_build_query([
            'client_id'     => config('identity.client_id'),
            'redirect_uri'  => config('identity.redirect'),
            'response_type' => 'code',
            'scope'         => config('identity.scopes'),
            'state'         => $state,
        ]);
        return redirect(config('identity.authorize_url') . "?" . $query);
    }

    public function refresh(Request $request)
    {
        $response = Http::asForm()->post(config('identity.token_url'), [
            'grant_type'    => 'refresh_token',
            'client_id'     => config('identity.client_id'),
            'client_secret' => config('identity.client_secret'),
            'refresh_token'  => $request->get('refresh_token'),
            'code'          => $request->input('code'),
        ]);

        if($response->failed()) {
            abort($response->status(), $response->body());
        }

        return $response->json();
    }

    public function callback(Request $request)
    {

        $state = $request->session()->pull('state');
        abort_unless(strlen($state) > 0 && $state === $request->input('state'), 400);

        $response = Http::asForm()->post(config('identity.token_url'), [
            'grant_type'    => 'authorization_code',
            'client_id'     => config('identity.client_id'),
            'client_secret' => config('identity.client_secret'),
            'redirect_uri'  => config('identity.redirect'),
            'code'          => $request->input('code'),
        ]);

        if($response->failed()) {
            abort($response->status(), $response->body());
        }

        if ($request->wantsJson()) {
            return $response->json();
        }

        $response = Http::withToken($response->json('access_token'))->get(config('identity.profile_url'));
        if($response->failed()) {
            abort($response->status(), $response->body());
        }

        $profile = $response->json();
        $user = new GenericUser($profile);
        Auth::login($user);

        return redirect(route('home'))->intended('/dashboard');
    }
}