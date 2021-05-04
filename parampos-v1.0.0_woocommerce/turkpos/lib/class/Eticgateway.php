<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EticGateway
{

    public $name; // unique
    public $full_name;
    public $lib;
    public $active;
    public $params;
    public $method = 'cc';
    public $exists = false;
    public static $gateways;
    public static $api_libs;

    public function __construct($name = false)
    {
        $this->name = $name;

        if (!$this->name)
            return false;

        $fields = EticSql::getRow('spr_gateway', 'name', $this->name);
        if (!is_array($fields))
            return false;
        $this->exists = true;
        foreach ($fields as $k => $v)
            if (!isset($this->{$k}) OR $this->{$k} == NULL)
                $this->{$k} = $v;

//        if (!$full)
//            return true;
        $this->params = json_decode($this->params);
        //$this->lib = EticGateway::$api_libs->{$this->name};
    }

    /* GETS BANKS (spr_gateways table) INTO OBJECT */

    public static function getGateways($only_active = false)
    {
        $query = $only_active ? EticSql::getRows('spr_gateway', 'active', true) : $query = EticSql::getRowsAll('spr_gateway');
        $gateways = array();
        if($query)
        foreach ($query as $gateway)
            $gateways[] = New EticGateway($gateway['name'], true);
        return $gateways;
    }

    /* GET INSTALLMENTS () INTO OBJECT */

    public function getInstallments()
    {
        if (!$this->exists)
            return false;
        $installments = EticSql::getRows('spr_installment', 'gateway', $this->name, null, array('by' => 'vade', 'type' => 'ASC'));
        if (!$installments) {
            $this->_errors [] = "Database query error";
            return false;
        }
        return $installments;
    }

    public function save()
    {
        if ($this->exists)
            return $this->update();
        return $this->add();
    }

    public function delete()
    {
        if (!$this->exists)
            return  Etictools::rwm('Silinmek istenen POS bulunamadı');
        if (EticSql::getRow('spr_installment', 'gateway', $this->name))
            return Etictools::rwm($this->full_name . ' bazı taksit seçenekleri için aktif durumda olduğu için silinemiyor. '
                            . 'Lütfen öncelikle taksit seçeneklerinden ' . $this->full_name . ' içeren taksitleri kaldırınız veya başka bir'
                            . 'POS üzerinden çekilecek şekilde düzenleyiniz.');
        if ($this->getTransactions())
            return Etictools::rwm($this->full_name . ' üzerinden daha önce yapılmış işlemler bulundu.'
                            . ' Güvenliğiniz için silemezsiniz. Dilerseniz pasif hale getirebilirsiniz.');
        EticSql::deleteRow('spr_gateway', 'name', $this->name);
        return Etictools::rwm('Pos silindi', true, 'success');
    }

    public function add()
    {
        if ($this->exists)
            return Etictools::rwm($this->name . ' zaten yüklenmiş. Aynı Pos\'u birden fazla ekleyemezsiniz. (addGateway ER02)');
        if (!isset(EticGateway::$gateways->{$this->name}))
            return Etictools::rwm('Gateway Kodu Bulunamadı. (addGateway ER01) ' . $this->name);
        $def = EticGateway::$gateways->{$this->name};
        $this->name = $def->name;
        $this->full_name = $def->full_name;
        $this->lib = $def->lib;
        $this->active = $def->active;

        $lib = EticGateway::$api_libs->{$this->lib};
        $params = array();
        foreach ($lib->params as $k => $v)
            $params[$k] = null;
        $this->params = json_encode($params);

        unset($this->exists);
        if (EticSql::insertRow('spr_gateway', (array) $this))
            return Etictools::rwm($this->name . ' POS eklendi. Lütfen parametrelerini girip test modundan çıkartınız');
    }

    public function update()
    {
        if (!$this->exists)
            return Etictools::rwm($this->name . ' Bulunamadığı için güncellenemedi. (UpdateGateway ER01))');
        if (!isset(EticGateway::$gateways->{$this->name}))
            return Etictools::rwm('Gateway Kodu Bulunamadı. (UpdateGateway ER02) ' . $this->name);

        $this->params = json_encode($this->params);

        unset($this->exists);
        unset($this->test_mode);
        if (EticSql::updateRow('spr_gateway', (array) $this, 'name', $this->name))
            return true;
    }

    /*
     * @param $params is object child param(s) with name,type and values
     */

    public function createFrom()
    {
        $t = '';
        if (!$this->exists)
            return false;
        $lib = EticGateway::$api_libs->{$this->lib};

        $t .= '<table class="table" style="text-align:left">';
        foreach ($lib->params as $pk => $param):
            if ($param->type == 'text')
                $input = '<input type="text" class="form-control" name="' . $this->name . '[params][' . $pk . ']" '
                    . ' value="'.(isset($this->params->{$pk}) ? $this->params->{$pk} : '').'" />';
            else if ($param->type == 'select') {
                $input = '<select class="form-control" name="' . $this->name . '[params][' . $pk . ']" >';
                foreach ($param->values as $vk => $vv)
                    $input .= '<option value="' . $vk . '" '
                        .((isset($this->params->{$pk}) AND $this->params->{$pk} == $vk) ? 'selected="selected"' : ''). ' >' . $vv . '</option>';
                $input .= '</select>';
            } else
                $input .= 'undefined type';

            $t .= '<tr><td>' . $param->name . '</td><td>' . $input . '</td></tr>';
        endforeach;
        $t .= '</table>';
        return $t;
    }

    public static function getByFamily($family, $active_only = false)
    {
        $gw_list = array();
        foreach (EticGateway::$gateways as $gw):
			if(!json_decode($gw->families))
				continue;
            foreach (json_decode($gw->families) as $f):
                if ($f == $family) {
                    $gw_list[] = $gw->name;
                }
            endforeach;
        endforeach;
        if (!$active_only)
            return $gw_list;

        $active_gateways = array();
        foreach (EticGateway::getGateways(true) as $gwe)
            $active_gateways[] = $gwe->name;
        $gw_list_active = array();
        foreach ($gw_list as $gwa)
            if (in_array($gwa, $active_gateways))
                $gw_list_active[] = $gwa;
        return $gw_list_active;
    }

    public function getTransactions()
    {
        return EticSql::getRows('spr_transaction', 'gateway', $this->name);
    }

}
