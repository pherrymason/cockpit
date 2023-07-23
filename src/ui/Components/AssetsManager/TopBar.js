function getRefValue() {}

function updateFilter() {}
function addFolder() {}
function removeSelected() {}

function TopBar({selected = [], toggleListMode = () => {}}) {
    return (
        <div className="uk-grid uk-grid-width-1-2">
            <div>
                <div className="uk-grid uk-grid-small uk-flex-middle">
                    <div>
                        <div className="uk-form-select">
                            <span
                                className="uk-button uk-button-large getRefValue('filtertype') && 'uk-button-primary' uk-text-capitalize">
                                <i className="uk-icon-eye uk-margin-small-right"></i>
                                {getRefValue('filtertype') || App.i18n.get('All')}
                            </span>

                            <select refo="filtertype" onChange={updateFilter }
                                    aria-label="{App.i18n.get('Mime Type')}">
                                <option value="">All</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                                <option value="audio">Audio</option>
                                <option value="document">Document</option>
                                <option value="archive">Archive</option>
                                <option value="code">Code</option>
                            </select>
                        </div>
                    </div>
                    <div className="uk-flex-item-1">
                        <div className="uk-form-icon uk-display-block uk-width-1-1">
                            <i className="uk-icon-search"></i>
                            <input className="uk-width-1-1 uk-form-large" type="text"
                                   aria-label="{ App.i18n.get('Search assets') }" refo="filtertitle"
                                   onChange={ updateFilter } />
                        </div>
                    </div>
                </div>
            </div>
            <div className="uk-text-right">
                <button className="uk-button uk-button-large uk-button-danger" type="button"
                        onClick={ removeSelected }
                        show={ selected.length }>
                    {App.i18n.get('Delete')} <span
                    className="uk-badge uk-badge-contrast uk-margin-small-left">{selected.length}</span>
                </button>

                <button className="uk-button uk-button-large uk-button-link"
                        onClick={addFolder}>{App.i18n.get('Add folder')}</button>

                <span className="uk-button-group uk-button-large">
                        <button className="uk-button uk-button-large {listmode=='list' && 'uk-button-primary'}"
                                type="button" onClick={ toggleListMode }
                                aria-label="{ App.i18n.get('Switch to list-mode') }"><i
                            className="uk-icon-list"></i></button>
                        <button className="uk-button uk-button-large {listmode=='grid' && 'uk-button-primary'}"
                                type="button" onClick={ toggleListMode }
                                aria-label="{ App.i18n.get('Switch to tile-mode') }"><i
                            className="uk-icon-th"></i></button>
                    </span>

                <span className="uk-button uk-button-large uk-button-primary uk-form-file">
                        <input className="js-upload-select" aria-label="{ App.i18n.get('Select file') }" type="file"
                               multiple={true} />
                        <i className="uk-icon-upload"></i>
                    </span>
            </div>
        </div>
    )
}

export default TopBar;