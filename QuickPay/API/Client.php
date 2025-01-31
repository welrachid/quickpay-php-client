<?php

namespace QuickPay\API;

use CurlHandle;
use QuickPay\API\Exceptions\GenericException;

class Client
{
    public CurlHandle $ch;
    protected ?string $auth_string;
    protected array $headers = [];
    protected string $api_url = "";

    public function __construct(?string $auth_string = '', array $additional_headers = [], $api_url = Constants::API_URL)
    {
        if (!function_exists('curl_init')) {
            throw new GenericException('Lib cURL must be enabled on the server');
        }

        $this->api_url = $api_url;
        if($api_url == Constants::INVOICING_API_URL){
            // no idea why, but invoicing requires this header.
            $additional_headers = array_merge($additional_headers,['Accept: application/vnd.api+json']);
        }


        // Save authentication string
        $this->auth_string = $auth_string;

        // Create cURL instance.
        $this->ch = curl_init();
        curl_setopt_array($this->ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
        ]);

        $this->headers = [
            'Accept-Version: v10',
            'Accept: application/json',
        ];
        if (!empty($this->auth_string)) {
            $this->headers[] = 'Authorization: Basic ' . base64_encode($this->auth_string);
        }

        // Add custom headers and set headers in cURL object.
        $this->setHeaders($additional_headers);
    }

    function getApiURL(){
        return $this->api_url;
    }

    public function shutdown(): void
    {
        if (!empty($this->ch)) {
            curl_close($this->ch);
        }
    }

    public function setHeaders(array $additional_headers): bool
    {
        if (!empty($additional_headers)) {
            $this->headers = array_merge($this->headers, $additional_headers);
        }

        return curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
    }
}
