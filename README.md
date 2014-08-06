guzzle-oauth2-subscriber
====================

Provides an OAuth2 subscriber for [Guzzle](http://guzzlephp.org/) 4.x.

Attribution
-----------
This plugin is based on the Guzzle 3.x OAuth2 plugin by Bojan Zivanovic and Damien Tournoud from the [CommerceGuys guzzle-oauth2-plugin repository](https://github.com/commerceguys/guzzle-oauth2-plugin).

I originally forked that project, but moved to a new repo since most of the code has changed and I needed to reset the versions to < 1.0.

Features
--------

- Acquires access tokens via one of the supported grant types (code, client credentials,
  user credentials, refresh token). Or you can set an access token yourself.
- Supports refresh tokens (stores them and uses them to get new access tokens).
- Handles token expiration (acquires new tokens and retries failed requests).
- Allows storage and lookup of access tokens via callbacks

Usage
-----
In progress...