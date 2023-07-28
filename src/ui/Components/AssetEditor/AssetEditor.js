import {useSelector} from 'reffects-store';
import {dispatch} from 'reffects';
import Asset from '../AssetsManager/Asset';
import {editingAssetSelector} from './selectors';
import Progress from '../Progress';
import {getIconCls} from '../assets.utils';
import {Thumbnail} from '../AssetsManager/Thumbnail';
import {ASSET_EDITOR_FIELD_CHANGED, ASSET_EDITOR_FOLDER_CHANGED, ASSET_EDITOR_SAVE, ASSET_EDITOR_CANCEL} from './events';
import AssetsFolderSelect from "../AssetsFolderSelect/AssetsFolderSelect";
import Account from "../Account";
import Tags from "../Fields/Tags/Tags";

function saveAsset() {
    dispatch(ASSET_EDITOR_SAVE);
}

function cancelAssetEdit() {
    dispatch(ASSET_EDITOR_CANCEL);
}

function changeField(event) {
    dispatch({id: ASSET_EDITOR_FIELD_CHANGED, payload: {field: event.target.name, value: event.target.value}});
}

function changeAssetFolder(folder) {
    dispatch({id: ASSET_EDITOR_FOLDER_CHANGED, payload: {folder}});
}

function onRemoveTag() {}
function onAddTag() {}

export default function AssetEditor({modal}) {
    const asset = useSelector(editingAssetSelector);
console.log(asset);
    return (
        <div className="uk-form">
            <h3 className="uk-text-bold">{App.i18n.get('Edit Asset')}</h3>
            { !asset &&
                <div className="uk-text-center uk-margin-large-top">
                    <Progress/>
                </div>
            }

            <div className="uk-form" if="{asset}">
                {/*
                <ul className="uk-tab uk-flex-center uk-margin" show="{ App.Utils.count(panels) }">
                    <li className="{!panel && 'uk-active'}"><a onClick="{selectPanel}">Main</a></li>
                    <li className="uk-text-capitalize {p.name == panel && 'uk-active'}" each="{p in panels}"><a
                        onClick="{parent.selectPanel}">{p.name}</a></li>
                </ul> */}

                <div className="uk-grid" show="{!panel}">
                    <div className="uk-width-2-3">
                        <div className="uk-panel uk-panel-box uk-panel-card uk-panel-space">
                            <div className="uk-form-row">
                                <label className="uk-text-small uk-text-bold">{App.i18n.get('Title')}</label>
                                <input className="uk-width-1-1" type="text" name="title" value={asset.title} required onChange={changeField}/>
                            </div>

                            <div className="uk-form-row">
                                <label className="uk-text-small uk-text-bold">{App.i18n.get('Description')}</label>
                                <textarea className="uk-width-1-1" onChange={changeField} name="description">
                                    {asset.description}
                                </textarea>
                            </div>

                            <div className="uk-margin-large-top uk-text-center" if="{asset}">
                                { asset.mime.match(/^image\//) == null &&
                                    <span className="uk-h1">
                                    <i className={`uk-icon-${ getIconCls(asset.path) }`}></i>
                                </span>}
                                {asset.mime.match(/^image\//) &&
                                <div className="uk-display-inline-block uk-position-relative asset-fp-image">
                                    <Thumbnail src={asset.path} width="800" />
                                    <div className="cp-assets-fp" title="Focal Point" data-uk-tooltip></div>
                                </div>}
                                <div className="uk-margin-top uk-text-truncate uk-text-small uk-text-muted">
                                    <a href={asset.path} target="_blank"
                                       title={ App.i18n.get('Direct link to asset') } data-uk-tooltip>
                                        <i className="uk-icon-button uk-icon-button-outline uk-text-primary uk-icon-link"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="uk-width-1-3">
                        <div className="uk-margin">
                            <label className="uk-text-small uk-text-bold">{App.i18n.get('Id')}</label>
                            <div className="uk-margin-small-top uk-text-muted">{asset._id}</div>
                        </div>
                        <div className="uk-margin">
                            <label className="uk-text-small uk-text-bold">{App.i18n.get('Folder')}</label>
                            <div className="uk-margin-small-top">
                                <AssetsFolderSelect asset={asset} changeAssetFolder={changeAssetFolder} />
                            </div>
                        </div>
                        <div className="uk-margin">
                            <label className="uk-text-small uk-text-bold">{App.i18n.get('Type')}</label>
                            <div className="uk-margin-small-top uk-text-muted"><span
                                className="uk-badge uk-badge-outline">{asset.mime}</span></div>
                        </div>

                        {asset.colors && Array.isArray(asset.colors) && asset.colors.length &&
                        <div className="uk-margin">
                            <label className="uk-text-small uk-text-bold">{App.i18n.get('Colors')}</label>
                            <div className="uk-margin-small-top uk-text-muted">
                                {asset.colors.map((color) => {
                                        const colorString = String(color).replace('#', '');
                                        return <span className="uk-icon-circle uk-text-large uk-margin-small-right"
                                                     style={{color: colorString}}></span>
                                    }
                                )}
                            </div>
                        </div>
                        }
                        <div className="uk-margin">
                            <label className="uk-text-small uk-text-bold">{App.i18n.get('Size')}</label>
                            <div className="uk-margin-small-top uk-text-muted">{App.Utils.formatSize(asset.size)}</div>
                        </div>
                        <div className="uk-margin">
                            <label className="uk-text-small uk-text-bold">{App.i18n.get('Modified')}</label>
                            <div className="uk-margin-small-top uk-text-primary">
                                <span className="uk-badge uk-badge-outline">
                                    {App.Utils.dateformat(new Date(asset.modified))}
                                </span>
                            </div>
                        </div>
                        <div className="uk-margin">
                            <label className="uk-text-small uk-text-bold">{App.i18n.get('Tags')}</label>
                            <div className="uk-margin-small-top">
                                <Tags tags={asset.tags} onRemove={onRemoveTag} onAddTag={onAddTag} />
                            </div>
                        </div>
                        {asset._by &&
                            <div className="uk-margin">
                                <label className="uk-text-small">{App.i18n.get('Last update by')}</label>
                                <div className="uk-margin-small-top">
                                    <Account account={asset._by} />
                                </div>
                            </div>
                        }
                    </div>
                </div>

                <div data-is="{'assetspanel-'+p.name}" asset="{asset}" each="{p in panels}"
                     show="{panel == p.name}"></div>
            </div>

            {modal && <div className="uk-margin-top" show="{modal}">
                <button type="button" className="uk-button uk-button-large uk-button-primary"
                        onClick={saveAsset}>{App.i18n.get('Save')}</button>
                <a className="uk-button uk-button-large uk-button-link"
                   onClick={cancelAssetEdit}>{App.i18n.get('Cancel')}</a>
            </div>
            }

            {!modal && <div>
                <div className="uk-container uk-container-center">
                    <button type="button" className="uk-button uk-button-large uk-button-primary"
                            onClick={saveAsset}>{App.i18n.get('Save')}</button>
                    <a className="uk-button uk-button-large uk-button-link"
                       onClick={cancelAssetEdit}>{App.i18n.get('Cancel')}</a>
                </div>
            </div>}
        </div>
    );
}