<?php
session_start();

include('helper.php');
include "totalRatio.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Param Ödeme Sayfası">
    <meta name="author" content="Param">

    <title>Param - Ödeme Sayfası</title>

    <!-- Bootstrap core CSS -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

    <script src="./js/jquery-1.10.2.min.js"></script>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="./js/pos.js"></script>
    <script type="text/javascript"
            src="http://cdn.jsdelivr.net/jquery.validation/1.13.1/jquery.validate.min.js"></script>

    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
<script type="text/javascript">
    function paramShow(){

       var data =  $("#hidden-show").attr("data-name");

       if(data == 'show'){
           $("#bir").css("visibility","visible")
           $("#iki").css("visibility","visible")
           $("#uc").css("visibility","visible")
           $("#sifir").css("visibility","visible")
           $("#hidden-show").attr('data-name','hidden')
           $("#hidden-show").text('Taksit Seçeneklerini Gizlemek İçin Tıklayınız')

       }else{
           $("#bir").css("visibility","hidden")
           $("#iki").css("visibility","hidden")
           $("#uc").css("visibility","hidden")
           $("#sifir").css("visibility","hidden")
           $("#hidden-show").attr('data-name','show')
           $("#hidden-show").text('Taksit Seçeneklerini Görmek İçin Tıklayınız')

       }


    }
    $(document).ready(function () {



        $(".oranCol").click(function () {
            $("tr").removeClass("success");
            $("td").removeClass("success");

            $(this).addClass("success");
            $(".toplamLabel").html('Toplam ' + $(this).attr('rel') + ' TL');

            $("#odemetaksit").val($(this).attr('taksit'));
            $("#odemetutar").val($(this).attr('rel'));
            $("#odemetip").val($(this).parent("tr").attr('rel'));
        });
    });

    function validateForm() {
        if ($("#odemetutar").val() == "" || $("#odemetip").val() == "") {
            $(".payment-errors").text("Ödeme Tipini Seçiniz!").show().fadeOut(3000);
            return false;
        }
    }

