<?php
@session_start();

$refer = 'Param';
if (!$_POST) {
    header('location: index.php?error=' . urlencode('Hatalı istekte bulundunuz!'));
    exit();
} elseif (empty($_POST['cardName'])) {
    header('location: index.php?refer='.$refer.'&error=' . urlencode('Kart üzerinde yazan ad soyad boş geçilemez'));
    exit();
} elseif (empty($_POST['cardNumber']) || strlen(trim($_POST['cardNumber'])) != 16) {
    header('location: index.php?refer='.$refer.'&error=' . urlencode('Kart numaranızı kontrol ediniz'));
    exit();
} elseif (empty($_POST['expMonth']) || strlen(trim($_POST['expMonth'])) != 2) {
    header('location: index.php?refer='.$refer.'&error=' . urlencode('Kartınızın son kullanım ayını kontrol ediniz'));
    exit();
} elseif (empty($_POST['expYear']) || strlen(trim($_POST['expYear'])) != 2) {
    header('location: index.php?refer='.$refer.'&error=' . urlencode('Kartınızın son kullanım yılını kontrol ediniz'));
    exit();
} elseif (empty($_POST['cvCode']) || strlen(trim($_POST['cvCode'])) != 3) {
    header('location: index.php?refer='.$refer.'&error=' . urlencode('Kartınızın güvenlik kodunu kontrol ediniz'));
    exit();
} elseif (empty($_POST['odemetip'])) {
    header('location: index.php?refer='.$refer.'&error=' . urlencode('Ödeme Tipini Seçiniz'));
    exit();
} elseif (empty($_POST['odemetutar']) || floatval($_POST['odemetutar']) == 0) {
    header('location: index.php?refer='.$refer.'&error=' . urlencode('Ödeme tutarınız hatalı'));
    exit();
}