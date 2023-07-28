import {effects, registerEventHandler} from 'reffects';
import {state} from 'reffects-store';
import {http} from 'reffects-batteries';
import {dispatch} from 'reffects';
import {environment} from '../../environment';
import {ASSET_DIALOG_ASSETS_REQUESTED} from "../AssetsManager/events";

export const ASSET_EDITOR_FIELD_CHANGED = 'ASSET_EDITOR_FIELD_CHANGED';
export const ASSET_EDITOR_FOLDER_CHANGED = 'ASSET_EDITOR_FOLDER_CHANGED';
export const ASSET_EDITOR_SAVE = 'ASSET_EDITOR_SAVE';
export const ASSET_EDITOR_SAVED = 'ASSET_EDITOR_SAVED';
export const ASSET_EDITOR_SAVE_FAILED = 'ASSET_EDITOR_SAVE_FAILED';
export const ASSET_EDITOR_CANCEL = 'ASSET_EDITOR_CANCEL';


registerEventHandler(
    ASSET_EDITOR_FIELD_CHANGED,
    (_, {field, value}) => {
        return {
            ...state.set({
                [`assetEditor.asset.${field}`]: value,
            })
        }
    });

registerEventHandler(
    ASSET_EDITOR_FOLDER_CHANGED,
    (_, {folder}) => {
        return {
            ...state.set({
                'assetEditor.asset.folder': folder._id
            })
        }
    }
);

registerEventHandler(ASSET_EDITOR_SAVE,
    ({environment: {apiEndpoint}, state:{asset}}, _) => {
    console.log('save', asset);
        return {
            ...http.post({
                url: `${apiEndpoint}assetsmanager/updateAsset`,
                body: {asset},
                successEvent: ASSET_EDITOR_SAVED,
                errorEvent: ASSET_EDITOR_SAVE_FAILED
            })
        }
    },
    [
        environment(),
        state.get({asset: 'assetEditor.asset'})
    ]
);

registerEventHandler(ASSET_EDITOR_SAVED,
    () => {
        return {
            ...state.set({
                'assetsDialog.showMode': 'list',
            }),
            ...dispatch(ASSET_DIALOG_ASSETS_REQUESTED)
        }
    }
);


registerEventHandler(ASSET_EDITOR_SAVE_FAILED,
    (_,[error]) => {
        return {
            ...state.set({
                'assetEditor.error': error,
            })
        }
    }
);

registerEventHandler(ASSET_EDITOR_CANCEL, () => {
    return {
        ...state.set({
            'assetsDialog.showMode': 'list',
        }),
    }
});