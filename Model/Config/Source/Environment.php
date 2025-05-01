<?php

namespace Saulmoralespa\WompiPa\Model\Config\Source;

class Environment
{
    /**
     * Options for the environment
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '1', 'label' => __('Development')],
            ['value' => '0', 'label' => __('Production')]
        ];
    }
}
