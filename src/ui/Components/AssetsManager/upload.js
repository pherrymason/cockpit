import Uppy from '@uppy/core';
import XHRUpload from '@uppy/xhr-upload';
import {dispatch} from 'reffects';
import {ASSET_DIALOG_ASSETS_REQUESTED, ASSET_DIALOG_UPLOAD_PROGRESSED, ASSET_DIALOG_UPLOAD_STARTED, ASSET_DIALOG_UPLOAD_FINISHED} from "./events";

export function createUploader({endpoint, folder}) {
    const uppy = new Uppy({
        restrictions: {
            maxNumberOfFiles: App?._vars?.maxFileUploads || 20,
            minNumberOfFiles: 1,
        },
        allowMultipleUploadBatches: true,
        meta: {
            folder: folder._id,
        },
    }).use(XHRUpload, {
        endpoint: endpoint,
        headers: {
            'X-CSRF-TOKEN': App.csrf
        },
        formData: true,
        bundle: true,
    });

    uppy.on('upload', (data) => {
        dispatch(ASSET_DIALOG_UPLOAD_STARTED);
    });
    uppy.on('progress', (progress) => {
        dispatch({id: ASSET_DIALOG_UPLOAD_PROGRESSED, payload:{progress}});
    });

    uppy.on('restriction-failed', (file, error) => {
        // do some customized logic like showing system notice to users
        App.ui.notify(error.message, "danger");
        resetUploader(uppy);
    });

    uppy.on('error', (error) => {
        resetUploader(uppy);
        App.ui.notify(error.message, "danger");
    });

    uppy.on('upload-error', (file, error, response) => {
        resetUploader(uppy);
        App.ui.notify(`Filed to upload file ${file.name}: ${error.message}`, "danger");
    });

    uppy.on('complete', () => {
        resetUploader(uppy);
    });

    return uppy;
}

function resetUploader(uppy) {
    const files = uppy.getFiles();
    uppy.cancelAll();
    dispatch(ASSET_DIALOG_UPLOAD_FINISHED);
}


export const uploadSettings = {
    action: App.route('/assetsmanager/upload'),
    type: 'json',
    before: function (options) {
        options.params.folder = assetsDialog.currentFolder._id
    },
    loadstart: function () {
        console.log('LOAD START');
        //$this.refs.uploadprogress.classList.remove('uk-hidden');
    },
    progress: function (percent) {
        percent = Math.ceil(percent) + '%';
        console.log(percent + '%');
        //$this.refs.progressbar.innerHTML   = '<span>'+percent+'</span>';
        //$this.refs.progressbar.style.width = percent;
    },
    allcomplete: function (response) {
        console.log('COMPLETE');
        //$this.refs.uploadprogress.classList.add('uk-hidden');
        if (response && response.failed && response.failed.length) {
            App.ui.notify("File(s) failed to upload.", "danger");
        }

        if (response && Array.isArray(response.assets) && response.assets.length) {
            if (!Array.isArray($this.assets)) {
                $this.assets = [];
            }

            App.ui.notify("File(s) uploaded.", "success");
            response.assets.forEach(function (asset) {
                $this.assets.unshift(asset);
            });

            //$this.listAssets(1);
            dispatch(ASSET_DIALOG_ASSETS_REQUESTED);
        }

        if (!response) {
            App.ui.notify("Something went wrong.", "danger");
        }
    }
};
