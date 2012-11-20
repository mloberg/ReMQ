<?php

namespace ReMQ;

use Predis\Client;

class Redis extends Client
{

    function __construct($config = null)
    {
        parent::__construct($config);
        if ($config['auth']) {
            $this->auth($config['auth']);
        }
    }

}
