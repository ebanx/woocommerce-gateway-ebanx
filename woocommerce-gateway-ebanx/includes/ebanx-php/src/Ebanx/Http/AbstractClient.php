<?php
namespace Ebanx\Http;

use Ebanx\Config;

abstract class AbstractClient
{
	protected $action;
	protected $allowedMethods = array('POST', 'GET');
	protected $hasToDecodeResponse = false;
	protected $ignoredStatusCodes = array();
	protected $method;
	protected $requestParams;

	abstract public function send();

	protected function get_http_response_code($url) {
		$headers = get_headers($url);
		return substr($headers[0], 9, 3);
	}

	public function setAction($action)
	{
		$this->action = Config::getURL() . $action;
		return $this;
	}

	public function setIgnoredStatusCodes($ignoredStatusCodes)
	{
		$this->ignoredStatusCodes = $ignoredStatusCodes;
		return $this;
	}

	public function setMethod($method)
	{
		if(!in_array(strtoupper($method), $this->allowedMethods)) {
		  throw new \InvalidArgumentException("The HTTP Client doesn't accept $method requests.");
		}

		$this->method = $method;
		return $this;
	}

	public function setRequestParams($requestParams)
	{
		$this->requestParams = $requestParams;
		$this->requestParams['integration_key'] = Config::getIntegrationKey();
		return $this;
	}

	public function setResponseType($responseType)
	{
		$this->hasToDecodeResponse = strtoupper($responseType) == 'JSON';
		return $this;
	}
}