</script>
<div class="container">
    <div class="header clearfix">
        <h3 class="text-muted"><img src="https://param.com.tr/images/param-logo-v3-mor.svg"></h3>
    </div>

    <div class="row">
        <?php if (isset($_GET['error']) && !empty($_GET['error'])) echo '<div class="alert alert-danger" role="alert">' . urldecode($_GET['error']) . '</div>'; ?>
        <div class="col-xs-12 col-md-12 col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Ödeme Detayları</h3>
                </div>
                <div class="panel-body">
                    <form role="form" id="payment-form" method="POST" action="index.php">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <table class="table">
                                        <tr>
                                            <td>Firma Ad</td>
                                            <td>:</td>
                                            <td><input required class="form-control" type="text" value="Test Firma"
                                                       name="firma_ad"></td>
                                            <!--                                            <td>-->
                                            <? //=$_SESSION['user']['as'];?><!--</td>-->
                                        </tr>
                                        <tr>
                                            <td>Vergi Dairesi</td>
                                            <td>:</td>
                                            <td><input required class="form-control" type="text" value="Test Daire"
                                                       name="vergi_daire"></td>
                                        </tr>
                                        <tr>
                                            <td>Vergi No</td>
                                            <td>:</td>
                                            <td><input required class="form-control" type="text" value="Test Vergi"
                                                       name="vergi_no"></td>
                                        </tr>
                                        <tr>
                                            <td>Fatura Tarihi</td>
                                            <td>:</td>
                                            <td><input required class="form-control" type="text" value="10.10.2022"
                                                       name="fatura_tarih"></td>
                                        </tr>
                                        <tr>
                                            <td>Fatura No</td>
                                            <td>:</td>
                                            <td><input required class="form-control" type="text" value="FT 354344"
                                                       name="fatura_no"></td>
                                        </tr>
                                        <tr>
                                            <td>Açıklama</td>
                                            <td>:</td>
                                            <td><textarea required class="form-control" type="text" name="aciklama">Test Açıklama</textarea>
                                            </td>

                                        </tr>

                                        <tr>
                                            <td>Tutar(10,00 seklinde Virgulle yazınız.)</td>
                                            <td>:</td>
                                            <td><input required class="form-control" type="text" value="10,00"
                                                       name="tutar"></td>
                                        </tr>

                                        <?php
                                        if (!isset($_POST['submit_btn'])) {

                                            ?>
                                            <tr>
                                                <td>
                                                    <button class="btn btn-primary" name="submit_btn">Gönder</button>
                                                </td>
                                            </tr>
                                            <?php
                                        }

                                        ?>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <br>
        </div>

        <?php
        if (isset($_POST['submit_btn'])) {
            ?>
            <div id="tl-field">
                <div class="col-xs-12 col-md-12 col-lg-12">

                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th class="col-sm-1"></th>
                            <th>Banka</th>
                            <th>1 Taksit</th>
                            <th>3 Taksit</th>
                            <th>6 Taksit</th>
                            <th>9 Taksit</th>
                        </tr>
                        </thead>
                        <?php foreach ($oranListesiT->DT_Ozel_Oranlar as $key => $obj): ?>
                            <?php if ($obj->Kredi_Karti_Banka == 'Param'): ?>
                                <tr  class="sanalPosID" rel="<?= $obj->SanalPOS_ID ?>">
                                    <td  class="col-sm-1"><img src="<?= $obj->Kredi_Karti_Banka_Gorsel ?>"></td>
                                    <td ><?= $obj->Kredi_Karti_Banka ?>
                                        <button onclick="paramShow()" id="hidden-show" style="width: 350px" data-name="show" class="btn btn-asd btn-xs">Taksit Seçeneklerini Görmek İçin Tıklayınız</button>

                                    </td>
                                    <td id="sifir" style="visibility: hidden" class="oranCol" taksit="1" rel="<?= $_POST['tutar']; ?>">%0.00<br><span
                                                class="label label-default"><?= $_POST['tutar']; ?> TL</span></td>
                                    <td id="bir" style="visibility: hidden" class="oranCol" taksit="3"
                                        rel="<?= calculateRatio($obj->MO_03); ?>"><?= (floatval($obj->MO_03) > 0 ? '%' . floatval($obj->MO_03) . '<br><span class="label label-default">' . calculateRatio($obj->MO_03) . ' TL</span>' : ''); ?></td>
                                    <td id="iki" style="visibility: hidden" class="oranCol" taksit="6"
                                        rel="<?= calculateRatio($obj->MO_06); ?>"><?= (floatval($obj->MO_06) > 0 ? '%' . floatval($obj->MO_06) . '<br><span class="label label-default">' . calculateRatio($obj->MO_06) . ' TL</span>' : ''); ?></td>
                                    <td id="uc" style="visibility: hidden" class="oranCol" taksit="9"
                                        rel="<?= calculateRatio($obj->MO_09); ?>"><?= (floatval($obj->MO_09) > 0 ? '%' . floatval($obj->MO_09) . '<br><span class="label label-default">' . calculateRatio($obj->MO_09) . ' TL</span>' : ''); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php foreach ($oranListesi->DT_Ozel_Oranlar_SK as $key => $obj): ?>
                            <?php if ($obj->Kredi_Karti_Banka == 'Param'): ?>

                            <?php else: ?>
                                <tr class="sanalPosID" rel="<?= $obj->SanalPOS_ID ?>">
                                    <td class="col-sm-1"><img src="<?= $obj->Kredi_Karti_Banka_Gorsel ?>"></td>
                                    <td><?= $obj->Kredi_Karti_Banka ?></td>
                                    <td class="oranCol" taksit="1" rel="<?= $_POST['tutar']; ?>">%0.00<br><span
                                                class="label label-default"><?= $_POST['tutar']; ?> TL</span></td>
                                    <td class="oranCol" taksit="3"
                                        rel="<?= calculateRatio($obj->MO_03); ?>"><?= (floatval($obj->MO_03) > 0 ? '%' . floatval($obj->MO_03) . '<br><span class="label label-default">' . calculateRatio($obj->MO_03) . ' TL</span>' : ''); ?></td>
                                    <td class="oranCol" taksit="6"
                                        rel="<?= calculateRatio($obj->MO_06); ?>"><?= (floatval($obj->MO_06) > 0 ? '%' . floatval($obj->MO_06) . '<br><span class="label label-default">' . calculateRatio($obj->MO_06) . ' TL</span>' : ''); ?></td>
                                    <td class="oranCol" taksit="9"
                                        rel="<?= calculateRatio($obj->MO_09); ?>"><?= (floatval($obj->MO_09) > 0 ? '%' . floatval($obj->MO_09) . '<br><span class="label label-default">' . calculateRatio($obj->MO_09) . ' TL</span>' : ''); ?></td>
                                </tr>
                            <?php endif; ?>

                        <?php endforeach; ?>


                    </table>
                </div>

                <div class="col-xs-12 col-md-12 col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-6">
                                    <h3 class="panel-title">Ödeme Bilgileri</h3>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <h3 class="panel-title toplamLabel"><?= isset($_POST['tutar']) ? 'Toplam ' . $_POST['tutar'] . 'TL' : '' ?> </h3>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form role="form" id="payment-form" action="payment.php" onsubmit="return validateForm()"
                                  method="post">
                                <input type="hidden" name="odemetutar" value="<?= $_POST['tutar'] ?>" id="odemetutar">
                                <input type="hidden" name="odemetip" value="" id="odemetip">
                                <input type="hidden" name="odemetaksit" value="" id="odemetaksit">
                                <input type="hidden" name="as" value="<?= $_POST['firma_ad'] ?>">
                                <input type="hidden" name="u" value="<?= $_POST['aciklama'] ?>">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label for="cardNumber">Ad Soyad</label>

                                            <div class="input-group">
                                                <input type="text" class="form-control" name="cardName"
                                                       placeholder="Kart Üzerinde Yazan Ad Soyad" value="Test Kartı"
                                                       required MAXLENGTH="25"/>
                                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label for="cardNumber">Kart Numarası</label>

                                            <div class="input-group">
                                                <input type="text" class="form-control" name="cardNumber"
                                                       placeholder="Kart Numaranız" required autofocus
                                                       data-stripe="number" MAXLENGTH="16" value="4022774022774026"/>
                                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-7 col-md-7">
                                        <label for="expMonth">Kartınızın Son kullanım Tarihi</label>
                                        <div class="form-group">

                                            <div class="col-xs-6 col-lg-6 pl-ziro">
                                                <input type="text" class="form-control" name="expMonth" placeholder="MM"
                                                       required data-stripe="exp_month" MAXLENGTH="2" value="12"/>
                                            </div>
                                            <div class="col-xs-6 col-lg-6 pl-ziro">
                                                <input type="text" class="form-control" name="expYear" placeholder="YY"
                                                       required
                                                       data-stripe="exp_year" MAXLENGTH="2" value="26"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-5 col-md-5 pull-right">
                                        <div class="form-group">
                                            <label for="cvCode">Güvenlik Kodu</label>
                                            <input type="password" value="000" class="form-control" name="cvCode"
                                                   placeholder="CV" required
                                                   data-stripe="cvc" MAXLENGTH="3"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <button class="btn btn-success btn-lg btn-block" type="submit">Ödeme İşlemini
                                            Gerçekleştir
                                        </button>
                                    </div>
                                </div>
                                <div class="row" style="">
                                    <div class="col-xs-12">
                                        <p class="payment-errors"></p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>



            </div>


            <?php
        }
        ?>

    </div>

    <footer class="footer">
        <p>&copy; Param</p>
    </footer>

</div>
<!-- /container -->

</body>
</html>

