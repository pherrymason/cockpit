import {effects, registerEventHandler} from 'reffects';
import {state} from 'reffects-store';
import {http} from 'reffects-batteries';
import {environment} from '../../environment';

export const ASSET_DIALOG_ASSETS_INIT = 'ASSET_DIALOG_ASSETS_INIT';
export const ASSET_DIALOG_ASSETS_REQUESTED = 'ASSET_DIALOG_ASSETS_REQUESTED';
export const ASSET_DIALOG_ASSETS_LOADED = 'ASSET_DIALOG_ASSETS_LOADED';

const DEFAULT_ASSETS_PER_PAGE = 10;
const firstPage = 1;
const defaultAssetDialog = {
    loading: true,
    showMode: 'listMode',
    filter: {},
    page: firstPage,
    pages: 0,
    limit: DEFAULT_ASSETS_PER_PAGE,
    skip: (firstPage - 1) * DEFAULT_ASSETS_PER_PAGE,
    sort: {title: 1},
    folder: '/',
    assets: [],
    selectedAssets: []
};

registerEventHandler(
    ASSET_DIALOG_ASSETS_REQUESTED,
    (
        {environment: {apiEndpoint}, state: {assetsDialog}},
        _
    ) => {

        let dialog = assetsDialog ?? defaultAssetDialog;

        let payload = {
            filter: dialog.filter,
            page: dialog.page,
            limit: dialog.limit,
            skip: dialog.skip,
            sort: dialog.sort,
            folder: dialog.currentFolder
        };

        return {
            ...state.set({assetsDialog: dialog}),
            ...http.post({
                url: `${apiEndpoint}assetsmanager/listAssets`,
                body: payload,
                successEvent: ASSET_DIALOG_ASSETS_LOADED,
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