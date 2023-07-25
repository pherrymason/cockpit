import {effects, registerEventHandler} from 'reffects';
import {state} from 'reffects-store';
import {http} from 'reffects-batteries';
import {dispatch} from 'reffects';
import {environment} from '../../environment';

require('../../../../assets/lib/uikit/js/components/upload.js');

export const ASSET_DIALOG_ASSETS_INIT = 'ASSET_DIALOG_ASSETS_INIT';
export const ASSET_DIALOG_ASSETS_REQUESTED = 'ASSET_DIALOG_ASSETS_REQUESTED';
export const ASSET_DIALOG_ASSETS_LOADED = 'ASSET_DIALOG_ASSETS_LOADED';
export const ASSET_DIALOG_ASSETS_LOAD_FAILED = 'ASSET_DIALOG_ASSETS_LOAD_FAILED';
export const ASSET_DIALOG_TOGGLE_SHOWMODE = 'ASSET_DIALOG_TOGGLE_SHOWMODE';
export const ASSET_DIALOG_UPDATE_FILTER = 'ASSET_DIALOG_UPDATE_FILTER';

export const ASSET_DIALOG_SELECT_ASSET = 'ASSET_DIALOG_SELECT_ASSET';


const DEFAULT_ASSETS_PER_PAGE = 15;
const firstPage = 1;
const defaultAssetDialog = {
    loading: true,
    showMode: 'list',
    filter: {},
    page: firstPage,
    pages: 0,
    currentFolder: {_id:null},
    foldersPath: [],
    limit: DEFAULT_ASSETS_PER_PAGE,
    skip: (firstPage - 1) * DEFAULT_ASSETS_PER_PAGE,
    sort: {title: 1},
    assets: [],
    selectedAssets: []
};

function configureUpload(assetsDialogElm, assetsDialog) {
    var uploadSettings = {
            action: App.route('/assetsmanager/upload'),
            type: 'json',
            before: function(options) {
                options.params.folder = assetsDialog.currentFolder._id
            },
            loadstart: function() {
                console.log('LOAD START');
                //$this.refs.uploadprogress.classList.remove('uk-hidden');
            },
            progress: function(percent) {
                percent = Math.ceil(percent) + '%';
                console.log(percent+'%');
                //$this.refs.progressbar.innerHTML   = '<span>'+percent+'</span>';
                //$this.refs.progressbar.style.width = percent;
            },
            allcomplete: function(response) {
                console.log('COMPLETE');
                //$this.refs.uploadprogress.classList.add('uk-hidden');
                if (response && response.failed && response.failed.length) {
                    App.ui.notify("File(s) failed to upload.", "danger");
                }

                if (response && Array.isArray(response.assets) && response.assets.length) {
                    if (!Array.isArray($this.assets)) {
                        $this.assets = [];
                    }

                    App.ui.notify("File(s) uploaded.", "success");
                    response.assets.forEach(function(asset){
                        $this.assets.unshift(asset);
                    });

                    //$this.listAssets(1);
                    dispatch(ASSET_DIALOG_ASSETS_REQUESTED);
                }

                if (!response) {
                    App.ui.notify("Something went wrong.", "danger");
                }
            }
        },

        uploadselect = UIkit.uploadSelect(App.$('.js-upload-select', assetsDialogElm)[0], uploadSettings),
        uploaddrop   = UIkit.uploadDrop(assetsDialogElm, uploadSettings);

    UIkit.init(assetsDialogElm);
}

registerEventHandler(
    ASSET_DIALOG_ASSETS_INIT,
    ({state:{assetsDialog}},{ref}) => {

        if (!assetsDialog) {
            assetsDialog = defaultAssetDialog;
            configureUpload(ref.current, assetsDialog)
        }

        return {
            ...state.set({assetsDialog: assetsDialog}),
            ...effects.dispatch({id: ASSET_DIALOG_ASSETS_REQUESTED})
        }
    },
    [
        state.get({
            assetsDialog: 'assetsDialog',
        })
    ]
)

registerEventHandler(
    ASSET_DIALOG_ASSETS_REQUESTED,
    (
        {environment: {apiEndpoint}, state: {assetsDialog}},
        _
    ) => {
        let payload = {
            filter: assetsDialog.filter,
            page: assetsDialog.page,
            limit: assetsDialog.limit,
            skip: assetsDialog.skip,
            sort: assetsDialog.sort,
            folder: assetsDialog.currentFolder._id,
        };

        if (assetsDialog.currentFolder._id) {
            payload.filter.folder = assetsDialog.currentFolder._id;
        } else {
            delete payload.filter.folder;
        }

        return {
            ...http.post({
                url: `${apiEndpoint}assetsmanager/listAssets`,
                body: payload,
                successEvent: ASSET_DIALOG_ASSETS_LOADED,
                errorEvent: ASSET_DIALOG_ASSETS_LOAD_FAILED,
            })
        }
    },

    [
        environment(),
        state.get({
            assetsDialog: 'assetsDialog',
        })]
);

registerEventHandler(
    ASSET_DIALOG_ASSETS_LOADED,
    (
        {state:{limit}},
        [data]
    ) => {
        return {
            ...state.set({
                'assetsDialog.loading': false,
                'assetsDialog.assets': data.assets,
                'assetsDialog.folders': data.folders,
                'assetsDialog.totalAssets': data.total,
                'assetsDialog.pages': Math.ceil(data.total/limit),
            })
        }
    },
    [state.get({limit: 'assetsDialog.limit'})]
);
registerEventHandler(
    ASSET_DIALOG_ASSETS_LOAD_FAILED,
    (
        _,
        [{message, error}]
    ) => {
        App.ui.notify(res && (res.message || res.error) ? (res.message || res.error) : 'Loading failed.', 'danger');
        return {};
    }
);

registerEventHandler(ASSET_DIALOG_TOGGLE_SHOWMODE,
    ({state: {showMode}}, _) => {
        const newMode = (showMode == 'list' ? 'grid' : 'list');
        return {
            ...state.set({
                'assetsDialog.showMode': newMode,
            })
        }
    }, [state.get({showMode: 'assetsDialog.showMode'})]
);

registerEventHandler(ASSET_DIALOG_UPDATE_FILTER, () => {
   let filter = {};
});

registerEventHandler(
    ASSET_DIALOG_SELECT_ASSET,
    ({state: {selectedAssets}}, {asset}) => {
    var idx = selectedAssets.findIndex((a) => a._id == asset._id);
    let copy = structuredClone(selectedAssets);

    if (idx == -1) {
        copy.push(asset);
    } else {
        copy.splice(idx, 1);
    }

    return {
        ...state.set({
            'assetsDialog.selectedAssets': copy,
        })
    }
}, [
    state.get({selectedAssets: 'assetsDialog.selectedAssets'})
]);