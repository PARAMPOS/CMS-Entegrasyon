{*
*
*  @author     Param www.param.com.tr
*  @license    http://opensource.org/licenses/MIT MIT
*}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="{$module_dir|escape:'htmlall':'UTF-8'}views/css/param.css">
<link rel="stylesheet" type="text/css" href="{$module_dir|escape:'htmlall':'UTF-8'}views/css/parampos.css">
<link rel="stylesheet" type="text/css" href="{$module_dir|escape:'htmlall':'UTF-8'}views/css/front.css">

<div class= "row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="bankwire" href="javascript:toggleform();" title="{$credit_card}">
                <span>{$credit_card}</span>
            </a>
        </p>

    <div class="col-xs-12">
<div id="param-form" class="" style="display: none;">

    <div action="{$gateway_url|escape:'htmlall':'UTF-8'}" id="payment-form" method='post' onsubmit="return Parampayment_submit();">
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


        {capture name="capture_name"}{$InstallmentUrl}{/capture}

        <form action="{$gateway_url|escape:'htmlall':'UTF-8'}" id="payment-form" method='post' onsubmit="return Parampayment_submit();">


        <div class="hepsi">

            <div class="demo-container">

                <div class="form-group active parampos">

                    <div class="paramposname paramposfull" id="">
                        <input class="c-card card-name" placeholder="Kart İsim Soyisim" autocomplete="off" type="text" required  id="PARAM_CARDNAME" oninput="setCustomValidity('')" name="PARAM_CARDNAME" >
                        <span id="paramcard_error" class="error_msg"></span>
                    </div>


                    <div class="paramposcard paramposorta">
                        <i class="paramposcardicon"></i>
                        <input id="PARAM_CARDNUMBER" class="c-card cardnumber"  autocomplete="off" onkeyup="return taksit_field();" placeholder="Kart Numarası" required   oninvalid="this.setCustomValidity('Kartın üzerindeki 16 haneli numarayı giriniz.')" type="tel" name="PARAM_CARDNUMBER" >
                    </div>


                    <div class="paramposleft paramposexpry">
                        <div style="float:left; width: 50%">
                            <input class="c-date c-card"  placeholder="AA"  autocomplete="off" type="tel" maxlength="2" required  oninvalid="this.setCustomValidity('Kartın son kullanma tarihini giriniz')" oninput="setCustomValidity('')" name="PARAM_CARDEXPIRYMONTH" >
                        </div>
                        <div style="float:left; width: 50%">
                            <input  style="" class="c-date c-card"  placeholder="YY"  autocomplete="off" type="tel" maxlength="2" required  oninvalid="this.setCustomValidity('Kartın son kullanma tarihini giriniz')" oninput="setCustomValidity('')" name="PARAM_CARDEXPIRYYEAR" >
                        </div>

                    </div>



                    <div class="paramposright paramposcvc">
                        <input class="card-cvc c-card" placeholder="CVC"  autocomplete="off" required  type="number"  oninvalid="this.setCustomValidity('Kartın arkasındaki 3 ya da 4 basamaklı sayıyı giriniz')" oninput="setCustomValidity('')" name="PARAM_CARDCVN" >

                    </div>

                </div>

            </div>


            <div class="form-group row hidden" id ="param_installment_field">
                <label class="col-md-3 form-control-label" for="param-installment">{l s='Taksit Seçimi' mod='parampos'}</label>
                <div class="col-md-9">
                    <select name="PARAM_INSTALLMENT" id="PARAM_INSTALLMENT" class="form-control form-control-select" style="width: ;">
                        <option style="width: ;" class="paramposname paramposfull" value="">--Lütfen Seçiniz--</option>
                    </select>
                </div>
            </div>
            <div class="clearfix"></div><div class="spinner-border custom-spinner hidden" role="status"><span class="sr-only">Yükleniyor...</span></div>

            <input type='hidden' name='PARAM_ACCESSCODE' value='{$AccessCode|escape:'htmlall':'UTF-8'}' />
            <input type='hidden' name='PARAM_PAYMENTTYPE' value='creditcard' />
            <button type="submit" class="paramposode" href="javascript:;" style=""><span class="paramposOdemeTutar">{$total}</span><span class="paramposOdemeText">ÖDE</span></button>

            {capture name="capture_name"}{$InstallmentUrl}{/capture}
    </div>
        </form>
    </div>
</div></div>  </div>
<script type="text/javascript">
    //<!--
    function taksit_field() {

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

    function toggleform() {
        var ele = document.getElementById("param-form");

        if (ele.style.display == "block") {
            ele.style.display = "none";
        } else {
            ele.style.display = "block";
        }
    }
</script>
<style>div.selector {
        width: 100% !important;
    }</style>