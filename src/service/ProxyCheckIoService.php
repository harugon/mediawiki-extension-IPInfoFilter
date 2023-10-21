<?php

namespace MediaWiki\Extension\IPInfoFilter\service;

use Config;
use FormatJson;
use MediaWiki\Extension\IPInfoFilter\IPInfoServiceInterface;
use MediaWiki\Http\HttpRequestFactory;
use Psr\Log\LoggerInterface;
use WANObjectCache;

class ProxyCheckIoService implements IPInfoServiceInterface {

	/** @var Config */
	private $config;

	/**
	 * @var WANObjectCache
	 */
	private $cache;

	/** @var HttpRequestFactory */
	private $httpRequestFactory;

	/** @var LoggerInterface */
	private $logger;

	public function __construct( $config, $logger, $cache, $httpRequestFactory ) {
		$this->config = $config;
		$this->logger = $logger;
		$this->cache = $cache;
		$this->httpRequestFactory = $httpRequestFactory;
	}

	public function getName(): string {
		return 'ProxyCheckIoService';
	}

	/**
	 * @inheritDoc
	 */
	public function getAsn( string $ip ): ?int {
		$data = $this->getProxyCheckIo( $ip );
		// AS番号のプレフィックス "AS"を削除し、整数値に変換
		return isset( $data['asn'] ) ? intval( substr( $data['asn'], 2 ) ) : null;
	}

	/**
	 * @inheritDoc
	 */
	public function getCountry( string $ip ): ?string {
		$data = $this->getProxyCheckIo( $ip );
		return $data['isocode'] ?? null;
	}

	public function getScore( string $ip ): ?string {
		$data = $this->getProxyCheckIo( $ip );
		return $data['risk'] ?? null;
	}

	public function getProxy( string $ip ): ?bool {
		$data = $this->getProxyCheckIo( $ip );
		return $data['proxy'] ?? null;
	}

	/**
	 * APIリクエストを送信する
	 * https://proxycheck.io/api/
	 * @param string $ip
	 * @return array|bool
	 */
	private function fetchProxyCheckData( string $ip ): bool|array {
		$baseurl = 'https://proxycheck.io/v2/';
		$para = [
			'vpn' => 1,
			'asn' => 1,
			'risk' => 1,
			'port' => 1,
			'seen' => 1,
			'days' => 7
		];

		$key = $this->config->get( 'IPInfoFilterProxyCheckIoKey' );
		if ( $key ) {
			$para['key'] = $key;
		}

		$url = $baseurl . $ip . '?' . http_build_query( $para );
		$options = [
			'method' => 'GET'
		];
		$request = $this->httpRequestFactory->create( $url, $options, __METHOD__ );

		$status = $request->execute();

		$this->logger->info( 'ProxyCheckIoService: status:' . $status->getStatusValue() . 'RequestURL: ' . $url );
		if ( !$status->isOK() ) {
			$errorMsg = $status->getMessage()->text();
			$this->logger->warning( "ProxyCheckIoService: Request failed. Error: {$errorMsg}" );
			return false;
		}
		$response = FormatJson::decode( $request->getContent(), true );
		if ( !$response ) {
			$this->logger->warning( 'ProxyCheckIoService: Response decoding failed.' );
			return false;
		}
		if ( !$response['status'] == 'ok' ) {
			// API limit
			$detail = $response['status'] ?? 'Unknown error';
			$this->logger->warning( "ProxyCheckIoService: Response Not OK status. Detail: {$detail}" );
			return false;
		}
		if ( !isset( $response[$ip] ) ) {
			return false;
		}

		return $response[$ip];
	}

	/**
	 * キャッシュを使用して、ProxyCheck.ioからデータを取得する
	 * @param string $ip
	 * @return array|null
	 */
	private function getProxyCheckIo( string $ip ): ?array {
		// wanObjectCache
		return $this->cache->getWithSetCallback(
			$this->cache->makeKey( 'proxy-check', $ip ),
			$this->cache::TTL_DAY,
			function ( $oldValue, &$ttl, array &$setOpts ) use ( $ip ) {
				$data = $this->fetchProxyCheckData( $ip );
				if ( !$data ) {
					// リクエストに失敗した場合は、キャッシュを短くして再試行する
					$ttl = $this->cache::TTL_HOUR;
				}
				return $data;
			},
			[
				// プロセスキャッシュTTLを設定して、同じウェブリクエスト中にキャッシュサーバーに複数回クエリを送信しない
				'pcTTL' => $this->cache::TTL_PROC_LONG
			]
		);
	}
}
