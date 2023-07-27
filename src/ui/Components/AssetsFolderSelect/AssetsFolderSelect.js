import {useMount} from 'react-use';
import {dispatch} from "reffects";
import {ASSET_DIALOG_ASSETS_INIT} from "../AssetsManager/events";
import {ASSET_FOLDER_SELECT_MOUNT} from "./events";
import {useSelector} from "reffects-store";
import {assetFolderSelectSelector} from './selectors';

export default function AssetsFolderSelect({asset, changeAssetFolder}) {
    useMount(() => {
        dispatch(ASSET_FOLDER_SELECT_MOUNT);
    });
    const folders = useSelector(assetFolderSelectSelector);

    if (folders.length === 0) {
        return null;
    }

    return (
        <div data-uk-dropdown="mode:'click'">
            <a className="uk-text-muted">
                <i className="uk-icon-folder-o"></i> {asset.folder && folders[asset.folder] ? folders[asset.folder].name : App.i18n.get('Select folder')}
            </a>

            <div className="uk-dropdown uk-dropdown-close uk-width-1-1">
                <strong>{App.i18n.get('Folders')}</strong>
                <div className={`uk-margin-small-top ${App.Utils.count(folders) > 10 && 'uk-scrollable-box'}`}>
                    <ul className="uk-list">
                        {Object.entries(folders).map(([key, folder]) =>
                            <li style={{marginLeft: `${(folder._lvl * 10)}px`}} key={`folder_option_${folder._id}`}>
                                <a className="uk-link-muted" onClick={() => changeAssetFolder(folder)}>
                                    <i className="uk-icon-folder-o"></i> {folder.name}
                                </a>
                            </li>
                        )}
                    </ul>
                </div>
            </div>

        </div>
    )
}