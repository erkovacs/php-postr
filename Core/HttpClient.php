<?php

namespace Postr\Core;

use Postr\Utils\Utils;
use Postr\Enums\HttpVerbs;

class HttpClient {

	private $endpoint;
	private $headers = [];
	private $client = null;
	private $code = 0;

	public function __construct()
	{
		$this->client = curl_init();
		curl_setopt($this->client, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->client, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	}

	public function toString()
	{
		print_r($this, true);
	}

	public function setHeaders($headers)
	{
		$this->headers = array_merge($this->headers, $headers);
	}

	protected function call($endpoint, $method, $params)
	{
		if(is_array($params) && !empty($params))
			$params = http_build_query($params);
		
		if(empty($params))
			$params = "";
		
		if($method !== HttpVerbs::get)
		{
			$length = strlen($params);
			$this->setHeaders(
				[
					"Content-Length : $length"
				]
			);
			curl_setopt($this->client, CURLOPT_POSTFIELDS, $params);
		}
		else {
			$endpoint .= (!empty($params)?"?":"") . $params;
		}
		
		curl_setopt($this->client, CURLOPT_URL, $endpoint);
		curl_setopt($this->client, CURLOPT_HTTPHEADER, $this->headers);

		switch($method)
		{
			case HttpVerbs::get:
				curl_setopt($this->client, CURLOPT_HTTPGET, true);
				break;
			case HttpVerbs::post:
				curl_setopt($this->client, CURLOPT_POST, true);
				break;
			case HttpVerbs::put:
				curl_setopt($this->client, CURLOPT_CUSTOMREQUEST, "PUT");
				break;
			case HttpVerbs::delete:
				curl_setopt($this->client, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
			default: 
				break;
		}

		$response   = curl_exec($this->client);
		$this->code = curl_getinfo($this->client, CURLINFO_HTTP_CODE);
		
		if($response === false)
		{
			throw new PostrException(curl_error($this->client));
		} else {
			return $response;
		}
	}

	public function getField($field)
	{
		if(is_string($field) && isset($this->{$field}))
			return $this->{$field};
	}

	public function __destruct()
	{
		curl_close($this->client);
	}
}