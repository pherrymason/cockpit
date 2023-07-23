import { useState } from 'react';
import { useMount } from 'react-use';
import { dispatch } from 'reffects';
import PathBreadCrumb from './PathBreadCrumb';
import TopBar from './TopBar';
import Folders from './Folders';
import AssetsList from './AssetsList';
import Paginator from './Paginator';
import Modal from '../Modal';
import {ASSET_DIALOG_ASSETS_INIT, ASSET_DIALOG_ASSETS_REQUESTED} from "./events";



function Progress() {
    return (
        <div refo="uploadprogress" className="uk-margin uk-hidden">
            <div className="uk-progress">
                <div refo="progressbar" className="uk-progress-bar" style={{width: '0%'}}>&nbsp;</div>
            </div>
        </div>
    )
}

function Asset({asset}) {
    return (
        <div className="uk-grid-margin" each="{ asset,idx in assets }" onClick="{ select }">
            <div
                className="uk-panel uk-panel-box uk-panel-card uk-padding-remove { selected.length && selected.indexOf(asset) != -1 ? 'uk-selected':''}">
                <div
                    className="uk-overlay uk-display-block uk-position-relative { asset.mime.match(/^image\//) && 'uk-bg-transparent-pattern' }">
                    <canvas className="uk-responsive-width" width="200" height="150"></canvas>
                    <div className="uk-position-absolute uk-position-cover uk-flex uk-flex-middle">
                        <div className="uk-width-1-1 uk-text-center">
                            <span if="{ asset.mime.match(/^image\//) == null }"><i
                                className="uk-h1 uk-text-muted uk-icon-{ parent.getIconCls(asset.path) }"></i></span>
                            <cp-thumbnail src="{asset.path}" height="150" if="{ asset.mime.match(/^image\//) }"
                                          title="{ asset.width && [asset.width, asset.height].join('x') }"></cp-thumbnail>
                        </div>
                    </div>
                </div>
                <div className="uk-panel-body uk-text-small">
                    <div className="uk-text-truncate">
                        <a onClick="{ parent.edit }">{asset.title}</a>
                    </div>
                    <div className="uk-text-muted uk-margin-small-top uk-flex">
                        <strong>{asset.mime}</strong>
                        <span
                            className="uk-flex-item-1 uk-margin-small-left uk-margin-small-right">{App.Utils.formatSize(asset.size)}</span>
                        <a href="{asset.path}" if="{ asset.mime.match(/^image\//) }" data-uk-lightbox="type:'image'"
                           title="{ asset.width && [asset.width, asset.height].join('x') }"
                           aria-label="{ asset.width && [asset.width, asset.height].join('x') }">
                            <i className="uk-icon-search"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    )
}


function AssetsDialog({showFunction, externalController}) {
    const [open, setOpen] = useState(true);
    const [listMode, setListMode] = useState('list');
    useMount(() => {
        dispatch(ASSET_DIALOG_ASSETS_REQUESTED);
    });

    showFunction(setOpen);
    console.log('re-render');

    return (
        <Modal title="Select assets" open={open}>
            <div refo="list">
                <Progress/>

                <div className="uk-form" if="{ mode=='list' }">
                    <TopBar />
                    <PathBreadCrumb />
                    <div className="uk-text-center uk-margin-large-top" show="{ loading }">
                        <cp-preloader className="uk-container-center"></cp-preloader>
                    </div>
                    <Folders />
                    <AssetsList listMode={listMode} />
                </div>
                
                <Paginator />
            </div>
        </Modal>
)
}

export default AssetsDialog;