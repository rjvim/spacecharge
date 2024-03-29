<?php namespace Betalectic\SpaceCharge\Models;

use Illuminate\Database\Eloquent\Model;

class SpacePriceTemplate extends Model 
{
	
	protected $table = "sc_space_price_templates";

    public $guarded = [];

    public function template()
    {
    	return $this->belongsTo(PriceTemplate::class, 'price_template_id');
    }

}
