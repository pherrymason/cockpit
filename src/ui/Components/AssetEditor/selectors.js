export function editingAssetSelector(state) {
    return state.assetEditor?.asset ?? null;
}