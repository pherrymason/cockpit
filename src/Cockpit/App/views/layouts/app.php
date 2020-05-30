<?php
/**
 * @var \Cockpit\App\UI\MenuItem[] $menuModules
 * @var \Cockpit\Framework\Template\PageAssets $pageAssets
 * @var \Mezzio\Authentication\UserInterface $user
 */
    // Generate title
   $_title = [];
    foreach (explode('/', $route) as $part) {
        if (trim($part)) {
            $_title[] = $i18n->get(ucfirst($part));
        }
    }

/*

    // sort modules by label
    $modules = $app('admin')->data['menu.modules']->getArrayCopy();

    usort($modules, function($a, $b) {
        return mb_strtolower($a['label']) <=> mb_strtolower($b['label']);
    });

    $appConfig = $app->getContainer()->get('app');
    $cmsConfig = $app->getContainer()->get('cms');

    $siteURL = $cmsConfig['host'] . '/' . trim($cmsConfig['base_path'], '/') . '/';
    $publicStorageURL = $cmsConfig['storage_path'];
*/
?><!doctype html>
<html lang="<?= $i18n->locale ?>" data-base="<?= $this->base('/') ?>" data-route="<?= $this->route('/')?>" data-version="{{ $app['cockpit/version'] }}" data-locale="<?= $i18n->locale ?>">
<head>
    <meta charset="UTF-8">
    <title><?= implode(' &raquo; ', $_title).(count($_title) ? ' - ':'').$appName ?></title>
    <link rel="icon" href="<?= $this->base('/favicon.png') ?>" type="image/png">
    <?php /*{{ $app->helper('admin')->favicon('red') }}*/ ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <script>
        // App constants
        var SITE_URL   = '{{ $siteURL }}';
        var ASSETS_URL = '/';
        var PUBLIC_STORAGE_URL = '{{ $publicStorageURL }}';
    </script>
    <?php foreach ($pageAssets->assets('scripts') as $script): ?>
        <script src="<?= $this->base($script)?>"></script>
    <?php endforeach; ?>
    <?php foreach ($pageAssets->assets('styles') as $style): ?>
        <link href="<?= $this->base($style) ?>" type="text/css" rel="stylesheet"/>
    <?php endforeach; ?>
    <script src="<?= $this->route('/cockpit.i18n.data')?>"></script>

    <script>
        App.$data = <?= json_encode($extract) ?>;
        UIkit.modal.labels.Ok = App.i18n.get(UIkit.modal.labels.Ok);
        UIkit.modal.labels.Cancel = App.i18n.get(UIkit.modal.labels.Cancel);
    </script>

    <?= $this->trigger('app.layout.header') ?>
    <?php /*@block('app.layout.header') */ ?>
