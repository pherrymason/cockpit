import {ASSET_DIALOG_ASSETS_REQUESTED} from "./events";
import Uppy from '@uppy/core';
import XHRUpload from '@uppy/xhr-upload';

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

    uppy.on('upload-error', (file, error, response) => {
        App.ui.notify(`Filed to upload file ${file.name}: ${error}`, "danger");
    })

    return uppy;
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
