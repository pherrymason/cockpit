import {useSelector} from 'reffects-store';
import {dispatch} from 'reffects';
import {assetsDialogFolders, assetsDialogLoading} from './selectors';
import {ASSET_DIALOG_FOLDER_CHANGE_DIR} from './events.folder';

export function changeDir(folder) {
    console.log(folder);
    dispatch({id: ASSET_DIALOG_FOLDER_CHANGE_DIR, payload: {folder}});
}

function renameFolder() {

}

export function Folders({modal}) {
    const folders = useSelector(assetsDialogFolders);
    const loading = useSelector(assetsDialogLoading);
    console.log('Folders:', folders);
    if (loading && folders.length < 1) {
        return null;
    }

    return (
        <div className={modal && 'uk-overflow-container'} style={{padding: '1px 1px'}}>
            <div className="uk-margin">
                <strong className="uk-text-small uk-text-muted">
                    <i className="uk-icon-folder-o uk-margin-small-right"></i> {folders.length} {App.i18n.get('Folders')}
                </strong>

                <div className="uk-grid uk-grid-small uk-grid-match uk-grid-width-medium-1-4 uk-grid-width-xlarge-1-5">
                    {folders.map((folder, idx) =>
                        <div className="uk-grid-margin" key={`folder_${idx}`}>
                            <div className="uk-panel uk-panel-box uk-panel-card">
                                <div className="uk-flex">
                                    <div className="uk-margin-small-right"><i className="uk-icon-folder-o"></i></div>
                                    <div className="uk-flex-item-1 uk-text-bold uk-text-truncate">
                                        <a className="uk-link-muted" onClick={() => changeDir(folder)}>{folder.name}</a>
                                    </div>
                                    <div>
                                    <span data-uk-dropdown="mode:'click', pos:'bottom-right'">
                                        <a><i className="uk-icon-ellipsis-v js-no-item-select"></i></a>
                                        <div className="uk-dropdown">
                                            <ul className="uk-nav uk-nav-dropdown uk-dropdown-close">
                                                <li className="uk-nav-header uk-text-truncate">{folder.name}</li>
                                                <li>
                                                    <a className="uk-dropdown-close" onClick={renameFolder}>
                                                        {App.i18n.get('Rename')}
                                                    </a>
                                                </li>
                                                <li className="uk-nav-divider"></li>
                                                <li className="uk-nav-item-danger">
                                                    <a className="uk-dropdown-close" onClick={renameFolder}>
                                                        {App.i18n.get('Delete')}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    )
}