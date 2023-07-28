import Gravatar from "./Gravatar";

export default function Account({account, opts = {}}) {

    return (
        <>
            {!account && <span className="uk-icon-spinner uk-icon-spin"></span>}
            {account &&
                <>
                    <span className="uk-flex-inline uk-flex-middle">
                    <Gravatar
                        email={account.emailHash} alt={account.name || 'Unknown'}
                        size={ opts.size || 25 }
                        title={ opts.label === false && (account.name || 'Unknown') }
                        data-uk-tooltip />
                        {opts.label &&
                            <span className="uk-margin-small-left">{account.name || 'Unknown'}</span>
                        }
                    </span>
                </>
            }
        </>
    )
}