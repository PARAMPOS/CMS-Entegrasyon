<?php
/**
 * Created by Payfull.
 * Date: 11/01/2019
 */

namespace param\paramBasics;

class TP_Ozel_Oran_SK_Guncelle
{
    public $GUID;//Key Belonging to Member Workplace
    public $G;//control and security object
    public $Ozel_Oran_SK_ID;//ID of the list that coming from Special Ratio SK Liste
    public $MO_1;//One installment rate
    public $MO_2;//2nd Installment Rate
    public $MO_3;//3rd Installment Rate
    public $MO_4;//4rd Installment Rate
    public $MO_5;//5rd Installment Rate
    public $MO_6;//6rd Installment Rate
    public $MO_7;//7rd Installment Rate
    public $MO_8;//8rd Installment Rate
    public $MO_9;//9rd Installment Rate
    public $MO_10;//10rd Installment Rate
    public $MO_11;//11rd Installment Rate
    public $MO_12;//12rd Installment Rate

    /**
     * TP_Ozel_Oran_SK_Guncelle constructor.
     * @param $CLIENT_CODE: Terminal ID, It will be forwarded by param.
     * @param $CLIENT_USERNAME: User Name, It will be forwarded by param.
     * @param $CLIENT_PASSWORD: Password, It will be forwarded by param.
     * @param $guid: Key Belonging to Member Workplace
     * @param $Ozel_Oran_SK_ID: ID of the list that coming from Special Ratio SK Liste
     * @param $MO1: One installment rate
     * @param $MO2: 2nd Installment Rate
     * @param $MO3: 3rd Installment Rate
     * @param $MO4: 4rd Installment Rate
     * @param $MO5: 5rd Installment Rate
     * @param $MO6: 6rd Installment Rate
     * @param $MO7: 7rd Installment Rate
     * @param $MO8: 8rd Installment Rate
     * @param $MO9: 9rd Installment Rate
     * @param $MO10: 10rd Installment Rate
     * @param $MO11: 11rd Installment Rate
     * @param $MO12: 12rd Installment Rate
     */
    public function __construct($CLIENT_CODE, $CLIENT_USERNAME , $CLIENT_PASSWORD, $guid, $Ozel_Oran_SK_ID,$MO1,$MO2,$MO3,$MO4,$MO5,$MO6,$MO7,$MO8,$MO9,$MO10,$MO11,$MO12)
    {
        $this->GUID = $guid;
        $this->G = new G($CLIENT_CODE, $CLIENT_USERNAME , $CLIENT_PASSWORD);

        $this->Ozel_Oran_SK_ID = $Ozel_Oran_SK_ID;
        $this->MO_1 = $MO1;
        $this->MO_2 = $MO2;
        $this->MO_3 = $MO3;
        $this->MO_4 = $MO4;
        $this->MO_5 = $MO5;
        $this->MO_6 = $MO6;
        $this->MO_7 = $MO7;
        $this->MO_8 = $MO8;
        $this->MO_9 = $MO9;
        $this->MO_10 = $MO10;
        $this->MO_11 = $MO11;
        $this->MO_12 = $MO12;
    }
}