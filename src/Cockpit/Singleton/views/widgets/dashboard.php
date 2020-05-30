<div>

    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header uk-flex uk-flex-middle">
            <strong class="uk-panel-box-header-title uk-flex-item-1">
                <?= $this->lang('Singletons') ?>

                <?php if($this->hasAccess('singletons', 'create')): ?>
                <a href="<?= $this->route('/singletons/singleton') ?>" class="uk-icon-plus uk-margin-small-left" title="<?= $this->lang('Create Singleton') ?>" data-uk-tooltip></a>
                <?php endif; ?>
            </strong>

            <?php if(count($singletons)): ?>
            <span class="uk-badge uk-flex uk-flex-middle"><span><?= count($singletons) ?></span></span>
            <?php endif ?>
        </div>

        <?php if(count($singletons)): ?>

            <div class="uk-margin">

                <ul class="uk-list uk-list-space uk-margin-top">
                    <?php foreach(array_slice($singletons, 0, count($singletons) > 5 ? 5: count($singletons)) as $singleton) :?>
                    <li class="uk-text-truncate">
                        <a class="uk-link-muted" href="<?= $this->route('singleton', ['name' =>$singleton->name()]) ?>">

                            <img class="uk-margin-small-right uk-svg-adjust" src="<?= $this->base(false ? 'assets:app/media/icons/'.$singleton->icon():'singletons:icon.svg')?>" width="18px" alt="icon" data-uk-svg>

                            <?= $this->e($singleton->label()) ?>
                        </a>
                    </li>
                    <?php endforeach ?>
                </ul>

            </div>

            <?php if(count($singletons) > 5): ?>
            <div class="uk-panel-box-footer uk-text-center">
                <a class="uk-button uk-button-small uk-button-link" href="<?= $this->route('/singletons') ?>"><?= $this->lang('Show all') ?></a>
            </div>
            <?php endif ?>

        <?php else: ?>

            <div class="uk-margin uk-text-center uk-text-muted">

                <p>
                    <img src="@url('singletons:icon.svg')" width="30" height="30" alt="Singletons" data-uk-svg />
                </p>

                <?= $this->lang('No singletons') ?>

            </div>

        <?php endif ?>

    </div>

</div>
