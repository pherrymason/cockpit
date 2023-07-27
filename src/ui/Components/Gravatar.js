export default function Gravatar({size, email, alt, title}) {
    return <div className="uk-responsive-width uk-border-circle" width={ size } height={ size }>
        <img
            src={`https://www.gravatar.com/avatar/${email}`}
            alt={alt}
            className="uk-responsive-width uk-border-circle"
            width={ size } height={ size }
        />
    </div>
}