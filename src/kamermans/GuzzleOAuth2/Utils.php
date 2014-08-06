<?php namespace kamermans\GuzzleOAuth2;

use GuzzleHttp\Post\PostBody;

class Utils {

	public static function arrayToPostBody(\ArrayAccess $data, PostBody $postBody = null)
	{
		if (!$postBody) {
			$postBody = new PostBody();
		}

		foreach ($data as $key => $value) {
			$postBody->setField($key, $value);
		}

		return $postBody;
	}

}