<style>
<?php if($collection['color']): ?>
.app-header { border-top: 8px <?= $collection['color'] ?> solid; }
<?php endif ?>
</style>
<script>

function CollectionHasFieldAccess(field) {

    var acl = field.acl || [];

    if (field.name == '_modified' ||
        App.$data.user.group == 'admin' ||
        !acl ||
        (Array.isArray(acl) && !acl.length) ||
        acl.indexOf(App.$data.user.group) > -1 ||
        acl.indexOf(App.$data.user._id) > -1
    ) { return true; }

    return false;
}

</script>

<script type="riot/tag" src="<?= $this->base('assets:collections/entries-batchedit.tag') ?>"></script>

<div>

    <ul class="uk-breadcrumb">
        <li><a href="<?= $this->route('collections') ?>"><?= $this->lang('Collections') ?></a></li>
        <li class="uk-active" data-uk-dropdown="mode:'hover', delay:300">

            <a><i class="uk-icon-bars"></i> <?= $this->e($collection['label']) ?></a>

            <?php if($this->hasAccess($collection['name'], 'collection_edit')): ?>
            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header"><?= $this->lang('Actions') ?></li>
                    <li><a href="<?= $this->route('collections_collection', ['name' => $collection['name']]) ?>"><?= $this->lang('Edit') ?></a></li>
                    <?php if($this->hasAccess($collection['name'], 'entries_delete')): ?>
                    <li class="uk-nav-divider"></li>
                    <li><a href="<?= $this->route('collections_trash_collection', ['name' => $collection['name']]) ?>"><?= $this->lang('Trash') ?></a></li>
                    <?php endif ?>
                    <li class="uk-nav-divider"></li>
                    <li class="uk-text-truncate"><a href="<?= $this->route('/collections/export/'.$collection['name']) ?>" download="{{ $collection['name'] }}.collection.json"><?= $this->lang('Export entries') ?></a></li>
                    <li class="uk-text-truncate"><a href="<?= $this->route('/collections/import/collection/'.$collection['name']) ?>"><?= $this->lang('Import entries') ?></a></li>
                </ul>
            </div>
            <?php endif ?>

        </li>
    </ul>

</div>

<?= $this->insert('collections::views/partials/entries'.($collection['sortable'] ? '.sortable':''), compact('collection')) ?>
