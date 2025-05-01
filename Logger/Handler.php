<?php

namespace Saulmoralespa\WompiPa\Logger;

use Monolog\Level;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var Level
     */
    protected $loggerType = Level::Debug;

    /**
     * @var string
     */
    protected $fileName = '/var/log/wompipa/info.log';
}
