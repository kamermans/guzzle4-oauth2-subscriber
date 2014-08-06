<?php namespace kamermans\GuzzleOAuth2\Exception;

use GuzzleHttp\Exception\TransferException;

class ReauthorizationException extends OAuth2Exception {
	
	public function __construct($message, TransferException $guzzle_exception)
	{
		parent::__construct($message, 0, $guzzle_exception);
	}

	/**
	 * Get the Guzzle Exception that was thrown while trying to reauthorize
	 * @return GuzzleHttp\Exception\TransferException
	 */
	public function getGuzzleException() {
		return $this->getPrevious();
	}
}