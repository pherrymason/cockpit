import {useSelector} from 'reffects-store';
import {dispatch} from 'reffects';
import {ASSET_DIALOG_TOGGLE_SHOWMODE, ASSET_DIALOG_UPLOAD_FILE} from "./events";
import {addFolder} from './Folders';
import {assetsDialogSelectedAssets} from "./selectors";

function getRefValue() {
}

function updateFilter() {
    dispatch(ASSET_DIALOG_UPDATE_FILTER);
}

function removeSelected() {
    App.ui.confirm("Are you sure?", function() {
        dispatch(ASSET_DIALOG_REMOVE_SELECTED);
    });
}

function toggleListMode() {
    dispatch(ASSET_DIALOG_TOGGLE_SHOWMODE);
}

function onUploadFile(event) {
    console.log(event.target.files[0]);
    /*
    let fileReader = new FileReader();
    fileReader.onloadend = (e) => {
        var arrayBuffer = e.target.result;
        var fileType = $('#file-type').value;
        var blob = blobUtil.arrayBufferToBlob(arrayBuffer, fileType)
        console.log('here is a blob', blob);
        console.log('its size is', blob.size);
        console.log('its type is', blob.type);
    }
    fileReader.readAsArrayBuffer(event.target.files[0]);
    */
    dispatch({id: ASSET_DIALOG_UPLOAD_FILE, payload: {files: event.target.files}});
}

function TopBar({listMode}) {
    const selected = useSelector(assetsDialogSelectedAssets);

    return (
        <div className="uk-grid uk-grid-width-1-2">
            <div>
                <div className="uk-grid uk-grid-small uk-flex-middle">
                    <div>
                        <div className="uk-form-select">
                            <span
                                className="uk-button uk-button-large getRefValue('filtertype') && 'uk-button-primary' uk-text-capitalize">
                                <i className="uk-icon-eye uk-margin-small-right"></i>
                                {getRefValue('filtertype') || App.i18n.get('All')}
                            </span>

                            <select refo="filtertype" onChange={updateFilter}
                                    aria-label={App.i18n.get('Mime Type')}>
                                <option value="">All</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                                <option value="audio">Audio</option>
                                <option value="document">Document</option>
                                <option value="archive">Archive</option>
                                <option value="code">Code</option>
                            </select>
                        </div>
                    </div>
                    <div className="uk-flex-item-1">
                        <div className="uk-form-icon uk-display-block uk-width-1-1">
                            <i className="uk-icon-search"></i>
                            <input className="uk-width-1-1 uk-form-large" type="text"
                                   aria-label={App.i18n.get('Search assets')} refo="filtertitle"
                                   onChange={updateFilter}/>
                        </div>
                    </div>
                </div>
            </div>
            <div className="uk-text-right">
                {selected.length > 0 && (<button className="uk-button uk-button-large uk-button-danger" type="button"
                        onClick={removeSelected}>
                    {App.i18n.get('Delete')} <span
                    className="uk-badge uk-badge-contrast uk-margin-small-left">{selected.length}</span>
                </button>)}

                <button className="uk-button uk-button-large uk-button-link"
                        onClick={addFolder}>{App.i18n.get('Add folder')}</button>

                <span className="uk-button-group uk-button-large">
                    <button className={'uk-button uk-button-large '+ (listMode=='list' && 'uk-button-primary')}
                            type="button" onClick={toggleListMode}
                            aria-label={App.i18n.get('Switch to list-mode')}>
                        <i className="uk-icon-list"></i>
                    </button>
                    <button className={'uk-button uk-button-large ' + (listMode=='grid' && 'uk-button-primary')}
                            type="button" onClick={toggleListMode}
                            aria-label={App.i18n.get('Switch to tile-mode')}>
                        <i className="uk-icon-th"></i>
                    </button>
                </span>

                <span className="uk-button uk-button-large uk-button-primary uk-form-file">
                    <input className="js-upload-selecto" aria-label={ App.i18n.get('Select file') } type="file"
                               multiple={true} onChange={onUploadFile}/>
                    <i className="uk-icon-upload"></i>
                </span>
            </div>
        </div>
    )
}

export default TopBar;