<?php

namespace Conle\ESign\Foundation\ServiceProviders;

use Conle\ESign\Account;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AccountProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['account'] = function ($pimple) {
            return new Account\Account($pimple['access_token']);
        };
    }
}