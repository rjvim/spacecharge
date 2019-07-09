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
			'entity_type' => get_class($entity),
			'capacity' => $capacity,
			'base_price_unit' => $baseCharge->chargeUnit,
			'base_price_amount' => $baseCharge->amount,
			'base_price_currency' => $baseCharge->currency,
			'charge_type' => $baseCharge->chargeType
		]);

		return $space;
	}

	public function setBaseCharge($type, $unit, $currency, $amount)
	{
		$charge = new Charge($type, $unit, $currency, $amount);
		
		return $charge;
	}

	public function createPriceTemplate($name, $basePriceUnit, $chargeType, $incrementType, $incrementValue)
	{
		$priceTemplate = PriceTemplate::create([
			'name' => $name,
			'base_price_unit' => $basePriceUnit,
			'increment_type' => $incrementType,
			'increment_value' => $incrementValue,
			'charge_type' => $chargeType
		]);

		return $priceTemplate;
	}

	public function attachTemplate($spaceId, $templateId, $applicableFrom)
	{
		$pivot = SpacePriceTemplate::create([
			'price_template_id' => $templateId,
			'space_id' => $spaceId,
			'applicable_from' => $applicableFrom
		]);

		return $pivot;
	}

	public function createPriceVariation($priceTemplateId, $incrementValue = 0, $dayOfWeek = null, $monthOfYear = null, $fromTime=null, $toTime = null)
	{

		$data = [];
		$data['price_template_id'] = $priceTemplateId;

		if ($monthOfYear) {
			$data['month_of_year'] = $monthOfYear;
		}

		if ($dayOfWeek) {
			$data['day_of_week'] = $dayOfWeek;
		}

		if ($fromTime) {
			$data['from_time'] = $fromTime;
		}

		if ($toTime) {
			$data['to_time'] = $toTime;
		}

		if ($incrementValue) {
			$data['increment_value'] = $incrementValue;
		}

		$priceVariation = PriceVariation::create($data);

		return $priceVariation;
	}

	public function getPriceVariation($templateId = null)
	{
		$variations = new PriceVariation();

		if ($templateId) {
			$variations = $variations->where('price_template_id', $templateId);
		}

		return $variations->get();

	}

	public function getApplicableTemplate($spaceId, $date = null)
	{	

		if (!$date) {
			$date = date("Y-m-d H:i:s");
		}

		$template = new SpacePriceTemplate();

		$template = $template->where('space_id', $spaceId)
							->where('applicable_from', '<=' , $date)
							->orderBy('applicable_from', 'desc')
							->with(['template' => function($query) {
								$query->with('variations');
							}]);

		return $template->first();

	}

	public function getAllTemplates($withVariations = false)
	{

		$templates = new PriceTemplate();

		if ($withVariations) {
			$templates = $templates->with('variations');
		}

		return $templates->get();

	}

	public function getSpaces($entityType = null, $options = [])
	{
		$spaces = new Space();

		if ($entityType) {
			$spaces = $spaces->where('entity_type', $entityType);
		}

		if (is_array($options)) {
			if (array_key_exists('base_price_unit', $options) && $options['base_price_unit']) {
				$spaces = $space->where('base_price_unit', $options['base_price_unit']);
			}

			if (array_key_exists('ids', $options) && $options['ids']) {
				$spaces = $space->whereIn('id', $options['ids']);
			}

			if (array_key_exists('base_price_currency', $options) && $options['base_price_currency']) {
				$spaces = $space->where('base_price_currency', $options['base_price_currency']);
			}

			if (array_key_exists('charge_type', $options) && $options['charge_type']) {
				$spaces = $space->where('charge_type', $options['charge_type']);
			}			
		}

		return $spaces->get();

	}

	public function getSpaceDetail($spaceId)
	{
		return Space::find($spaceId);
	}

}