export function assetsDialogLoading(state) {
    return state.assetsDialog?.loading ?? false;
}

export function assetsDialogFolders(state) {
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