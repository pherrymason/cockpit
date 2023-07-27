import {effects, registerEventHandler} from 'reffects';
import {state} from 'reffects-store';
import {http} from 'reffects-batteries';
import {environment} from '../../environment';

export const ASSET_FOLDER_SELECT_MOUNT = 'ASSET_FOLDER_SELECT_MOUNT';
export const ASSET_FOLDER_SELECT_FOLDERS_LOADED = 'ASSET_FOLDER_SELECT_FOLDERS_LOADED';
export const ASSET_FOLDER_SELECT_FOLDERS_LOAD_FAILED = 'ASSET_FOLDER_SELECT_FOLDERS_LOAD_FAILED';

registerEventHandler(
    ASSET_FOLDER_SELECT_MOUNT,
    ({environment:{apiEndpoint}}, _) => {
        return {
            ...state.set({
                'assetFolderSelect.loading': true,
                'assetFolderSelect.folders': []
            }),
            ...http.post({
                url: `${apiEndpoint}assetsmanager/_folders`,
                successEvent: ASSET_FOLDER_SELECT_FOLDERS_LOADED,
                errorEvent: ASSET_FOLDER_SELECT_FOLDERS_LOAD_FAILED
            })
        }
    },
    [environment()]);

registerEventHandler(
    ASSET_FOLDER_SELECT_FOLDERS_LOADED,
    (_, [data]) => {
        let foldersIndexedById = {};
        data.forEach((folder) => {
            foldersIndexedById[folder._id] = folder;
        });

        return {
            ...state.set({
                'assetFolderSelect.loading': false,
                'assetFolderSelect.folders': foldersIndexedById
            })
        }
    });
registerEventHandler(
    ASSET_FOLDER_SELECT_FOLDERS_LOAD_FAILED,
    (_, [error]) => {
        console.log(error);
    });