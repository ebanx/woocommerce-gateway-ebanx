<?php
namespace Ebanx\Http;

abstract class AbstractClient
{
	protected $method;
	protected $allowedMethods = array('POST', 'GET');
	protected $action;
	protected $requestParams;
	protected $hasToDecodeResponse = false;
	protected $ignoredStatusCodes = array();

	// WIP
}