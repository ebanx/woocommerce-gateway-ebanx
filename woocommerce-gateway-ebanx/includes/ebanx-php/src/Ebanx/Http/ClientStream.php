<?php
namespace Ebanx\Http;

class ClientStream extends AbstractClient
{
    public function send()
    {
        try {
            $requestParams = http_build_query($this->requestParams);
            $uri    = ($this->method == 'GET') ? ($this->action . '?' . $requestParams) : $this->action;

            $context = stream_context_create(array(
                'http' => array(
                    'method' => $this->method
                  , 'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                                "User-Agent: EBANX PHP Library " . \Ebanx\Ebanx::VERSION . "\r\n"
                  , 'content' => ($this->method == 'GET') ? '' : $requestParams
                )
            ));

            if(in_array($this->get_http_response_code($uri), $this->ignoredStatusCodes)) {
                return (object) array('status' => 'HTTP_STATUS_CODE_IGNORED');
            }

            $response = file_get_contents($uri, false, $context);

            if($response && strlen($response)) {
                return $this->hasToDecodeResponse ? json_decode($response) : $response;
            }

            throw new \RuntimeException("Bad HTTP request: {$response}");
        } finally {
            $this->ignoredStatusCodes = array();
        }
    }
}
