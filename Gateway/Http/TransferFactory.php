<?php

namespace Saulmoralespa\WompiPa\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        private readonly TransferBuilder $transferBuilder
    ) {
    }

    /**
     * Creates a transfer object with the specified request data.
     *
     * @param array $request The request data to be included in the transfer object.
     * @return TransferInterface The created transfer object.
     */
    public function create(array $request): TransferInterface
    {
        return $this->transferBuilder
            ->setMethod('POST')
            ->setBody($request)
            ->build();
    }
}
