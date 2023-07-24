import {effects, registerEventHandler} from 'reffects';
import {state} from 'reffects-store';
import {http} from 'reffects-batteries';
import {environment} from '../../environment';
import {ASSET_DIALOG_ASSETS_REQUESTED} from "./events";

export const ASSET_DIALOG_FOLDER_CHANGE_DIR = 'ASSET_DIALOG_FOLDER_CHANGE_DIR';

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
                'assetsDialog.currentFolder': folder._id,
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