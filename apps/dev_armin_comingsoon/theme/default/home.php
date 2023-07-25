<!doctype html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo config('app.title'); ?></title>

    <link rel="stylesheet" href="<?php echo $_url ?>assets/css/style.css">
</head>
<body class="rtl">


<div class="home" style="background: linear-gradient(rgba(74,13,187,0.75), rgba(37,39,50,0.71)), url('<?php echo $background ?>') no-repeat;">
    <?php if(isLogin()) { ?>
        <div class="alert-login">
            <?php lang('default.login_to_setting_info'); ?> <br>
             <a href="<?php echo url('panel') ?>" class="btn"> <?php lang('default.login_to_setting'); ?></a>
        </div>
    <?php } ?>

    <div class="text">
        <h2 class="title"><?php echo config('app.title') ?></h2>
        <h4 class="message"><?php echo config('app.description') ?></h4>
    </div>

    <div class="socials">
        <?php if(config('app.twitter')) { ?>
        <a target="_blank" href="<?php echo config('app.twitter'); ?>">
            <img src="<?php echo $_url . 'assets/images/socials/twitter.svg' ?>" alt="twitter">
        </a>
        <?php } ?>

        <?php if(config('app.linkedin')) { ?>

        <a target="_blank" href="<?php echo config('app.linkedin'); ?>">
            <img src="<?php echo $_url . 'assets/images/socials/linkedin.svg' ?>" alt="linkedin">
        </a>
        <?php } ?>

        <?php if(config('app.instagram')) { ?>
        <a target="_blank" href="<?php echo config('app.instagram'); ?>">
            <img src="<?php echo $_url . 'assets/images/socials/instagram.svg' ?>" alt="instagram">
        </a>
        <?php } ?>

        <?php if(config('app.telegram')) { ?>
        <a target="_blank" href="<?php echo config('app.telegram'); ?>">
            <img src="<?php echo $_url . 'assets/images/socials/telegram.svg' ?>" alt="telegram">
        </a>
        <?php } ?>

    </div>
</div>

</body>
</html>