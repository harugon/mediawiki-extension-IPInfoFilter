<?php

use MediaWiki\Extension\IPInfoFilter\service\GeoLite2Service;
use MediaWiki\Extension\IPInfoFilter\service\ProxyCheckIoService;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

return [
	'IPInfoService' => static function ( MediaWikiServices $services ) {
		// Get the necessary services from the service container
		$config = $services->getMainConfig();
		$logger = LoggerFactory::getInstance( 'IPInfoFilter' );
		if ( $config->get( 'IPInfoFilterGeoLite2CountryPath' ) || $config->get( 'IPInfoFilterGeoLite2AsnPath' ) ) {
			return new GeoLite2Service(
				$config,
				$logger
			);
		} else {
			return new ProxyCheckIoService(
				$config,
				$logger,
				$services->getMainWANObjectCache(),
				$services->getHttpRequestFactory(),
			);
		}
	},
];
