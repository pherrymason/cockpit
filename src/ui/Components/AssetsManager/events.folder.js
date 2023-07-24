import {effects, registerEventHandler} from 'reffects';
import {dispatch} from 'reffects';
import {state} from 'reffects-store';
import {http} from 'reffects-batteries';
import {environment} from '../../environment';
import {ASSET_DIALOG_ASSETS_REQUESTED} from "./events";

export const ASSET_DIALOG_FOLDER_CHANGE_DIR = 'ASSET_DIALOG_FOLDER_CHANGE_DIR';
export const ASSET_DIALOG_FOLDER_ADD_DIR = 'ASSET_DIALOG_FOLDER_ADD_DIR';
export const ASSET_DIALOG_FOLDER_DIR_ADDED = 'ASSET_DIALOG_FOLDER_DIR_ADDED';

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
    ({state: {currentFolder, foldersPath}}, {folder}) => {

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


        return {
            ...state.set({
                'assetsDialog.currentFolder': folder,
                'assetsDialog.foldersPath': newFoldersPath
            }),
            ...effects.dispatch({
                id: ASSET_DIALOG_ASSETS_REQUESTED
            })
        }
    },

    [state.get({
        folder: 'assetsDialog.currentFolder',
        foldersPath: 'assetsDialog.foldersPath',
    })]
)