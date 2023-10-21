<?php

namespace MediaWiki\Extension\IPInfoFilter;

interface IPInfoServiceInterface {

	/**
	 * Gets the name of the service.
	 *
	 * @return string The name of the service.
	 */
	public function getName(): string;

	/**
	 * Gets the Autonomous System Number (ASN) for the given IP address.
	 *
	 * @param string $ip The IP address.
	 * @return int|null The ASN, or null if not available.
	 */
	public function getAsn( string $ip ): ?int;

	/**
	 * Gets the country code for the given IP address.
	 *
	 * @param string $ip The IP address.
	 * @return string|null The country code, or null if not available.
	 */
	public function getCountry( string $ip ): ?string;

	/**
	 * Gets the score for the given IP address.
	 *
	 * @param string $ip
	 * @return string|null
	 *
	 */
	public function getScore( string $ip ): ?string;

	/**
	 * Gets the proxy for the given IP address.
	 *
	 * @param string $ip
	 * @return bool|null
	 *
	 */
	public function getProxy( string $ip ): ?bool;
}
