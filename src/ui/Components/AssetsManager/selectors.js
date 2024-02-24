export function assetsDialogLoadingSelector(state) {
    return state.assetsDialog?.loading ?? false;
}
export function assetsDialogOpen(state) {
    return state.assetsDialog?.open ?? false;
}

export function assetsDialogFoldersSelector(state) {
    return state.assetsDialog?.folders ?? [];
}

export function assetsDialogAssets(state) {
    return state.assetsDialog?.assets ?? [];
}

export function assetsDialogTotalAssetsSelector(state) {
    return state.assetsDialog?.totalAssets ?? [];
}

export function assetsDialogSelectedAssets(state) {
    return state.assetsDialog?.selectedAssets ?? [];
}

export function assetsDialogPageSelector(state) {
    return state.assetsDialog?.page ?? 1;
}

export function assetsDialogPagesSelector(state) {
    return state.assetsDialog?.pages ?? 0;
}
export function assetsDialogShowModeSelector(state) {
    return state.assetsDialog?.showMode ?? 'list';
}

export function assetsDialogFoldersPath(state) {
    return state.assetsDialog?.foldersPath ?? [];
}

export function assetsDialogIsUploadingSelector(state) {
    return state.assetsDialog?.upload_started ?? false;
}

export function assetsDialogUploadProgressSelector(state) {
    return state.assetsDialog?.upload_progress ?? null;
}