<div>

    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header uk-flex uk-flex-middle">
            <strong class="uk-panel-box-header-title uk-flex-item-1">
                <?= $this->lang('Collections') ?>

                <?php if ($this->hasAccess('collections', 'create')): ?>
                <a href="<?= $this->route('/collections/collection') ?>" class="uk-icon-plus uk-margin-small-left" title="<?= $this->lang('Create Collection') ?>" data-uk-tooltip></a>
                <?php endif;?>
            </strong>
            <?php if(count($collections)): ?>
            <span class="uk-badge uk-flex uk-flex-middle"><span><?= count($collections) ?></span></span>
            <?php endif;?>
        </div>

        <?php if(count($collections)):?>

            <div class="uk-margin">

                <ul class="uk-list uk-list-space uk-margin-top">
                    <?php foreach(array_slice($collections, 0, count($collections) > 5 ? 5: count($collections)) as $col): ?>
                    <li>
                        <div class="uk-grid uk-grid-small">
                            <div class="uk-flex-item-1 uk-text-truncate">
                                <a class="uk-link-muted" href="<?= $this->route('collections_entries', ['name' => $col->name()]) ?>">

                                    <img class="uk-margin-small-right uk-svg-adjust" src="<?= $this->base('assets:collections/icon.svg')?>" width="18px" alt="icon" data-uk-svg>
                                    <?= $this->e($col->label()) ?>
                                </a>
                            </div>
                            <div>
                                <?php if($this->hasAccess($col->name(), 'entries_create')): ?>
                                <a class="uk-text-muted" href="<?= $this->route('/collections/entry') ?>/{{ $col['name'] }}" title="<?= $this->lang('Add entry') ?>" aria-label="<?= $this->lang('Add entry') ?>" data-uk-tooltip="pos:'right'">
                                    <img src="<?= $this->base('assets:app/media/icons/plus-circle.svg') ?>" width="1.2em" data-uk-svg />
                                </a>
                                <?php endif;?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach;?>
                </ul>

            </div>

            <?php if(count($collections) > 5): ?>
            <div class="uk-panel-box-footer uk-text-center">
                <a class="uk-button uk-button-small uk-button-link" href="<?= $this->route('/collections') ?>"><?= $this->lang('Show all') ?></a>
            </div>
        <?php endif; ?>

        <?php else: ?>

            <div class="uk-margin uk-text-center uk-text-muted">

                <p>
                    <img src="<?= $this->base('assets:collections/icon.svg') ?>" width="30" height="30" alt="Collections" data-uk-svg />
                </p>

                <?= $this->lang('No collections') ?>
            </div>

        <?php endif ?>

    </div>

</div>