</head>
<body>

    <div class="app-header" data-uk-sticky="{animation: 'uk-animation-slide-top', showup:true}">

        <div class="app-header-topbar">

            <div class="uk-container uk-container-center">

                <div class="uk-grid uk-flex-middle">

                    <div>

                        <div class="app-menu-container" data-uk-dropdown="delay:400,mode:'click'">

                            <a href="<?= $this->route('home')?>" class="uk-link-muted uk-text-bold app-name-link uk-flex uk-flex-middle">
                                <span class="app-logo"></span>
                                <span class="app-name"><?= $appName ?></span>
                            </a>

                            <div class="uk-dropdown app-panel-dropdown uk-dropdown-close">

                                <?php if(count($menuModules)): ?>
                                <div class="uk-visible-small">
                                    <span class="uk-text-upper uk-text-small uk-text-bold"><?= $this->lang('Modules') ?></span>
                                </div>

                                <ul class="uk-grid uk-grid-match uk-grid-small uk-text-center uk-visible-small uk-margin-bottom">

                                    <?php foreach($menuModules ?? [] as $item): ?>
                                    <li class="uk-width-1-2 uk-width-medium-1-3 uk-grid-margin" data-route="{{ $item['route'] }}">
                                        <a class="uk-display-block uk-panel-box uk-panel-card-hover uk-panel-space {{ (@$item['active']) ? 'uk-bg-primary uk-contrast':'' }}" href="<?= $this->route($item->routeName())?>">
                                            <div class="uk-svg-adjust">
                                                <?php if(preg_match('/\.svg$/i', $item->iconPath())): ?>
                                                <img src="<?= $this->base($item->iconPath())?>" alt="<?= $this->lang($item->label()) ?>" data-uk-svg width="40" height="40" />
                                                <?php else: ?>
                                                <img src="<?= $this->base('assets:app/media/icons/module.svg')?>" alt="<?= $this->lang($item->label()) ?>" data-uk-svg width="40" height="40" />
                                                <?php endif; ?>
                                            </div>
                                            <div class="uk-text-truncate uk-text-small uk-margin-small-top"><?= $this->lang($item->label()) ?></div>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>

                                    <?= $this->trigger('cockpit.menu.modules') ?>

                                </ul>
                                <?php endif; ?>


                                <div>
                                    <span class="uk-text-upper uk-text-small uk-text-bold"><?= $this->lang('System') ?></span>
                                </div>

                                <ul class="uk-grid uk-grid-small uk-grid-width-1-2 uk-grid-width-medium-1-4 uk-text-center">

                                    <li class="uk-grid-margin">
                                        <a class="uk-display-block uk-panel-card-hover uk-panel-box uk-panel-space {{ ($app['route'] == '/cockpit/dashboard') ? 'uk-bg-primary uk-contrast':'' }}" href="<?= $this->route('/cockpit/dashboard')?>">
                                            <div class="uk-svg-adjust">
                                                <img class="uk-margin-small-right inherit-color" src="<?= $this->base('assets:app/media/icons/dashboard.svg') ?>" width="40" height="40" data-uk-svg alt="assets" />
                                            </div>
                                            <div class="uk-text-truncate uk-text-small uk-margin-small-top"><?= $this->lang('Dashboard') ?></div>
                                        </a>
                                    </li>

                                    <li class="uk-grid-margin">
                                        <a class="uk-display-block uk-panel-card-hover uk-panel-box uk-panel-space {{ (strpos($app['route'],'/assetsmanager')===0) ? 'uk-bg-primary uk-contrast':'' }}" href="<?= $this->route('/assetsmanager')?>">
                                            <div class="uk-svg-adjust">
                                                <img class="uk-margin-small-right inherit-color" src="<?= $this->base('assets:app/media/icons/assets.svg') ?>" width="40" height="40" data-uk-svg alt="assets" /> 
                                            </div>
                                            <div class="uk-text-truncate uk-text-small uk-margin-small-top"><?= $this->lang('Assets') ?></div>
                                        </a>
                                    </li>

                                    <?php if ($this->hasAccess('cockpit', 'finder')): ?>
                                    <li class="uk-grid-margin">
                                        <a class="uk-display-block uk-panel-card-hover uk-panel-box uk-panel-space {{ (strpos($app['route'],'/finder')===0) ? 'uk-bg-primary uk-contrast':'' }}" href="<?= $this->route('/finder')?>">
                                            <div class="uk-svg-adjust">
                                                <img class="uk-margin-small-right inherit-color" src="<?= $this->base('assets:app/media/icons/finder.svg') ?>" width="40" height="40" data-uk-svg alt="assets" /> 
                                            </div>
                                            <div class="uk-text-truncate uk-text-small uk-margin-small-top"><?= $this->lang('Finder') ?></div>
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <?php if ($this->hasAccess('cockpit', 'settings')): ?>
                                    <li class="uk-grid-margin">
                                        <a class="uk-display-block uk-panel-box uk-panel-card-hover uk-panel-space {{ (strpos($app['route'],'/settings')===0) ? 'uk-bg-primary uk-contrast':'' }}" href="<?= $this->route('/settings')?>">
                                            <div class="uk-svg-adjust">
                                                <img class="uk-margin-small-right inherit-color" src="<?= $this->base('assets:app/media/icons/settings.svg') ?>" width="40" height="40" data-uk-svg alt="assets" />
                                            </div>
                                            <div class="uk-text-truncate uk-text-small uk-margin-small-top"><?= $this->lang('Settings') ?></div>
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <?php if ($this->hasAccess('cockpit', 'accounts')): ?>
                                    <li class="uk-grid-margin">
                                        <a class="uk-display-block uk-panel-box uk-panel-card-hover uk-panel-space {{ (strpos($app['route'],'/accounts')===0) ? 'uk-bg-primary uk-contrast':'' }}" href="<?= $this->route('/accounts')?>">
                                            <div class="uk-svg-adjust">
                                                <img class="uk-margin-small-right inherit-color" src="<?= $this->base('assets:app/media/icons/accounts.svg') ?>" width="40" height="40" data-uk-svg alt="assets" /> 
                                            </div>
                                            <div class="uk-text-truncate uk-text-small uk-margin-small-top"><?= $this->lang('Accounts') ?></div>
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <?= $this->trigger('cockpit.menu.system') ?>

                                </ul>

                                <?= $this->trigger('cockpit.menu') ?>

                            </div>

                        </div>

                    </div>

                    <div class="uk-flex-item-1" riot-mount>
                        <cp-search></cp-search>
                    </div>

                    <?php if(count($menuModules)): ?>
                    <div class="uk-hidden-small">
                        <ul class="uk-subnav app-modulesbar">
                            <?php foreach($menuModules as $item): ?>
                            <li>
                                <a class="uk-svg-adjust {{ (@$item['active']) ? 'uk-active':'' }}" href="<?= $this->route($item->routeName())?>" title="<?= $this->lang($item->label()) ?>" aria-label="<?= $this->lang($item->label()) ?>" data-uk-tooltip="offset:10">
                                    <?php if(preg_match('/\.svg$/i', $item->iconPath())): ?>
                                    <img src="<?= $this->base($item->iconPath())?>" alt="<?= $this->lang($item->label()) ?>" data-uk-svg width="20px" height="20px" />
                                    <?php else: ?>
                                    <img src="<?= $this->base('assets:app/media/icons/module.svg')?>" alt="<?= $this->lang($item->label()) ?>" data-uk-svg width="20px" height="20px" />
                                    <?php endif; ?>

                                    <?php if($item->active()): ?>
                                    <span class="uk-text-small uk-margin-small-left uk-text-bolder"><?= $this->lang($item->label()) ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div>

                        <div data-uk-dropdown="mode:'click'">

                            <a class="uk-display-block" href="<?= $this->route('/accounts/account')?>" style="width:30px;height:30px;" aria-label="<?= $this->lang('Edit account') ?>" riot-mount>
                                <cp-gravatar email="<?= $user->getDetail('email', 'admin@admin.com') ?>" size="30" alt="<?= $user->getIdentity() ?>"></cp-gravatar>
                            </a>

                            <div class="uk-dropdown uk-dropdown-navbar uk-dropdown-flip">
                                <ul class="uk-nav uk-nav-navbar">
                                    <li class="uk-nav-header uk-text-truncate">{{ $app["user"]["name"] ? $app["user"]["name"] : $app["user"]["user"] }}</li>
                                    <li><a href="<?= $this->route('/accounts/account')?>"><?= $this->lang('Account') ?></a></li>
                                    <li class="uk-nav-divider"></li>
                                        <li class="uk-nav-item-danger"><a href="<?= $this->route('/auth/logout')?>"><?= $this->lang('Logout') ?></a></li>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="app-main" role="main">
        <div class="uk-container uk-container-center">
            <?= $this->trigger('app.layout.contentbefore') ?>
            <?=$this->section('content')?>
            <?= $this->trigger('app.layout.contentafter') ?>
        </div>
    </div>

    <?= $this->trigger('app.layout.footer') ?>
    <?php /*@block('app.layout.footer')*/ ?>

    <!-- RIOT COMPONENTS -->
    <?php foreach($pageAssets->assets('components') as $component): ?>
    <script type="riot/tag" src="<?= $this->base($component) ?>?nc={{ $app['debug'] ? time() : $app['cockpit/version'] }}"></script>
    <?php endforeach; ?>

    <?php /*
    <?php foreach($app('fs')->ls('*.tag', '#config:tags') as $component): ?>
    <script type="riot/tag" src="{{$app->pathToUrl('#config:tags/'.$component->getBasename())}}?nc={{ $app['debug'] ? time() : $app['cockpit/version'] }}"></script>
    <?php endforeach; ?>
    */ ?>

    <?= $this->insert('cockpit::views/_partials/logincheck') ?>

</body>
</html>
