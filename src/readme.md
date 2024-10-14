# How to use `TokenGuard`

```php
<?php
 
namespace App\Providers;
 
use App\Services\Auth\JwtGuard;
use Codelocks\Identity\Client\AuthController;use Illuminate\Contracts\Foundation\Application;
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

// token guard for api
Route::middleware('auth:token')->group(function () {
    // ...
    Route::get('/user', function (){
        return request()->user()
    })
});

// auth routs
Route::get('/auth/redirect', [AuthController::class, 'redirect'])
Route::get('/auth/callback', [AuthController::class, 'callback'])

```