import {registerStateBatteries, store} from "reffects-store";
import registerHttpEffects from "./effects/http";
import {dispatch} from "reffects";
import httpClient from "./httpClient";
import registerEnvironmentCoeffect from "./environment";
import AssetsDialog from "./Components/AssetsManager/AssetsDialog";
import ReactDOM from "react-dom";

console.log('sinleton.form!');

store.initialize({});
registerStateBatteries();
registerHttpEffects({
    dispatch,
    httpClient,
});
registerEnvironmentCoeffect({
    API_ENDPOINT: API_ENDPOINT,
});

window.AssetsDialogController = {
    displayAssetsDialog: undefined,
    onSubmit: undefined,
};
const displayAssetsDialogSetter = function (showDialog) {
    window.AssetsDialogController.displayAssetsDialog = showDialog;
}

function App() {
    return (
        <>
            <AssetsDialog showFunction={displayAssetsDialogSetter} externalController={AssetsDialogController} />
        </>
    );
}

ReactDOM.render(<App/>, document.getElementById("sepia-app"));