import {useSelector} from 'reffects-store';
import {assetsDialogAssets, assetsDialogSelectedAssets} from "./selectors";
import {Thumbnail} from './Thumbnail';

function onSelect() {

}

function onEdit() {
}

export default function Asset({asset}) {
    const selected = useSelector(assetsDialogSelectedAssets);
    const assets = useSelector(assetsDialogAssets);
    const isImage = asset.mime.match(/^image\//);

    return (
        <div className="uk-grid-margin" onClick={onSelect}>
            <div
                className={'uk-panel uk-panel-box uk-panel-card uk-padding-remove ' + (selected.length && selected.indexOf(asset) != -1 ? 'uk-selected' : '')}>
                <div
                    className={'uk-overlay uk-display-block uk-position-relative ' + (asset.mime.match(/^image\//) && 'uk-bg-transparent-pattern')}>
                    <img className="uk-responsive-width" src={asset.path} width={200} height={150} />
                    <div className="uk-position-absolute uk-position-cover uk-flex uk-flex-middle">
                        <div className="uk-width-1-1 uk-text-center">
                            {!isImage && <span><i
                                className="uk-h1 uk-text-muted uk-icon-{ parent.getIconCls(asset.path) }"></i></span>}
                            {isImage &&
                                <Thumbnail src={asset.path} height="150"
                                           title={asset.width && [asset.width, asset.height].join('x')}/>}
                        </div>
                    </div>
                </div>
                <div className="uk-panel-body uk-text-small">
                    <div className="uk-text-truncate">
                        <a onClick={onEdit}>{asset.title}</a>
                    </div>
                    <div className="uk-text-muted uk-margin-small-top uk-flex">
                        <strong>{asset.mime}</strong>
                        <span
                            className="uk-flex-item-1 uk-margin-small-left uk-margin-small-right">{App.Utils.formatSize(asset.size)}</span>
                        {isImage &&
                            <a href={asset.path} data-uk-lightbox="type:'image'"
                               title={asset.width && [asset.width, asset.height].join('x')}
                               aria-label={asset.width && [asset.width, asset.height].join('x')}>
                                <i className="uk-icon-search"></i>
                            </a>}
                    </div>
                </div>
            </div>
        </div>
    )
}