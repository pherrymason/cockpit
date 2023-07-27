import React, {useCallback, useRef, useState} from 'react';
import ReactTags from 'react-tag-autocomplete';

// TODO Implement
export default function Tags({placeholder, tags = [], onRemove}) {
    const [tagId, setTagId] = useState(0);
    const [inputTags, setTags] = useState(
        tags.map((tag,idx) => ({id: idx, name: tag})));

    const reactTags = useRef()

    const onDelete = useCallback((tagIndex) => {
        setTags(inputTags.filter((_, i) => i !== tagIndex))
    }, [inputTags])

    const onAddition = useCallback((newTag) => {
        let newTagId = tagId + 1;
        setTags([...inputTags, {...newTag, id:newTagId}])
        setTagId(newTagId);
    }, [inputTags])

    return (
        <div className="uk-grid uk-grid-small uk-flex-middle">
            <ReactTags
                ref={reactTags}
                tags={inputTags}
                onDelete={onDelete}
                onAddition={onAddition}
                allowNew={true}
            />
        </div>
    )
}

/*

{tags.map((tag) =>
            <div className="uk-text-primary">
                <span className="field-tag">
                    <i className="uk-icon-tag"></i> {_tag}
                    <a onClick={ onRemove }><i className="uk-icon-close"></i></a>
                </span>
            </div>
        )}

        <div>
            <div refo="autocomplete" className="uk-autocomplete uk-form-icon uk-form">
                <i className="uk-icon-tag"></i>
                <input refo="input" className="uk-width-1-1 uk-form-blank" type="text"
                       placeholder={ App.i18n.get(placeholder || 'Add Tag...') } />
            </div>
        </div>
 */