import { useState } from 'react';
import { useMount } from 'react-use';
import { dispatch } from 'reffects';
import PathBreadCrumb from './PathBreadCrumb';
import TopBar from './TopBar';
import {Folders} from './Folders';
import AssetsList from './AssetsList';
import Paginator from './Paginator';
import Modal from '../Modal';
import {ASSET_DIALOG_ASSETS_INIT, ASSET_DIALOG_ASSETS_REQUESTED} from "./events";
import {useSelector} from "reffects-store";
import {assetsDialogShowModeSelector} from "./selectors";



function Progress() {
    return (
        <div refo="uploadprogress" className="uk-margin uk-hidden">
            <div className="uk-progress">
                <div refo="progressbar" className="uk-progress-bar" style={{width: '0%'}}>&nbsp;</div>
            </div>
        </div>
    )
}




function AssetsDialog({showFunction, externalController}) {
    const [open, setOpen] = useState(true);
    const listMode = useSelector(assetsDialogShowModeSelector)
    useMount(() => {
        dispatch(ASSET_DIALOG_ASSETS_INIT);
    });

    showFunction(setOpen);
    console.log('re-render');
    const modal = true;

    return (
        <Modal title="Select assets" open={open}>
            <div refo="list">
                <Progress/>

                <div className="uk-form" if="{ mode=='list' }">
                    <TopBar listMode={listMode} />
                    <PathBreadCrumb />
                    <div className="uk-text-center uk-margin-large-top" show="{ loading }">
                        <cp-preloader className="uk-container-center"></cp-preloader>
                    </div>
                    <Folders modal={modal} />
                    <AssetsList listMode={listMode} />
                </div>
                
                <Paginator />
            </div>
        </Modal>
)
}

export default AssetsDialog;