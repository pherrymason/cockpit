import {effects, registerEventHandler} from 'reffects';
import {state} from 'reffects-store';
import {http} from 'reffects-batteries';
import {dispatch} from 'reffects';
import {environment} from '../../environment';
import {changeUploadFolder, configureUpload, createUploader} from './upload';

require('../../../../assets/lib/uikit/js/components/upload.js');

export const ASSET_DIALOG_OPEN = 'ASSET_DIALOG_OPEN';
export const ASSET_DIALOG_ASSETS_INIT = 'ASSET_DIALOG_ASSETS_INIT';
export const ASSET_DIALOG_ASSETS_REQUESTED = 'ASSET_DIALOG_ASSETS_REQUESTED';
export const ASSET_DIALOG_ASSETS_LOADED = 'ASSET_DIALOG_ASSETS_LOADED';
export const ASSET_DIALOG_ASSETS_LOAD_FAILED = 'ASSET_DIALOG_ASSETS_LOAD_FAILED';
export const ASSET_DIALOG_ASSETS_PAGE_CHANGED = 'ASSET_DIALOG_ASSETS_PAGE_CHANGED';

export const ASSET_DIALOG_TOGGLE_SHOWMODE = 'ASSET_DIALOG_TOGGLE_SHOWMODE';
export const ASSET_DIALOG_UPDATE_FILTER = 'ASSET_DIALOG_UPDATE_FILTER';

export const ASSET_DIALOG_SELECT_ASSET = 'ASSET_DIALOG_SELECT_ASSET';
export const ASSET_DIALOG_REMOVE_SELECTED = 'ASSET_DIALOG_REMOVE_SELECTED';
export const ASSETS_DIALOG_REMOVE_COMPLETED = 'ASSETS_DIALOG_REMOVE_COMPLETED';
export const ASSETS_DIALOG_REMOVE_FAILED = 'ASSETS_DIALOG_REMOVE_FAILED';
export const ASSET_DIALOG_EDIT_ASSET = 'ASSET_DIALOG_EDIT_ASSET';
export const ASSET_DIALOG_REMOVE_ASSET = 'ASSET_DIALOG_REMOVE_ASSET';
export const ASSET_DIALOG_SUBMIT = 'ASSET_DIALOG_SUBMIT';
export const ASSET_DIALOG_UPLOAD_FILE = 'ASSET_DIALOG_UPLOAD_FILE';


const DEFAULT_ASSETS_PER_PAGE = 15;
const firstPage = 1;
const defaultAssetDialog = {
    loading: true,
    open: false,
    showMode: 'list',
    filter: {},
    page: firstPage,
    pages: 0,
    currentFolder: {_id: null},
    foldersPath: [],
    limit: DEFAULT_ASSETS_PER_PAGE,
    sort: {title: 1},
    assets: [],
    selectedAssets: []
};


registerEventHandler(ASSET_DIALOG_OPEN, () => {
    return {
        ...state.set({
            'assetsDialog.open': true,
        }),
        //...effects.dispatch(ASSET_DIALOG_ASSETS_INIT)
    }
})

registerEventHandler(
    ASSET_DIALOG_ASSETS_INIT,
    ({state: {assetsDialog}}, {ref}) => {

        if (!assetsDialog) {
            assetsDialog = defaultAssetDialog;
            assetsDialog.uploader = createUploader({
                endpoint: App.route('/assetsmanager/upload'),
                folder: assetsDialog.currentFolder
            });
        }

        return {
            ...state.set({
                assetsDialog: assetsDialog
            }),
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
            skip: (assetsDialog.page - 1) * DEFAULT_ASSETS_PER_PAGE,
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
        {state: {limit}},
        [data]
    ) => {
        return {
            ...state.set({
                'assetsDialog.loading': false,
                'assetsDialog.assets': data.assets,
                'assetsDialog.folders': data.folders,
                'assetsDialog.totalAssets': data.total,
                'assetsDialog.pages': Math.ceil(data.total / limit),
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

registerEventHandler(ASSET_DIALOG_REMOVE_SELECTED,
    ({state: {selectedAssets}, environment: {apiEndpoint}}) => {
        return {
            ...http.post({
                url: `${apiEndpoint}assetsmanager/removeAssets`,
                body: {assets: selectedAssets},
                successEvent: ASSETS_DIALOG_REMOVE_COMPLETED,
                errorEvent: ASSETS_DIALOG_REMOVE_FAILED
            })
        }
    },
    [
        environment(),
        state.get({selectedAssets: 'assetsDialog.selectedAssets'})
    ]);


registerEventHandler(ASSETS_DIALOG_REMOVE_COMPLETED,
    () => {

        return {
            ...state.set({
                'assetsDialog.selectedAssets': [],
            })
        }
    });

registerEventHandler(ASSETS_DIALOG_REMOVE_FAILED, () => {});

registerEventHandler(ASSET_DIALOG_EDIT_ASSET, (_, {asset}) => {
    return {
        ...state.set({
            'assetsDialog.showMode': 'edit',
            'assetEditor.asset': asset
        })
    }
})

registerEventHandler(ASSET_DIALOG_REMOVE_ASSET,
    ({environment: {apiEndpoint}}, {asset}) => {

        return {
            ...http.post({
                url: `${apiEndpoint}assetsmanager/removeAssets`,
                body: {assets: [asset]},
                successEvent: ASSETS_DIALOG_REMOVE_COMPLETED,
                errorEvent: ASSETS_DIALOG_REMOVE_FAILED
            })
        };
    }, [environment()]);

registerEventHandler(ASSET_DIALOG_ASSETS_PAGE_CHANGED, (_, {page}) => {
    return {
        ...state.set({
            'assetsDialog.page': page
        }),
        ...effects.dispatch(ASSET_DIALOG_ASSETS_REQUESTED)
    }
});

registerEventHandler(ASSET_DIALOG_SUBMIT,
    ({state: {selectedAssets}}, {callback}) => {
        callback(selectedAssets);
        return {
            ...state.set({
                'assetsDialog.open': false,
            })
        }
    },
    [state.get({selectedAssets: 'assetsDialog.selectedAssets'})]);

registerEventHandler(
    ASSET_DIALOG_UPLOAD_FILE,
    ({state: {uploader}}, {files}) => {
        console.log(files);
        for (const file of files) {
            uploader.addFile({
                name: file.name,
                type: file.type,
                data: file
            });
        }

        uploader.upload().then(
            (result) => {
                if (result.failed.length > 0) {
                    console.error(result.failed);
                } else {
                    dispatch(ASSET_DIALOG_ASSETS_REQUESTED);
                }
                /*
            console.info('Successful uploads:', result.successful);

            if (result.failed.length > 0) {
                console.error('Errors:');
                result.failed.forEach((file) => {
                    console.error(file.error);
                });
            }*/
        }, (error) => {
                console.error('UPload failed:', error);
            });
    },
    [state.get({uploader: 'assetsDialog.uploader'})]
)