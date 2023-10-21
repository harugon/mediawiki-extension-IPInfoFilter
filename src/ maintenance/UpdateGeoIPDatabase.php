<?php
namespace MediaWiki\Extension\Example\Maintenance;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

use Maintenance;
use MediaWiki\MediaWikiServices;

/**
 * Manual:Writing maintenance scripts - MediaWiki https://www.mediawiki.org/wiki/Manual:Writing_maintenance_scripts
 */
class UpdateGeoIPDatabase extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Updates the GeoIP2 database using tronovav/geoip2-update' );
	}

	public function execute() {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$licenseKey = $config->get( 'IPInfoFilterMaxMindLicenseKey' );
		if ( !$licenseKey ) {
			$this->error( "IPInfoFilterMaxMindLicenseKey is not set in configuration.", 1 );
		}
		// Home | GeoDbase Update https://www.geodbase-update.com/
		$updater = new Tronovav\GeoIP2Update\Updater( [
			'license_key' => $licenseKey,
			'dir' => 'DESTINATION_DIRECTORY_PATH',
			'editions' => [ 'GeoLite2-ASN','GeoLite2-Country' ],
			// Other options...
		] );
		$updater->update();
		$updater->errors();
		$this->output( "GeoIP2 database updated successfully.\n" );
	}
}

$maintClass = UpdateGeoIPDatabase::class;
require_once RUN_MAINTENANCE_IF_MAIN;
