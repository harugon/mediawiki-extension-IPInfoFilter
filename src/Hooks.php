<?php

namespace MediaWiki\Extension\IPInfoFilter;

use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterBuilderHook;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterComputeVariableHook;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterGenerateGenericVarsHook;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use MWException;
use RecentChange;
use RequestContext;

class Hooks implements
	AbuseFilterComputeVariableHook,
	AbuseFilterBuilderHook,
	AbuseFilterGenerateGenericVarsHook
{

	private $prefix = 'ipinfo';
	private IPInfoServiceInterface $ipinfoService;

	public function __construct( $ipinfoService ) {
		$this->ipinfoService = $ipinfoService;
	}

	/**
	 * @param string $method
	 * @param VariableHolder $vars
	 * @param array $parameters
	 * @param string|null &$result
	 * @return bool 計算が完了した場合はfalseを返す
	 */
	public function onAbuseFilter_computeVariable( string $method, VariableHolder $vars, array $parameters, ?string &$result ) {
		$ip = $parameters['ip'];

		switch ( $method ) {
			case $this->prefix . '-service':
				$result = $this->ipinfoService->getName();
				return false;
			case $this->prefix . '-asn':
				$result = $this->ipinfoService->getASN( $ip );
				return false;
			case $this->prefix . '-country':
				$result = $this->ipinfoService->getCountry( $ip );
				return false;
			case $this->prefix . '-score':
				$result = $this->ipinfoService->getScore( $ip );
				return false;
			case $this->prefix . '-proxy':
				$result = $this->ipinfoService->getProxy( $ip );
				return false;
		}
		return true;
	}

	/**
	 * @param array &$realValues
	 * @return true
	 */
	public function onAbuseFilter_builder( array &$realValues ) {
		$realValues['vars'][$this->prefix . '_service'] = $this->prefix . '-service';
		$realValues['vars'][$this->prefix . '_asn'] = $this->prefix . '-asn';
		$realValues['vars'][$this->prefix . '_country'] = $this->prefix . '-country';
		$realValues['vars'][$this->prefix . '_score'] = $this->prefix . '-score';
		$realValues['vars'][$this->prefix . '_proxy'] = $this->prefix . '-proxy';
		return true;
	}

	public function onAbuseFilter_generateGenericVars( VariableHolder $vars, ?RecentChange $rc ) {
		$ip = $rc ? $rc->getAttribute( 'rc_ip' ) : $this->getIP();
		if ( $ip !== null ) {
			$vars->setLazyLoadVar( $this->prefix . '_service', $this->prefix . '-service', [ 'ip' => $ip ] );
			$vars->setLazyLoadVar( $this->prefix . '_asn', $this->prefix . '-asn', [ 'ip' => $ip ] );
			$vars->setLazyLoadVar( $this->prefix . '_country', $this->prefix . '-country', [ 'ip' => $ip ] );
			$vars->setLazyLoadVar( $this->prefix . '_score', $this->prefix . '-score', [ 'ip' => $ip ] );
			$vars->setLazyLoadVar( $this->prefix . '_proxy', $this->prefix . '-proxy', [ 'ip' => $ip ] );
		}
	}

	public function getIP(): ?string {
		try {
			$request = RequestContext::getMain()->getRequest();
			return $request->getIP();
		} catch ( MWException $e ) {
			return null; // 例外が発生した場合はIPアドレスを取得できないのでnullを返す
		}
	}
}
