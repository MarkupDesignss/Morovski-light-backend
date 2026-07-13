<?php

return [
    App\Providers\AppServiceProvider::class,
    // ... other providers ...
    Laravel\Socialite\SocialiteServiceProvider::class,   // <-- must be present
];