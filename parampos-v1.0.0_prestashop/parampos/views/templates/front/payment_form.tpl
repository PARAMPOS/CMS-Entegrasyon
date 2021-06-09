{*
*
*  @author     Param www.param.com.tr
*  @license    http://opensource.org/licenses/MIT MIT
*}
<form action="{$gateway_url|escape:'htmlall':'UTF-8'}" id="payment-form" method='post' onsubmit="return Parampayment_submit();">
    <a name="param"></a>
    {if $isFailed == 1}
        <div class="alert alert-danger"  id="param_error">
            {if !empty($smarty.get.message)}
                {l s='Sorry, your payment failed: ' mod='parampos'}
                {$smarty.get.message|escape:'htmlall':'UTF-8'}
            {else}
                {l s='Error, please verify the card information' mod='parampos'}
            {/if}
        </div>
    {/if}
      
  <div class="form-group row">
    <label for="PARAM_CARDNAME" class="col-md-3 form-control-label required">{l s='Kart sahibi ad soyad' mod='parampos'}</label>
    <div class="col-md-6">
        <input type="text" class="form-control" name="PARAM_CARDNAME" id='PARAM_CARDNAME' size="30" autocomplete="cc-name" />
        <span id="paramcard_error" class="error_msg"></span>
    </div>
  </div>

  <div class="form-group row">
    <label for="PARAM_CARDNUMBER" class="col-md-3 form-control-label required">{l s='Kart Numarası' mod='parampos'}</label>
    <div class="col-md-6">
        <input type="text" class="form-control" name="PARAM_CARDNUMBER" id='PARAM_CARDNUMBER' autocomplete="cc-number" size="30" maxlength="19" pattern="\d*" />
        <span id="paramnumber_error" class="error_msg"></span>
    </div>
  </div>

  <div class="form-group row">
    <label for="PARAM_CARDEXPIRYMONTH" class="col-md-3 form-control-label required">{l s='Son Kullanım Tarihi' mod='parampos'}</label>
    <div class="col-md-2">
        <select class="form-control form-control-select" id="PARAM_CARDEXPIRYMONTH" name="PARAM_CARDEXPIRYMONTH">
            {section name=date_m start=01 loop=13}
                <option value="{$smarty.section.date_m.index|string_format:"%02d"|escape:'htmlall':'UTF-8'}">{$smarty.section.date_m.index|string_format:"%02d"|escape:'htmlall':'UTF-8'}</option>
            {/section}
        </select> 
    </div>
    <div class="col-md-1">/</div>
    <div class="col-md-2">
        <select class="form-control form-control-select" id="PARAM_CARDEXPIRYYEAR" name="PARAM_CARDEXPIRYYEAR">
            {section name=date_y start=21 loop=28}
                <option value="{$smarty.section.date_y.index|escape:'htmlall':'UTF-8'}">{$smarty.section.date_y.index|escape:'htmlall':'UTF-8'}</option>
            {/section}
        </select>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <label class="col-md-3">&nbsp;</label>
        <div class="col-md-9">
            <span id="expiry_error" class="error_msg"></span>
        </div>
    </div>
  </div>

  <div class="form-group row">
    <label for="PARAM_CARDCVN" class="col-md-3 form-control-label required">{l s='Güvenlik Kodu (CVV/CVC)' mod='parampos'}</label>
    <div class="col-md-6">
        <input class="form-control" type="text" name="PARAM_CARDCVN" id="PARAM_CARDCVN" size="4" maxlength="4" autocomplete="cc-csc" pattern="\d*" />
        <span id="paramcvn_error" class="error_msg"></span>
    </div>
  </div>
  {if $installment == 1}
    <div class="form-group row hidden" id ="param_installment_field">
        <label class="col-md-3 form-control-label" for="param-installment">{l s='Taksit Seçimi' mod='parampos'}</label>
        <div class="col-md-6">
            <select name="PARAM_INSTALLMENT" id="PARAM_INSTALLMENT" class="form-control form-control-select">
                <option value="">--Lütfen Seçiniz--</option>
            </select>
        </div>
    </div>
    <div class="clearfix"></div><div class="spinner-border custom-spinner hidden" role="status"><span class="sr-only">Yükleniyor...</span></div>
  {/if}
  <input type='hidden' name='PARAM_ACCESSCODE' value='{$AccessCode|escape:'htmlall':'UTF-8'}' />
  <input type='hidden' name='PARAM_PAYMENTTYPE' value='creditcard' />
