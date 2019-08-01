<?php

namespace Betalectic\SpaceCharge;

use Exception;
use DB;

use Betalectic\SpaceCharge\Models\Space;
use Betalectic\SpaceCharge\Models\PriceTemplate;
use Betalectic\SpaceCharge\Models\SpacePriceTemplate;
use Betalectic\SpaceCharge\Models\PriceVariation;
use Betalectic\SpaceCharge\ValueObjects\Charge;
use Betalectic\SpaceCharge\Helpers\OverlapHelper;

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
			'capacity' => ($capacity) ? $capacity : 1,
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

	public function attachTemplate(Space $space, PriceTemplate $template, $applicableFrom = null)
	{

		if ($space->charge_type == $template->charge_type) {

			if ( !$applicableFrom ) {
				$applicableFrom = date("Y-m-d H:i:s");
			}

			$pivot = SpacePriceTemplate::create([
				'price_template_id' => $template->id,
				'space_id' => $space->id,
				'applicable_from' => $applicableFrom
			]);

			return $pivot;
		}

		return false;

	}

	public function createPriceVariation($priceTemplateId, $incrementValue = 0, $dayOfWeek = null, $monthOfYear = null, $fromTime=null, $toTime = null)
	{

		$data = [];
		$data['price_template_id'] = $priceTemplateId;

		if (!is_null($monthOfYear)) {
			$data['month_of_year'] = $monthOfYear;
		}

		if (!is_null($dayOfWeek)) {
			$data['day_of_week'] = $dayOfWeek;
		}

		if (!is_null($fromTime)) {
			$data['from_time'] = $fromTime;
		}

		if (!is_null($toTime)) {
			$data['to_time'] = $toTime;
		}

		if (!is_null($incrementValue)) {
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
							
		return ($template->first()) ? $template->first()->template : null;

	}

	public function getAllTemplates($withVariations = false)
	{

		$templates = new PriceTemplate();

		if ($withVariations) {
			$templates = $templates->with('variations');
		}

		return $templates->orderBy('created_at', "desc")->get();

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

	public function getApplicablePrice(Space $space, $date, $fromTime, $toTime)
	{
		$price = [];

		$numberOfHours = $this->getNumberOfHours($fromTime, $toTime);

		if ($space->charge_type == 'per-seat') {
			$chargeMultiplier = $space->capacity;
		} else {
			$chargeMultiplier = 1;
		}

		$template = $this->getApplicableTemplate($space->id, $date);

		if (!$template) {
			$price['charge_type'] = $space->charge_type;
			$price['charge_unit'] = $space->base_price_unit;
			$price['price_to_charge'] = $this->finalPrice($space->base_price_amount, $numberOfHours, $chargeMultiplier);
			$price['currency'] = $space->base_price_currency;
			$price['total_hours'] = $numberOfHours;

			return $price;
		} else {

			$variations = $this->getPriceVariation($template->id);
			if (!$variations) {

				$priceToCharge = $this->calculateIncrement($space->base_price_amount, $template->increment_type, $template->increment_value);

				$price['charge_type'] = $template->charge_type;
				$price['charge_unit'] = $template->base_price_unit;
				$price['price_to_charge'] = $this->finalPrice($priceToCharge, $numberOfHours, $chargeMultiplier);
				$price['currency'] = $space->base_price_currency;
				$price['total_hours'] = $numberOfHours;
				
				return $price;
			} else {

				$monthOfYear = date("m", strtotime($date));
				$dayOfWeek = date("w", strtotime($date));

				$overlapManager = new OverlapHelper();

				$applicableVariation = PriceVariation::where('price_template_id', $template->id)
													->whereNotNull('from_time')
													->whereNotNull('to_time');

				$applicableVariation = $applicableVariation->where(function ($query) use($overlapManager, $monthOfYear, $dayOfWeek, $fromTime, $toTime) {
					$query->where(function($query) use($overlapManager, $monthOfYear, $dayOfWeek, $fromTime, $toTime) {
						$query->where('month_of_year', $monthOfYear)->where('day_of_week', $dayOfWeek);
					})->orWhere(function($query) use($monthOfYear, $dayOfWeek) {
						$query->where('month_of_year', $monthOfYear)->whereNull('day_of_week');
					})->orWhere(function($query) use($monthOfYear, $dayOfWeek) {
						$query->where('day_of_week', $dayOfWeek)->whereNull('month_of_year');
					})->orWhere(function($query){
						$query->whereNull('day_of_week')->whereNull('month_of_year');
					});
				});

				$applicableVariation = $overlapManager->isInRange($applicableVariation, "from_time", "to_time", $fromTime, $toTime);

				$applicableVariation = $applicableVariation->first();

				if ($applicableVariation) {
					$priceToCharge = $this->calculateIncrement($space->base_price_amount, $template->increment_type, $applicableVariation->increment_value);
				} else {
					$priceToCharge = $this->calculateIncrement($space->base_price_amount, $template->increment_type, $template->increment_value);
				}

				$price['charge_type'] = $template->charge_type;
				$price['charge_unit'] = $template->base_price_unit;
				$price['price_to_charge'] = $this->finalPrice($priceToCharge, $numberOfHours, $chargeMultiplier);
				$price['currency'] = $space->base_price_currency;
				$price['total_hours'] = $numberOfHours;
				
				return $price;
			}

		}


	}

	public function getNumberOfHours($fromTime, $toTime) 
	{
		$difference = round((strtotime($toTime) - strtotime($fromTime))/3600, 1);

		return $difference;
	}

	public function calculateIncrement($basePrice, $incrementType, $incrementValue)
	{

		if ($incrementType == 'percentage') {

			$priceToAdd = ($basePrice * $incrementValue)/100;
			$newPrice = $basePrice + $priceToAdd;

			return $newPrice;

		} else if($incrementValue == 'amount'){

			$priceToAdd = $incrementValue;
			$newPrice = $basePrice + $priceToAdd;

			return $newPrice;
		}

	}


	public function getTemplateDetail($templateId)
	{
		return PriceTemplate::find($templateId);
	}

	public function removeTemplateVariations($templateId)
	{
		$deleted = PriceVariation::where('price_template_id', $templateId)->delete();

		return $deleted;
	}

	public function removeAttachedTemplate(Space $space, PriceTemplate $template)
	{

		$pivot = SpacePriceTemplate::where([
			'price_template_id' => $template->id,
			'space_id' => $space->id,
		])->delete();

		return $pivot;

	}

	public function finalPrice($perHourPrice, $numberOfHours, $capacity)
	{
		$finalPrice = $perHourPrice * $numberOfHours * $capacity;

		return $finalPrice;
	}

}
