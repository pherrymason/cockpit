<collection-entrypreview>

    <style>

        .collection-entrypreview {
            position: fixed;
            top: 0;
            bottom: 0;
            left:0;
            width: 100%;
            background: #fafafa;
            animation-duration: 200ms;
            z-index: 1010;
        }

        .collection-entrypreview .preview-panel {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 500px;
            box-shadow: 0 0 50px rgba(0,0,0,.4);
            border-right: 1px rgba(0, 0, 0, 0.03) solid;
            background: #fafafa;
            z-index: 1;
        }

        .preview-panel > form {
            position: absolute;
            display: flex;
            flex-direction: column;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .preview-panel-header,
        .preview-panel-content,
        .preview-panel-footer {
            padding: 20px;
            box-sizing: border-box;
        }

        .preview-panel-header {
            background: #fff;
        }

        .preview-panel-content {
            flex: 1;
            overflow-y: scroll;
        }

        .iframe-container {
            position: absolute;
            top: 0;
            left: 500px;
            width: calc(100% - 500px);
            height: 100%;
            overflow: scroll;
            z-index: 0;
        }

        .iframe-container iframe {
            background: #fff;
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.22);
            transition: all 400ms;
        }

        iframe[mode="desktop"] {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
        }

        iframe[mode="laptop"] {
            width: 1000px;
            height: 800px;
        }

        iframe[mode="tablet"] {
            width: 768px;
            height: 1024px;
        }

        iframe[mode="phone"] {
            width: 375px;
            height: 667px;
        }

        .preview-mode {
            display: block;
            transition: all 200ms;
            opacity: 0.3;
        }

        .preview-mode-active {
            opacity: 1;
        }

    </style>

    <div class="collection-entrypreview uk-animation-fade">
        <div class="iframe-container uk-flex uk-flex-center uk-flex-middle"><iframe riot-src="{ url }" mode="{ mode }" ref="iframe"></iframe></div>
        <div class="preview-panel uk-animation-slide-left">

            <form class="uk-form" if="{ fields.length }" onsubmit="{ submit }">

                <div class="preview-panel-header">

                    <div class="uk-flex uk-flex-middle">
                        <span class="uk-text-large uk-flex-item-1">{ App.i18n.get('Content Preview') }</span>
                        <a class="uk-text-large" onclick="{ hidePreview }" title="{ App.i18n.get('Close Preview') }"><img class="uk-svg-adjust uk-text-primary" riot-src="{App.base('/assets/app/media/icons/misc/close.svg')}" width="40" height="40" data-uk-svg></a>
                    </div>

                    <div class="uk-margin-small-top uk-flex uk-flex-middle">

                        <div class="uk-form-select uk-margin-right" show="{ App.Utils.count(groups) > 1 }">
                            <span class="uk-text-bold uk-text-uppercase {group && 'uk-text-primary'} ">{ group || App.i18n.get('All') }</span>
                            <select onchange="{toggleGroup}" ref="selectGroup">
                                <option class="uk-text-capitalize" value="">{ App.i18n.get('All') }</option>
                                <option class="uk-text-capitalize" value="{_group}" each="{items,_group in groups}">{ App.i18n.get(_group) }</option>
                            </select>
                        </div>

                        <div class="uk-form-select" if="{ languages.length }">

                            <span class="{lang ? 'uk-text-primary':'uk-text-muted'}">
                                <i class="uk-icon-globe uk-margin-small-right"></i>{ lang ? _.find(languages,{code:lang}).label:'Default' }
                            </span>

                            <select bind="lang">
                                <option value="">{ App.i18n.get('Default') }</option>
                                <option each="{language,idx in languages}" value="{language.code}">{language.label}</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="preview-panel-content">

                    <div class="uk-grid uk-grid-match uk-grid-gutter">

                        <div class="uk-width-1-1" each="{field,idx in fields}" show="{!group || (group == field.group) }" if="{ hasFieldAccess(field.name) }" no-reorder>

                            <div class="uk-panel">

                                <label class="uk-text-bold">
                                    { field.label || field.name }
                                    <span if="{ field.localize }" class="uk-icon-globe" title="{ App.i18n.get('Localized field') }" data-uk-tooltip="pos:'right'"></span>
                                </label>

                                <div class="uk-margin uk-text-small uk-text-muted">
                                    { field.info || ' ' }
                                </div>

                                <div class="uk-margin">
                                    <cp-field type="{field.type || 'text'}" bind="entry.{ field.localize && parent.lang ? (field.name+'_'+parent.lang):field.name }" opts="{ field.options || {} }"></cp-field>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="preview-panel-footer">
                    <div class="uk-grid uk-grid-small uk-flex-center">
                        <div><a class="preview-mode { mode=='desktop' && 'preview-mode-active'}" onclick="{setMode.bind(this, 'desktop')}"><img riot-src="{App.base('/assets/app/media/icons/devices/desktop.svg')}" width="20" height="20" data-uk-svg></a></div>
                        <div><a class="preview-mode { mode=='laptop' && 'preview-mode-active'}" onclick="{setMode.bind(this, 'laptop')}"><img riot-src="{App.base('/assets/app/media/icons/devices/laptop.svg')}" width="20" height="20" data-uk-svg></a></div>
                        <div><a class="preview-mode { mode=='tablet' && 'preview-mode-active'}" onclick="{setMode.bind(this, 'tablet')}"><img riot-src="{App.base('/assets/app/media/icons/devices/tablet.svg')}" width="20" height="20" data-uk-svg></a></div>
                        <div><a class="preview-mode { mode=='phone' && 'preview-mode-active'}" onclick="{setMode.bind(this, 'phone')}"><img riot-src="{App.base('/assets/app/media/icons/devices/phone.svg')}" width="20" height="20" data-uk-svg></a></div>
                    </div>
                </div>

            </form>

        </div>
    </div>


    <script>

        this.mixin(RiotBindMixin);

        var $this = this;

        this.fields = opts.fields;
        this.fieldsidx = opts.fieldsidx;
        this.excludeFields = opts.excludeFields || [];
        this.groups = opts.groups;
        this.languages = opts.languages || [];
        this.entry = opts.entry;
        this.url = opts.url;

        this.mode = 'desktop';
        this.group = '';
        this.lang = '';
        this.$idle = false;

        this.on('mount', function() {

            $this.$cache = JSON.stringify(this.entry);

            this.refs.iframe.addEventListener('load', function() {

                $this.$iframe = $this.refs.iframe.contentWindow;

                $this.$idle = setInterval(_.throttle(function() {

                    var hash = JSON.stringify($this.entry);

                    if ($this.$cache != hash) {
                        $this.$cache = hash;
                        $this.updateIframe();
                    }

                }, 600), 1000);

                $this.updateIframe();
            });

            this.refs.selectGroup.value = this.group;

            document.body.style.overflow = 'hidden';
        });

        setMode(mode) {
            this.mode = mode;
        }

        updateIframe() {
            if (!this.$iframe) return;
            this.$iframe.postMessage({
                entry: this.entry,
                lang: (this.lang || 'default')
            }, '*');
        }

        toggleGroup() {
            this.group = this.refs.selectGroup.value;
        }

        hidePreview() {
            clearInterval(this.$idle);
            document.body.style.overflow = '';
            this.parent.preview = false;
            this.parent.update();
        }

        hasFieldAccess(field) {

            var acl = this.fieldsidx[field] && this.fieldsidx[field].acl || [];

            if (this.excludeFields.indexOf(field) > -1) {
                return false;
            }

            if (field == '_modified' ||
                App.$data.user.group == 'admin' ||
                !acl ||
                (Array.isArray(acl) && !acl.length) ||
                acl.indexOf(App.$data.user.group) > -1 ||
                acl.indexOf(App.$data.user._id) > -1
            ) {
                return true;
            }

            return false;
        }

    </script>


</collection-entrypreview>
