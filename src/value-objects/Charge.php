<?php namespace Betalectic\SpaceCharge\ValueObjects;


class Charge 
{

	protected $chargeType;
	protected $chargeUnit;
	protected $currency;
	protected $amount;

	public function __construct($type, $unit, $currency, $amount)
	{

		$this->chargeType = $type;
		$this->chargeUnit = $unit;
		$this->currency = $currency;
		$this->amount = $amount;

	}

}
