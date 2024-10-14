# How to use `TokenGuard`

```php
<?php
 
namespace App\Providers;
 
use App\Services\Auth\JwtGuard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
 
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application authentication / authorization services.
     */
    public function boot(): void
    {
        Auth::extend('token', function (Application $app) {
            // Return an instance of Illuminate\Contracts\Auth\Guard...

            return new TokenGuard($app['request']));
        });
    }
}

// on routes

Route::middleware('auth:token')->group(function () {
    // ...
});

```