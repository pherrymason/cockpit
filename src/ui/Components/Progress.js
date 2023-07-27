export default function Progress() {
    return (
        <div refo="uploadprogress" className="uk-margin uk-hidden">
            <div className="uk-progress">
                <div refo="progressbar" className="uk-progress-bar" style={{width: '0%'}}>&nbsp;</div>
            </div>
        </div>
    )
}