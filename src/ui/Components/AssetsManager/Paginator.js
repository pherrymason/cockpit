import {useSelector} from "reffects-store";
import {dispatch} from 'reffects';
import {assetsDialogLoading, assetsDialogPageSelector, assetsDialogPagesSelector} from './selectors';
import {ASSET_DIALOG_ASSETS_INIT, ASSET_DIALOG_ASSETS_PAGE_CHANGED} from "./events";

function loadPage(page) {
    dispatch({id: ASSET_DIALOG_ASSETS_PAGE_CHANGED, payload: {page}});
}

function Paginator() {
    const loading = useSelector(assetsDialogLoading);
    const page = useSelector(assetsDialogPageSelector);
    const pages = useSelector(assetsDialogPagesSelector);

    if (loading && pages <= 1) {
        return null;
    }

    const lis = [];
    for (let p=0; p<pages; p++) {
        lis.push(
            <li className="uk-text-small" key={`page_${p}`}>
                <a className="uk-dropdown-close" onClick={() => loadPage(p + 1)}> {App.i18n.get('Page')} {p + 1}</a>
            </li>);
    }

    return (
        <div className="uk-margin uk-flex uk-flex-middle uk-noselect">
            <ul className="uk-breadcrumb uk-margin-remove">
                <li className="uk-active"><span>{page}</span></li>
                <li data-uk-dropdown="mode:'click'">
                    <a><i className="uk-icon-bars"></i> {pages}</a>
                    <div className="uk-dropdown">
                        <strong className="uk-text-small"> {App.i18n.get('Pages')}</strong>
                        <div className="uk-margin-small-top { pages > 5 && 'uk-scrollable-box' }">
                            <ul className="uk-nav uk-nav-dropdown">
                                { lis }
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>

            <div className="uk-button-group uk-margin-small-left">
                {((page-1) > 0) &&
                    <a className="uk-button uk-button-link uk-button-small" onClick={() => loadPage(page+1)}
                    if="{page-1 > 0}"> {App.i18n.get('Previous')}</a>
                }
                {((page+1) <= pages) &&
                    <a className="uk-button uk-button-link uk-button-small" onClick={() => loadPage(page+1)}
                    if="{page+1 <= pages}"> {App.i18n.get('Next')}</a>
                }
            </div>
        </div>
    );
}

export default Paginator;