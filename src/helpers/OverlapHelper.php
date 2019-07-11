<?php namespace Betalectic\SpaceCharge\Helpers;


class OverlapHelper {

    public static function isInRange($queryBuilder, $findFrom, $findTo, $rangeFrom, $rangeTo)
    {
        return $queryBuilder->where(function($query) use ($findFrom, $findTo, $rangeFrom, $rangeTo){
            $query->where(function($query) use ($findFrom, $findTo, $rangeFrom, $rangeTo) {
                $query
                ->where($findFrom,'<',$rangeFrom)
                ->where($findTo,'>',$rangeTo);
            })
            ->orWhere(function($query) use ($findFrom, $findTo, $rangeFrom, $rangeTo) {
                $query
                ->where($findFrom,'=',$rangeFrom)
                ->where($findTo,'=',$rangeTo);
            })
            ->orWhere(function($query) use ($findFrom, $findTo, $rangeFrom, $rangeTo) {
                $query
                ->where($findFrom,'=',$rangeFrom)
                ->where($findTo,'<',$rangeTo);
            })
            ->orWhere(function($query) use ($findFrom, $findTo, $rangeFrom, $rangeTo) {
                $query
                ->where($findFrom,'>',$rangeFrom)
                ->where($findTo,'=',$rangeTo);
            });
        });
    }
}