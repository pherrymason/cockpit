import {useSelector} from 'reffects-store';
import {dispatch} from 'reffects';
import {assetsDialogFoldersPath} from "./selectors";
import {changeDir} from './Folders';

function PathBreadCrumb() {
    const foldersPath = useSelector(assetsDialogFoldersPath);

    return (
        <div className="uk-margin">
            <ul className="uk-breadcrumb">
                <li onClick={ () => changeDir({_id:null}) }>
                    <a title={ App.i18n.get('Change dir to root') }>
                        <i className="uk-icon-home"></i>
                    </a>
                </li>
                {foldersPath.map((folder, idx) => (
                    <li key={`folder${idx}`}>
                        <a onClick={ () => changeDir(folder) } title="Change dir to { folder.name }">{folder.name}</a>
                    </li>
                ))}
            </ul>
        </div>
    )
}
export default PathBreadCrumb;