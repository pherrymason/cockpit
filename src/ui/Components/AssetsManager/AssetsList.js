import {useSelector} from 'reffects-store';
import {dispatch} from 'reffects';
import {
    assetsDialogAssets,
    assetsDialogTotalAssetsSelector,
    assetsDialogLoading,
    assetsDialogSelectedAssets
} from './selectors';
import {Thumbnail} from './Thumbnail';
import Asset from './Asset';
import {ASSET_DIALOG_SELECT_ASSET} from "./events";

function selectAsset(e, asset) {
    if (App.$(e.target).is('a') || App.$(e.target).parents('a').length) {
        return;
    }

    dispatch({id: ASSET_DIALOG_SELECT_ASSET, payload: {asset}});
}

function editAsset() {
}

function removeAsset() {
}

function getIconCls(path) {

    var name = path.toLowerCase();

    if (name.match(typefilters.image)) {

        return 'image';

    } else if (name.match(typefilters.video)) {

        return 'video-camera';

    } else if (name.match(typefilters.audio)) {

        return 'music';

    } else if (name.match(typefilters.document)) {

        return 'file-text-o';

    } else if (name.match(typefilters.code)) {

        return 'code';

    } else if (name.match(typefilters.archive)) {

        return 'archive';

    } else {
        return 'paperclip';
    }
}

function AssetsList({listMode}) {
    const assets = useSelector(assetsDialogAssets);
    const count = useSelector(assetsDialogTotalAssetsSelector);
    const selectedAssets = useSelector(assetsDialogSelectedAssets);
    const loading = useSelector(assetsDialogLoading);
    const show = !loading && assets.length;

    function isSelected(asset) {
        return selectedAssets.some((elm) => {
            return elm._id==asset._id;
        })
    }

    return (
        <>
            {loading && <div className="uk-margin-large-top uk-panel-space uk-text-center">
                <span className="uk-text-muted uk-h2">{App.i18n.get('No Assets found')}</span>
            </div>}
            {show && <div className="uk-margin">
                <strong className="uk-text-small uk-text-muted">
                    <i className="uk-icon-file-o uk-margin-small-right"></i> {count} {App.i18n.get('Assets')}
                </strong>
                {listMode == 'grid' && (
                    <div className="uk-grid uk-grid-match uk-grid-small uk-grid-width-medium-1-5">
                        {
                            assets.map((asset) => <Asset asset={asset} key={`grid_asset_${asset._id}`}/>)
                        }
                    </div>
                )}
            </div>}
            {listMode == 'list' && (
                <table className="uk-table uk-table-tabbed">
                    <thead>
                    <tr>
                        <td width="30"></td>
                        <th className="uk-text-small uk-noselect">{App.i18n.get('Title')}</th>
                        <th className="uk-text-small uk-noselect" width="20%">{App.i18n.get('Type')}</th>
                        <th className="uk-text-small uk-noselect" width="10%">{App.i18n.get('Size')}</th>
                        <th className="uk-text-small uk-noselect" width="10%">{App.i18n.get('Updated')}</th>
                        <th className="uk-text-small uk-noselect" width="30"></th>
                    </tr>
                    </thead>
                    <tbody>
                    {
                        assets.map((asset) => {
                            const isImage = asset.mime.match(/^image\//);
                            return (
                                <tr className={selectedAssets.length && isSelected(asset) ? 'uk-selected' : ''}
                                    onClick={(e) => selectAsset(e,asset)} key={`asset_${asset._id}`}
                                    key={`asset_${asset._id}`}
                                >
                                    <td className="uk-text-center">
                                        {!isImage && (<span><i
                                            className={`uk-text-muted uk-icon-${getIconCls(asset.path)}`}></i></span>)}
                                        {isImage && (<a href={asset.path}
                                                        data-uk-lightbox="type:'image'"
                                                        title={asset.width && [asset.width, asset.height].join('x')}
                                                        aria-label={asset.width && [asset.width, asset.height].join('x')}>
                                            <Thumbnail src={asset.path} width="20" height="20"/>
                                        </a>)}
                                    </td>
                                    <td>
                                        <a if="{!parent.modal}" onClick={editAsset}>{asset.title}</a>
                                        <span if="{parent.modal}">{asset.title}</span>
                                    </td>
                                    <td className="uk-text-small">{asset.mime}</td>
                                    <td className="uk-text-small">{App.Utils.formatSize(asset.size)}</td>
                                    <td className="uk-text-small">{App.Utils.dateformat(new Date(1000 * asset.modified))}</td>
                                    <td>
                                <span className="uk-float-right" data-uk-dropdown="mode:'click'">

                                    <a className="uk-icon-bars"></a>

                                    <div className="uk-dropdown uk-dropdown-flip">
                                        <ul className="uk-nav uk-nav-dropdown">
                                            <li className="uk-nav-header">{App.i18n.get('Actions')}</li>
                                            <li><a className="uk-dropdown-close"
                                                   onClick={editAsset}>{App.i18n.get('Edit')}</a></li>
                                            <li><a className="uk-dropdown-close"
                                                   onClick={removeAsset}>{App.i18n.get('Delete')}</a></li>
                                        </ul>
                                    </div>
                                </span>
                                    </td>
                                </tr>)
                        })
                    }
                    </tbody>
                </table>
            )}
        </>
    )
}

export default AssetsList;