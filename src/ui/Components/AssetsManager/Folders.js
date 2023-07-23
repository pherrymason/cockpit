import {useSelector} from 'reffects-store';
import {assetsDialogFolders, assetsDialogLoading} from './selectors';

function changeUp() {

}

function renameFolder() {

}

function Folders() {
    const folders = useSelector(assetsDialogFolders);
    const show = useSelector(assetsDialogLoading);

    return (
        <div className="uk-margin" style={{display: show ? 'block' : 'none'}}>

            <strong className="uk-text-small uk-text-muted"><i
                className="uk-icon-folder-o uk-margin-small-right"></i> {folders.length} {App.i18n.get('Folders')}
            </strong>

            <div className="uk-grid uk-grid-small uk-grid-match uk-grid-width-medium-1-4 uk-grid-width-xlarge-1-5">
                <div className="uk-grid-margin" each="{ folder,idx in folders }">
                    {folders.map((folder, idx) =>
                        <div className="uk-panel uk-panel-box uk-panel-card" key={`folder_${idx}`}>
                            <div className="uk-flex">
                                <div className="uk-margin-small-right"><i className="uk-icon-folder-o"></i></div>
                                <div className="uk-flex-item-1 uk-text-bold uk-text-truncate">
                                    <a className="uk-link-muted" onClick={changeUp}>{folder.name}</a>
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
                    )}
                </div>
            </div>

        </div>
    )
}

export default Folders;