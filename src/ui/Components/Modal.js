function Modal({open, title, children}) {
    const style = open ? {display: 'block', 'overflowY': 'scroll'}:{};


    return (
        <div
            className="uk-modal uk-open" tabIndex="-1" aria-labelledby="exampleModalLabel"
             aria-hidden="true" style={style}>
            <div className="uk-modal-dialog uk-modal-dialog-large">
                <div className="modal-content">
                    <div className="uk-modal-header uk-text-large">
                        <h1 className="modal-title fs-5" id="exampleModalLabel">{title}</h1>
                        <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div className="modal-body">
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}

export default Modal;