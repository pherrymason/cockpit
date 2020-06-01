<!doctype html>
<html lang="<?= $i18n->locale ?>" class="uk-height-1-1" data-base="<?= $this->base('/') ?>" data-route="<?= $this->base('/') ?>" data-locale="{{ $app('i18n')->locale }}">
<head>
    <meta charset="UTF-8">
    <title><?= $this->lang('Authenticate Please!') ?></title>
    <link rel="icon" href="<?= $this->base('/favicon.png') ?>" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <style>

        .login-container {
            width: 420px;
            max-width: 90%;
        }

        .login-dialog {
            box-shadow: 0 30px 75px 0 rgba(10, 25, 41, 0.2);
        }

        .login-image {
            background-image: url(<?= $this->base('assets:app/media/logo.svg')?>);
            background-repeat: no-repeat;
            background-size: contain;
            background-position: 50% 50%;
            height: 80px;
        }

        svg path,
        svg rect,
        svg circle {
            fill: currentColor;
        }

    </style>

    <!--{{ $app->assets($app['app.assets.base'], $app['debug'] ? time() : $app['cockpit/version']) }}-->
    <?php foreach ($pageAssets->assets('scripts') as $script): ?>
        <script src="<?= $this->base($script)?>"></script>
    <?php endforeach; ?>
    <?php foreach (['assets:lib/uikit/js/components/form-password.min.js'] as $script): ?>
        <script src="<?= $this->base($script)?>"></script>
    <?php endforeach; ?>
    <?php foreach ($pageAssets->assets('styles') as $style): ?>
        <link href="<?= $this->base($style) ?>" type="text/css" rel="stylesheet"/>
    <?php endforeach; ?>
    <?php $this->trigger('app.login.header') ?>

</head>
<body class="login-page uk-height-viewport uk-flex uk-flex-middle uk-flex-center">

    <div class="uk-position-relative login-container uk-animation-scale uk-container-vertical-center" role="main" riot-view>

        <form class="uk-form" method="post" action="<?= $this->route('login_check') ?>" onsubmit="{ submit }">

            <div class="uk-panel-space uk-nbfc uk-text-center uk-animation-slide-bottom" if="{$user}">

                <h1 class="uk-h2 uk-text-bold uk-text-truncate"><?= $this->lang('Welcome back!') ?></h1>

                <p>
                    <cp-gravatar email="{ $user.email }" size="80" alt="{ $user.name || $user.user }" if="{$user}"></cp-gravatar>
                </p>

            </div>

            <div id="login-dialog" class="login-dialog uk-panel-box uk-panel-space uk-nbfc" show="{!$user}">

                <div name="header" class="uk-panel-space uk-text-bold uk-text-center">

                    <div class="uk-margin login-image"></div>

                    <h2 class="uk-text-bold uk-text-truncate"><span><?= $appName ?></span></h2>

                    <div class="uk-animation-shake uk-margin-top" if="{ error }">
                        <span class="uk-badge uk-badge-outline uk-text-danger">{ error }</span>
                    </div>
                </div>

                <div class="uk-form-row">
                    <input ref="user" class="uk-form-large uk-width-1-1" type="text" aria-label="<?= $this->lang('Username') ?>" placeholder="<?= $this->lang('Username') ?>" autofocus required>
                </div>

                <div class="uk-form-row">
                    <div class="uk-form-password uk-width-1-1">
                        <input ref="password" class="uk-form-large uk-width-1-1" type="password" aria-label="<?= $this->lang('Password') ?>" placeholder="<?= $this->lang('Password') ?>" required>
                        <a href="#" class="uk-form-password-toggle" data-uk-form-password><?= $this->lang('Show') ?></a>
                    </div>
                </div>

                <div class="uk-margin-large-top">
                    <button class="uk-button uk-button-outline uk-button-large uk-text-primary uk-width-1-1"><?= $this->lang('Authenticate') ?></button>
                </div>
            </div>

            <p class="uk-text-center" if="{!$user}"><a class="uk-button uk-button-link uk-link-muted" href="<?= $this->route('/auth/forgotpassword') ?>"><?= $this->lang('Forgot Password?') ?></a></p>


        </form>

        <?php $this->trigger('app.login.footer') ?>


        <script type="view/script">

            this.error = false;
            this.$user  = null;

            submit(e) {

                e.preventDefault();

                this.error = false;

                App.request('/auth/login', {
                    username: this.refs.user.value,
                    password: this.refs.password.value,
                    csfr : "<?php
                        //$app('csfr')->token('login')
                    ?>"
                }).then(function(data) {

                    if (data && data.success) {

                        this.$user = data.user;

                        setTimeout(function(){
                            App.reroute('/');
                        }, 2000)

                    } else {

                        this.error = '<?= $this->lang("Login failed") ?>';

                        App.$(this.header).addClass('uk-bg-danger uk-contrast');
                        App.$('#login-dialog').removeClass('uk-animation-shake');

                        setTimeout(function(){
                            App.$('#login-dialog').addClass('uk-animation-shake');
                        }, 50);
                    }

                    this.update();

                }.bind(this), function(res) {
                    App.ui.notify(res && (res.message || res.error) ? (res.message || res.error) : 'Login failed.', 'danger');
                });

                return false;
            }

            // i18n for uikit-formPassword
            UIkit.components.formPassword.prototype.defaults.lblShow = '<?= $this->lang("Show") ?>';
            UIkit.components.formPassword.prototype.defaults.lblHide = '<?= $this->lang("Hide") ?>';

        </script>

    </div>

</body>
</html>
