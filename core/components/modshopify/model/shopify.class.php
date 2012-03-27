<?php
/**
 * Copyright (C) 2011 by Sandeep Shetty
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class ShopifyClient {
	private $shop_domain;
	private $token;
	private $api_key;
	private $secret;
	private $is_private_app;
	private $last_response_headers = null;

	public function __construct($shop_domain, $token, $api_key, $secret, $is_private_app=false) {
		$this->name = "ShopifyClient";
		$this->shop_domain = $shop_domain;
		$this->token = $token;
		$this->api_key = $api_key;
		$this->secret = $secret;
		$this->is_private_app = $is_private_app;
	}
	
	public function getAppInstallUrl() {
		return "http://{$this->shop_domain}/admin/api/auth?api_key={$this->api_key}";
	}
	
	public function isAppInstalled($timestamp, $signature) {
		return (md5("{$this->secret}shop={$this->shop_domain}t={$this->token}timestamp={$timestamp}") === $signature);
	}
	
	public function callsMade()	{
		return $this->shopApiCallLimitParam(0);
	}

	public function callLimit()	{
		return $this->shopApiCallLimitParam(1);
	}

	public function callsLeft($response_headers) {
		return $this->callLimit() - $this->callsMade();
	}

	public function call($method, $path, $params=array())	{
		$password = $this->is_private_app ? $this->secret : md5($this->secret.$this->token);
		$baseurl = "https://{$this->api_key}:$password@{$this->shop_domain}/";
	
		$url = $baseurl.ltrim($path, '/');
		$query = in_array($method, array('GET','DELETE')) ? $params : array();
		$payload = in_array($method, array('POST','PUT')) ? stripslashes(json_encode($params)) : array();
		$request_headers = in_array($method, array('POST','PUT')) ? array("Content-Type: application/json; charset=utf-8", 'Expect:') : array();

		$response = $this->curlHttpApiRequest($method, $url, $query, $payload, $request_headers);
		$response = json_decode($response, true);

		if (isset($response['errors']) or ($this->last_response_headers['http_status_code'] >= 400))
			throw new ShopifyApiException($method, $path, $params, $this->last_response_headers, $response);

		return (is_array($response) and (count($response) > 0)) ? array_shift($response) : $response;
	}

	private function curlHttpApiRequest($method, $url, $query='', $payload='', $request_headers=array()) {
		$url = $this->curlAppendQuery($url, $query);
		$ch = curl_init($url);
		$this->curlSetopts($ch, $method, $payload, $request_headers);
		$response = curl_exec($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		curl_close($ch);

		if ($errno) throw new ShopifyCurlException($error, $errno);

		list($message_headers, $message_body) = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
		$this->last_response_headers = $this->curlParseHeaders($message_headers);

		return $message_body;
	}

	private function curlAppendQuery($url, $query)	{
		if (empty($query)) return $url;
		if (is_array($query)) return "$url?".http_build_query($query);
		else return "$url?$query";
	}

	private function curlSetopts($ch, $method, $payload, $request_headers) {
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_USERAGENT, 'HAC');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		if ('GET' == $method) 	{
			curl_setopt($ch, CURLOPT_HTTPGET, true);
		}
		else	{
			curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, $method);
			if (!empty($request_headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
			if (!empty($payload)) {
				if (is_array($payload)) $payload = http_build_query($payload);
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $payload);
			}
		}
	}

	private function curlParseHeaders($message_headers) {
		$header_lines = preg_split("/\r\n|\n|\r/", $message_headers);
		$headers = array();
		list(, $headers['http_status_code'], $headers['http_status_message']) = explode(' ', trim(array_shift($header_lines)), 3);
		foreach ($header_lines as $header_line) {
			list($name, $value) = explode(':', $header_line, 2);
			$name = strtolower($name);
			$headers[$name] = trim($value);
		}
		return $headers;
	}
	
	private function shopApiCallLimitParam($index) {
	  if ($this->last_response_headers == null) {
			throw new Exception('Cannot be called before an API call.');
		}
		$params = explode('/', $this->last_response_headers['http_x_shopify_shop_api_call_limit']);
		return (int) $params[$index];
	}	
}

class ShopifyCurlException extends Exception { }
class ShopifyApiException extends Exception {
  protected $method;
  protected $path;
  protected $params;
  protected $response_headers;
  protected $response;

  function __construct($method, $path, $params, $response_headers, $response) {
    $this->method = $method;
    $this->path = $path;
    $this->params = $params;
    $this->response_headers = $response_headers;
    $this->response = $response;
    parent::__construct($response_headers['http_status_message'], $response_headers['http_status_code']);
  }

  function getMethod() { return $this->method; }
  function getPath() { return $this->path; }
  function getParams() { return $this->params; }
  function getResponseHeaders() { return $this->response_headers; }
  function getResponse() { return $this->response; }
}