</form>

<script type="text/javascript">
    //<!--
    window.onload = function() {
        $(document).ready(function(){
            var requestOn = false;
            $('#PARAM_CARDNUMBER').on('keyup', function() {
                var val = $(this).val().replace(' ', '');
                var installmentUrl = "{$InstallmentUrl}";
                if(val.length >= 6 && requestOn == false) {
                    $('#param_installment_field').addClass('hidden');
                    $('#PARAM_INSTALLMENT').find('option:not(:first)').remove();
                    $.ajax({
                        type: "POST",
                        url: installmentUrl,
                        dataType: 'html',
                        data: { ccnumber : val },
                        beforeSend:function(){
                            requestOn = true;
                            $('.custom-spinner').removeClass('hidden');
                        },
                        complete: function(response) {
                            requestOn = false;
                        },
                        success: function(response) {
                            $('#param_installment_field').removeClass('hidden');
                            $('.custom-spinner').addClass('hidden');
                            var data = $.parseJSON(response);
                            if(Object.keys(data).length > 0){
                            $('#PARAM_INSTALLMENT').find('option:not(:first)').remove();
                            $.each(data, function(index, value) {
                                $('#PARAM_INSTALLMENT').append($('<option>', { 
                                value: index + '|' + value.rate + '|' + value.fee,
                                text : index + ' Taksit - %' + value.rate + ' Komisyon - Genel Toplam ' + value.total_pay
                                }));
                            });
                            } else {
                            $('#param_installment_field').addClass('hidden');
                            }
                            return false;
                            
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        }
                    });
                } else {
                    $('#param_installment_field').addClass('hidden');
                }
            });
        });
    }
    //-->
</script>

<script type="text/javascript">
//<!--
function select_ParamPaymentOption(v) {
    if (document.getElementById("creditcard_info"))
        document.getElementById("creditcard_info").style.display = "none";
    if (document.getElementById("tip_paypal"))
        document.getElementById("tip_paypal").style.display = "none";
    if (document.getElementById("tip_masterpass"))
        document.getElementById("tip_masterpass").style.display = "none";
    if (document.getElementById("tip_vme"))
        document.getElementById("tip_vme").style.display = "none";
    if (v == 'creditcard') {
        document.getElementById("creditcard_info").style.display = "block";
    } else {
        document.getElementById("tip_" + v).style.display = "block";
    }
}

function Parampayment_submit() {
{literal}
		var param_error = false;
		if ($('#PARAM_CARDNAME').val().length < 1) {
			param_error = true;
			$('#paramcard_error').html('Kart sahibi bilgilerini giriniz.');
		} else {
			$('#paramcard_error').empty();
		}

		var ccnum_regex = new RegExp("^[0-9]{13,19}$");
		if (!ccnum_regex.test($('#PARAM_CARDNUMBER').val().replace(/ /g, '')) || !luhn10($('#PARAM_CARDNUMBER').val())) {
			param_error = true;
			$('#paramnumber_error').html('Kart numarası geçersiz.');
		} else {
			$('#paramnumber_error').empty();
		}

		var cc_year = parseInt($('#PARAM_CARDEXPIRYYEAR').val(),10) + 2000;
		var cc_month = parseInt($('#PARAM_CARDEXPIRYMONTH').val(),10);

		var cc_expiry = new Date(cc_year, cc_month, 1);
		var cc_expired = new Date(cc_expiry - 1);
		var today = new Date();

		if (today.getTime() > cc_expired.getTime()) {
			param_error = true;
			$('#expiry_error').html('This expiry date has passed');
		} else {
			$('#expiry_error').empty();
		}

		var ccv_regex = new RegExp("^[0-9]{3,4}$");
		if (!ccv_regex.test($('#PARAM_CARDCVN').val().replace(/ /g, ''))) {
			param_error = true;
			$('#paramcvn_error').html('Güvenlik kodu hatalı.');
		} else {
			$('#paramcvn_error').empty();
		}

		if (param_error) {
			return false;
		}

	$("#payment-form").submit();
	return true;
}

var luhn10 = function(a,b,c,d,e) {
	for(d = +a[b = a.length-1], e=0; b--;)
		c = +a[b], d += ++e % 2 ? 2 * c % 10 + (c > 4) : c;
	return !(d%10)
};
//-->
{/literal}
</script>
