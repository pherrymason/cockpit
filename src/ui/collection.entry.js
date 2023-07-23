import ReactDOM from 'react-dom';
import AssetsDialog from './Components/AssetsManager/AssetsDialog';
import { dispatch } from 'reffects';
import httpClient from './httpClient';
import { registerStateBatteries, store } from 'reffects-store';
import registerHttpEffects from './effects/http';
import registerEnvironmentCoeffect from './environment';


store.initialize({});
registerStateBatteries();
registerHttpEffects({
    dispatch,
    httpClient,
});
registerEnvironmentCoeffect({
    API_ENDPOINT: process.env.API_ENDPOINT,
});

window.AssetsDialogController = {
    displayAssetsDialog: undefined,
    onSubmit: () => {}
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

ReactDOM.render(<App/>, document.getElementById("vue-app"));