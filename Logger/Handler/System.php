<?php

namespace Saulmoralespa\WompiPa\Logger\Handler;

use Monolog\Level;

class System extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var Level
     */
    protected $loggerType = Level::Debug;

    /**
     * @var string
     */
    protected $fileName = '/var/log/wompipa.log';
}
