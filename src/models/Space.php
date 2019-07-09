<?php namespace Betalectic\SpaceCharge\Models;

use Illuminate\Database\Eloquent\Model;
use Betalectic\SpaceCharge\Traits\UUIDTrait;

class Space extends Model 
{
    use UUIDTrait;

	protected $table = "sc_spaces";

    public $guarded = [];

    protected $UUIDCode = 'uuid';

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uniquify();
        });
    }


}
