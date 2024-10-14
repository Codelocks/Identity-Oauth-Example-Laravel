<?php

namespace Codelocks\Identity\Client;

use Illuminate\Auth\GenericUser;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * this guard used by resource server to authorize api by Identity access token
 * see: https://laravel.com/docs/10.x/authentication#adding-custom-guards
 */
class TokenGuard implements Guard
{
    use GuardHelpers;



    public function __construct(
        protected Request $request,
        protected string $inputKey = 'token',
    ) { }

    public function user()
    {
        if(!is_null($this->user)) {
            return $this->user;
        }
        $accessToken = $this->getTokenForRequest();

        if (!empty($accessToken)) {
            return $this->user = $this->getUserByToken($accessToken);
        }
        return null;
    }

    public function validate(array $credentials = [])
    {
        return !is_null((new static($credentials['request'], $this->inputKey))->user());
    }


    private function getTokenForRequest(): ?string
    {
        $token = $this->request->query($this->inputKey);

        if (empty($token)) {
            $token = $this->request->input($this->inputKey);
        }

        if (empty($token)) {
            $token = $this->request->bearerToken();
        }

        return $token;
    }

    private function getUserByToken($accessToken): ?Authenticatable
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken"
        ])->get(data_get(config('identity'), 'host') . data_get(config('identity'), 'user_url'));
        if ($response->failed()) {
            return null;
        }
        return new GenericUser($response->json());
    }
}