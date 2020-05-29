<!doctype html>
<html class="uk-height-1-1" lang="{{ $app('i18n')->locale }}" data-base="<?= $this->base('/')?>" data-route="<?= $this->route('home') ?>">
    <head>
        <meta charset="UTF-8">
        <title><?= $this->lang('Authenticate Please!') ?></title>
        <link rel="icon" href="<?= $this->base('/favicon.png')?>" type="image/png">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <?php
                /*
        <?= $this->assets($app['app.assets.base'], $app['cockpit/version']) }}
        <?= $this->assets(['assets:lib/uikit/js/components/form-password.min.js'], $app['cockpit/version']) ?>
                */
        ?>
        <?php foreach ($scripts as $script): ?>

        <?php endforeach; ?>
        <?php foreach ($styles as $style): ?>
            <link href="<?= $this->base($style) ?>" type="text/css" rel="stylesheet"/>
        <?php endforeach; ?>
    </head>
    <body class="login-page uk-height-viewport uk-flex uk-flex-middle uk-flex-center">
    <?=$this->section('content')?>
    </body>
</html>
