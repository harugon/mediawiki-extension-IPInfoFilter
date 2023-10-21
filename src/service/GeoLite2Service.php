<?php

namespace MediaWiki\Extension\IPInfoFilter\service;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;
use MediaWiki\Extension\IPInfoFilter\IPInfoServiceInterface;

class GeoLite2Service implements IPInfoServiceInterface {

	private $config;
	private $logger;

	public function __construct( $config, $logger ) {
		$this->config = $config;
		$this->logger = $logger;
	}

	public function getName(): string {
		return "GeoLite2";
	}

	public function getASN( string $ip ): ?int {
		$geoLite2AsnPath = $this->config->get( 'IPInfoFilterGeoLite2AsnPath' );
		if ( !$geoLite2AsnPath ) {
			return null;
		}
		try {
			$reader = new Reader( $geoLite2AsnPath );
			$record = $reader->asn( $ip );
		} catch ( AddressNotFoundException | InvalidDatabaseException $e ) {
			$this->logger->warning( 'GeoLite2Service - getASN: ' . $e->getMessage() );
			return null;
		}
		return $record->autonomousSystemNumber;
	}

	public function getCountry( string $ip ): ?string {
		$geoLite2CountryPath = $this->config->get( 'IPInfoFilterGeoLite2CountryPath' );
		if ( !$geoLite2CountryPath ) {
			return null;
		}
		try {
			$reader = new Reader( $geoLite2CountryPath );
			$record = $reader->country( $ip );
		}catch ( AddressNotFoundException | InvalidDatabaseException $e ) {
			$this->logger->warning( 'GeoLite2Service - getCountry: ' . $e->getMessage() );
			return null;
		}
		return $record->country->isoCode;
	}

	public function getScore( string $ip ): ?string {
		return null;
	}

	public function getProxy( string $ip ): ?bool {
		return null;
	}
}
