
export function Thumbnail({path, width, height}) {
    return <div className="cp-thumbnail uk-position-relative">
        <i refo="spinner" className="uk-icon-spinner uk-icon-spin uk-position-center"></i>
        <img loading="lazy" src={path} width={width} height={height} />
    </div>
}
