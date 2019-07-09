<?php namespace Betalectic\SpaceCharge\Models;

use Illuminate\Database\Eloquent\Model;
use Betalectic\SpaceCharge\Traits\UUIDTrait;

class PriceTemplate extends Model 
{
    use UUIDTrait;

	protected $table = "sc_price_templates";

    public $guarded = [];

    protected $UUIDCode = 'uuid';

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uniquify();
        });
    }

    public function variations()
    {
        return $this->hasMany(PriceVariation::class, 'price_template_id');
    }

}
