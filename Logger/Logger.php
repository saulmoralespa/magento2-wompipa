<?php

namespace Saulmoralespa\WompiPa\Logger;

class Logger extends \Monolog\Logger
{
    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
