import {effects, registerEventHandler} from 'reffects';
import {dispatch} from 'reffects';
import {state} from 'reffects-store';
import {http} from 'reffects-batteries';
import {environment} from '../../environment';
import {ASSET_DIALOG_ASSETS_REQUESTED} from "./events";
import {changeUploadFolder, createUploader} from "./upload";

export const ASSET_DIALOG_FOLDER_CHANGE_DIR = 'ASSET_DIALOG_FOLDER_CHANGE_DIR';
export const ASSET_DIALOG_FOLDER_ADD_DIR = 'ASSET_DIALOG_FOLDER_ADD_DIR';
export const ASSET_DIALOG_FOLDER_DIR_ADDED = 'ASSET_DIALOG_FOLDER_DIR_ADDED';
export const ASSET_DIALOG_FOLDER_RENAMED = 'ASSET_DIALOG_FOLDER_RENAMED';
export const ASSET_DIALOG_FOLDER_RENAMED_SUCCESS = 'ASSET_DIALOG_FOLDER_RENAMED_SUCCESS';
export const ASSET_DIALOG_FOLDER_RENAMED_FAILED = 'ASSET_DIALOG_FOLDER_RENAMED_FAILED';
export const ASSET_DIALOG_FOLDER_REMOVED = 'ASSET_DIALOG_FOLDER_REMOVED';
export const ASSET_DIALOG_FOLDER_REMOVED_SUCCESS = 'ASSET_DIALOG_FOLDER_REMOVED_SUCCESS';
export const ASSET_DIALOG_FOLDER_REMOVED_FAILED = 'ASSET_DIALOG_FOLDER_REMOVED_FAILED';

registerEventHandler(
    ASSET_DIALOG_FOLDER_ADD_DIR,
    ({state:{currentFolder}}) => {
        App.ui.prompt(App.i18n.get('Folder Name:'), '', function(name) {
            if (!name.trim()) {
                return;
            }

            App.request('/assetsmanager/addFolder', {name:name, parent:currentFolder}).then(function(folder) {
                dispatch({id: ASSET_DIALOG_FOLDER_DIR_ADDED, payload: {folder}});/*
                if (!folder._id) {
                    return;
                }

                $this.folders.push(folder);
                $this.update();*/
            });
        });
    },
    [
        state.get({folder: 'assetsDialog.currentFolder'})
    ]
)

registerEventHandler(ASSET_DIALOG_FOLDER_DIR_ADDED, ({state: {folders}}, {folder}) => {
    console.log('NEW FOLDER:',folder)
    folders.push(folder);

    return {
        ...state.set({
            'assetsDialog.folders': folders
        })
    }
}, [
    state.get({folders: 'assetsDialog.folders'})
]);

registerEventHandler(
    ASSET_DIALOG_FOLDER_CHANGE_DIR,
    ({state: {uploader, currentFolder, foldersPath}}, {folder}) => {

        if (folder._id !== null && currentFolder == folder._id) {
            return;
        }
        let newFoldersPath = [];
        if (folder._id) {
            let skip = false;
            newFoldersPath = foldersPath.filter(function(f) {
                if (f._id == folder._id) {
                    skip = true;
                }

                return !skip;
            });

            newFoldersPath.push(folder);
        } else {
            newFoldersPath = [];
        }
        uploader.setMeta({ folder: folder._id });

        return {
            ...state.set({
                'assetsDialog.uploader': uploader,
                'assetsDialog.currentFolder': folder,
                'assetsDialog.foldersPath': newFoldersPath
            }),
            ...effects.dispatch({
                id: ASSET_DIALOG_ASSETS_REQUESTED
            })
        }
    },

    [state.get({
        uploader: 'assetsDialog.uploader',
        folder: 'assetsDialog.currentFolder',
        foldersPath: 'assetsDialog.foldersPath',
    })]
)

registerEventHandler(
    ASSET_DIALOG_FOLDER_RENAMED,
    ({environment: {apiEndpoint}}, {folder, name}) => {
        return {
            ...http.post({
                url: `${apiEndpoint}assetsmanager/renameFolder`,
                body: {
                    name,
                    folder
                },
                successEvent: ASSET_DIALOG_FOLDER_RENAMED_SUCCESS,
                errorEvent: ASSET_DIALOG_FOLDER_RENAMED_FAILED,
            })
        }
    },
    [environment()]
);

registerEventHandler(
    ASSET_DIALOG_FOLDER_RENAMED_SUCCESS,
    () => {
        return {
            ...effects.dispatch(ASSET_DIALOG_ASSETS_REQUESTED)
        }
    }
);

registerEventHandler(
    ASSET_DIALOG_FOLDER_RENAMED_FAILED,
    () => {
        return {}
    }
);

registerEventHandler(
    ASSET_DIALOG_FOLDER_REMOVED,
    ({environment: {apiEndpoint}}, {folder}) => {
        return {
            ...http.post({
                url: `${apiEndpoint}assetsmanager/removeFolder`,
                body: {
                    folder
                },
                successEvent: ASSET_DIALOG_FOLDER_REMOVED_SUCCESS,
                errorEvent: ASSET_DIALOG_FOLDER_REMOVED_FAILED,
            })
        }
    },
    [environment()]
);

registerEventHandler(
    ASSET_DIALOG_FOLDER_REMOVED_SUCCESS,
    () => {
        return {
            ...effects.dispatch(ASSET_DIALOG_ASSETS_REQUESTED)
        }
    }
);

registerEventHandler(
    ASSET_DIALOG_FOLDER_REMOVED_FAILED,
    () => {
        return {}
    }
);