<?php namespace kamermans\GuzzleOAuth2;

class TokenData {

	public function __construct(array $data = null) {
		if ($data) {
			$this->setFromArray($data);

		}
	}

	public function setFromArray(array $data) {
		foreach ($data as $key => $value) {
			$key = $this->convertToCamelCase($key);
			if (!property_exists($this, $key)) {
				continue;
			}
			$this->$key = $value;
		}
		$this->calculateExpires();
	}

	public function calculateExpires() {
		if (!$this->expires && $this->expiresIn) {
            $this->expires = time() + $this->expiresIn;
        }
	}

	public function isExpired() {
		return ($this->expires && $this->expires < time());
	}

	protected function convertToCamelCase($value) {
		return str_replace(' ', '', lcfirst(ucwords(str_replace('_', ' ', $value))));
	}

	public $accessToken;
	public $refreshToken;
	public $expiresIn;
	public $expires;
}