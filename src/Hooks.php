<?php

namespace MediaWiki\Extension\IPInfoFilter;

use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterBuilderHook;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterComputeVariableHook;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterGenerateUserVarsHook;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use RecentChange;
use User;

class Hooks implements
	AbuseFilterGenerateUserVarsHook,
	AbuseFilterComputeVariableHook,
	AbuseFilterBuilderHook
{

	private $prefix = 'ipinfo';
	private IPInfoServiceInterface $ipinfoService;

	public function __construct( $ipinfoService ) {
		$this->ipinfoService = $ipinfoService;
	}

	public function onAbuseFilter_generateUserVars( VariableHolder $vars, User $user, ?RecentChange $rc ) {
		$vars->setLazyLoadVar( $this->prefix . '_service', $this->prefix . '-service', [ 'user' => $user ] );
		$vars->setLazyLoadVar( $this->prefix . '_asn', $this->prefix . '-asn', [ 'user' => $user ] );
		$vars->setLazyLoadVar( $this->prefix . '_country', $this->prefix . '-country', [ 'user' => $user ] );
		$vars->setLazyLoadVar( $this->prefix . '_score', $this->prefix . '-score', [ 'user' => $user ] );
		$vars->setLazyLoadVar( $this->prefix . '_proxy', $this->prefix . '-proxy', [ 'user' => $user ] );
	}

	/**
	 * @param string $method
	 * @param VariableHolder $vars
	 * @param array $parameters
	 * @param string|null &$result
	 * @return bool 計算が完了した場合はfalseを返す
	 */
	public function onAbuseFilter_computeVariable( string $method, VariableHolder $vars, array $parameters, ?string &$result ) {
		$user = $parameters['user'];

		$ip = $user->getRequest()->getIP();

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
}
