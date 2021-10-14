<!doctype html>
<html lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <title>Ödemeniz <?php echo ($_GET['status'] == 1 ? 'Başarılı' : 'Başarısız'); ?></title>

    <link rel="stylesheet" type="text/css" media="all" href="./css/result.css">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
</head>

<body>

<div id="w">
    <div id="content">
        <?php if ($_GET['status'] == 1): ?>
        <div class="notify successbox">
            <h1>Başarılı!</h1>
            <span class="alerticon"><img width="75" src="img/check.png" alt="checkmark"/></span>

            <p>Ödeme işleminiz için teşekkürler.</p>
        </div>
        <?php else: ?>
        <div class="notify errorbox">
            <h1>Hata!</h1>
            <span class="alerticon"><img width="75" src="img/error.png" alt="error"/></span>
            <p>Ödeme işleminiz başarısız, lütfen tekrar deneyin..</p>
        </div>
        <?php endif; ?>

    </div>

</div>


</body>
</html>