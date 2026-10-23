<?php

return [
    App\Providers\AppServiceProvider::class,
    // 未注册时 $policies 不会生效，所有 $this->authorize() 一律被拒绝
    App\Providers\AuthServiceProvider::class,
];
