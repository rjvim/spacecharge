<?php namespace Betalectic\SpaceCharge\ValueObjects;


class Charge 
{

	public $chargeType;
	public $chargeUnit;
	public $currency;
	public $amount;

	public function __construct($type, $unit, $currency, $amount)
	{

		$this->chargeType = $type;
		$this->chargeUnit = $unit;
		$this->currency = $currency;
		$this->amount = $amount;

	}

}
