<!doctype html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php lang('default.setting_page'); ?></title>

    <link rel="stylesheet" href="<?php echo $_url ?>assets/css/style.css">
</head>
<body class="rtl panel-container">

<div class="panel">

    <h2><?php lang('default.setting_page'); ?></h2>
    <form class="form" method="post" action="<?php echo url('panel'); ?>">
        <label><?php lang('default.title'); ?></label>
        <input name="title" type="text" placeholder="<?php lang('default.title'); ?>" value="<?php echo config('app.title'); ?>">
        <label><?php lang('default.description'); ?></label>
        <textarea name="description" id="" cols="30" rows="5" placeholder="<?php lang('default.description'); ?>"><?php echo config('app.description'); ?></textarea>

        <label><?php lang('default.twitter'); ?></label>
        <input name="twitter" type="text" placeholder="<?php lang('default.twitter'); ?>" value="<?php echo config('app.twitter'); ?>">
        <label><?php lang('default.instagram'); ?></label>
        <input name="instagram" type="text" placeholder="<?php lang('default.instagram'); ?>" value="<?php echo config('app.instagram'); ?>">
        <label><?php lang('default.telegram'); ?></label>
        <input name="telegram" type="text" placeholder="<?php lang('default.telegram'); ?>" value="<?php echo config('app.telegram'); ?>">
        <label><?php lang('default.linkedin'); ?></label>
        <input name="linkedin" type="text" placeholder="<?php lang('default.linkedin'); ?>" value="<?php echo config('app.linkedin'); ?>">

        <a href="<?php echo url(); ?>" class="btn white"><?php lang('default.back'); ?></a>
        <button type="submit"  class="btn"><?php lang('default.save'); ?></button>
    </form>
</div>

</body>
</html>