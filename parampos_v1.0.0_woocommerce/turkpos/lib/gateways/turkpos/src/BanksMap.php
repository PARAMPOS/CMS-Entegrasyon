<?php
/**
 * Created by Payfull.
 * Date: 10/18/2018
 */

namespace param;


class BanksMap
{
    public static function getPOSId($networkName)
    {
        $posList = [
            'Axess'=>'1014',
            'Bonus'=>'1013',
            'CardFinans'=>'1011',
            'Maximum'=>'1008',
            'Paraf'=>'1012',
            'World'=>'1009',
            'Others'=>'1029',
            'Param'=>'1018',
        ];
        if(isset($posList[$networkName]) == False){
            throw new \Exception('Network not found.');
        }else{
            return $posList[$networkName];
        }
    }
}