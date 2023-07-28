export default function Progress({progress = 0}) {
    return (
        <div refo="uploadprogress" className="uk-margin">
            <div className="uk-progress">
                <div refo="progressbar" className="uk-progress-bar" style={{width: `${progress}%`}}>&nbsp;</div>
            </div>
        </div>
    )
}