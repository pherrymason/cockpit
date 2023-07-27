
export function Thumbnail({src, width, height}) {
    return <div className="cp-thumbnail uk-position-relative">
        {/*<i refo="spinner" className="uk-icon-spinner uk-icon-spin uk-position-center"></i>*/}
        <img loading="lazy" src={src} width={width} height={height} />
    </div>
}
