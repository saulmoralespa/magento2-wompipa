<?php

namespace Saulmoralespa\WompiPa\Gateway\Http;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Saulmoralespa\WompiPa\Logger\Logger;

class Client implements ClientInterface
{

    public const ENDPOINT = 'https://checkout.wompi.pa/p/';

    /**
     * Places a request to the WompiPa payment gateway.
     *
     * @param TransferInterface $transferObject The transfer object containing the request data.
     * @return array An array containing redirect form parameters and the redirect URL.
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $request = $transferObject->getBody();

        return [
            'redirect_url' => self::ENDPOINT,
            'redirect_form_params' => $request,
        ];
    }
}
