<?php

namespace Betalectic\SpaceCharge;

use Exception;
use DB;

use Betalectic\SpaceCharge\Models\Space;
use Betalectic\SpaceCharge\Models\PriceTemplate;
use Betalectic\SpaceCharge\Models\SpacePriceTemplate;
use Betalectic\SpaceCharge\Models\PriceVariation;
use Betalectic\SpaceCharge\ValueObjects\Charge;

class SpaceCharge
{

	public function __construct()
	{

	}

	public function createSpace($entity, $capacity, $baseCharge)
	{
		$space = Space::create([
			'entity_id' => $entity->getKey(),
			'entity_type' => get_class()
		]);
	}

}