import {useState, useRef} from 'react';
import {useMount} from 'react-use';
import {dispatch} from 'reffects';
import PathBreadCrumb from './PathBreadCrumb';
import TopBar from './TopBar';
import {Folders} from './Folders';
import AssetsList from './AssetsList';
import Paginator from './Paginator';
import Modal from '../Modal';
import {ASSET_DIALOG_ASSETS_INIT, ASSET_DIALOG_ASSETS_REQUESTED, ASSET_DIALOG_SUBMIT, ASSET_DIALOG_OPEN,ASSET_DIALOG_CLOSE} from "./events";
import {useSelector} from "reffects-store";
import {
    assetsDialogIsUploadingSelector,
    assetsDialogLoading,
    assetsDialogOpen,
    assetsDialogShowModeSelector,
    assetsDialogUploadProgressSelector
} from "./selectors";
import AssetEditor from '../AssetEditor/AssetEditor';
import Progress from '../Progress';

function selectAssets(callback) {
    dispatch({id: ASSET_DIALOG_SUBMIT, payload: {callback}});
}

function cancel() {
    dispatch(ASSET_DIALOG_CLOSE);
}

function openDialog() {
    dispatch(ASSET_DIALOG_OPEN);
}

function AssetsDialog({showFunction, externalController}) {
    const open = useSelector(assetsDialogOpen);
    const mode = useSelector(assetsDialogShowModeSelector);
    const loading = useSelector(assetsDialogLoading);
    const uploading = useSelector(assetsDialogIsUploadingSelector);
    const uploadProgress = useSelector(assetsDialogUploadProgressSelector);
    const assetManagerRef = useRef(null);

    useMount(() => {
        dispatch({id: ASSET_DIALOG_ASSETS_INIT, payload: {ref: assetManagerRef}});
    });

    showFunction(openDialog);
    const modal = true;

    return (
        <Modal title="Select assets" open={open} onClose={cancel}>
            <div ref={assetManagerRef}>
                {(mode == 'list' || mode == 'grid') &&
                    <>
                        { uploading && <Progress progress={uploadProgress}/> }
                        <div className="uk-form">
                            <TopBar listMode={mode}/>
                            <PathBreadCrumb/>
                            <div className="uk-text-center uk-margin-large-top"
                                 style={{display: loading ? 'block' : 'none'}}>
                                <cp-preloader className="uk-container-center"></cp-preloader>
                            </div>
                            <div className={modal && 'uk-overflow-container'} style={{padding: '1px 1px'}}>
                                <Folders modal={modal}/>
                                <AssetsList listMode={mode} modal={modal}/>
                            </div>
                        </div>
                        <Paginator/>
                        <div className="uk-margin-top">
                            <button type="button" className="uk-button uk-button-large uk-button-primary"
                                onClick={() => selectAssets(externalController.onSubmit)}>{App.i18n.get('Save')}</button>
                            <a className="uk-button uk-button-large uk-button-link"
                           onClick={cancel}>{App.i18n.get('Cancel')}</a>
                        </div>
                    </>
                }
                {mode == 'edit' && <AssetEditor modal={modal} /> }
            </div>
        </Modal>
    )
}

export default AssetsDialog;