# PXP Auth

Authentication for [https://github.com/leonickl/pxp](leonickl/pxp).

## Install and Register

Install it with `composer require leonickl/pxp-auth`.
Then, register a user model. It must extend `PXP\Auth\Models\Identity`. You can use `PXP\Auth\Models\User` or define your own model and register it in `config.php` as follows:

```php
use App\Models\User;
use PXP\Auth\Models\Identity;

return [
    'resolver' => [
        Identity::class => User::class,
    ],
]
```

## Setup name fields

By default, the `users` table has one single `name` field. If you want to customize this, adjust your `config.php`:

```php
return [
    'auth' => [
        'name-columns' => [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
        ],
    ],
]
```

## Migrate

Finally, in `migrate.php`, define proper migrations like

```php
$db->create('users', [
    'email' => 'text not null',
    'password_hash' => 'text not null',
    'role' => 'int not null default 0',
    'name' => "string not null default ''",
    'verified' => 'int not null default 0',
]);

$db->sql('create unique index if not exists '.
    'unique_users_email on users(email)');

$db->create('verification_link', [
    'token' => 'string not null',
    'user_id' => 'int references user(id)',
]);
```

Then, migrate with `./run migrate`.

Adjust the user definition to your model.

## Add auth routes

```php
use PXP\Auth\Controllers\LoginController;
use PXP\Auth\Controllers\RegisterController;
use PXP\Auth\Controllers\VerificationController;

Route::get('/auth/register')->do(RegisterController::class, 'form')->name('register');
Route::post('/auth/register')->do(RegisterController::class, 'register');

Route::get('/auth/verify')->do(VerificationController::class, 'verify')->name('verify');

Route::get('/auth/login')->do(LoginController::class, 'form')->name('login');
Route::post('/auth/login')->do(LoginController::class, 'login');

Route::group(
    Route::get('/auth/logout')->do(LoginController::class, 'logout')->name('logout'),
    Route::post('/auth/logout')->do(LoginController::class, 'logout'),
)
    ->middleware(InteractiveAuth::class);
```

## Protect routes

Protect your routes by adding middleware to them. You may also want to restrict access to users that have already verified their email address (`VerifiedEmail`) or allow access only for admins (`RequireAdmin`). You can also add middleware to a `Route::group()` block.

```php
use App\Controllers\MyController;
use PXP\Auth\Middleware\InteractiveAuth;
use PXP\Auth\Middleware\VerifiedEmail;

Route::get('/my')->do(MyController::class, 'index')->name('my')
    ->middleware(InteractiveAuth::class)
    ->middleware(VerifiedEmail::class);
```
