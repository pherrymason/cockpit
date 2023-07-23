function changeDir() {}

function PathBreadCrumb({foldersPath = []}) {
    return (
        <div className="uk-margin">
            <ul className="uk-breadcrumb">
                <li onClick={ changeDir }>
                    <a title="{ App.i18n.get('Change dir to root') }">
                        <i className="uk-icon-home"></i>
                    </a>
                </li>
                {foldersPath.map((folder, idx) => (
                    <li key={`folder${idx}`}>
                        <a onClick={ changeDir } title="Change dir to { folder.name }">{folder.name}</a>
                    </li>
                ))}
            </ul>
        </div>
    )
}
export default PathBreadCrumb;