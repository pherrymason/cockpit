export function getIconCls(path) {

    var name = path.toLowerCase();

    if (name.match(typefilters.image)) {

        return 'image';

    } else if (name.match(typefilters.video)) {

        return 'video-camera';

    } else if (name.match(typefilters.audio)) {

        return 'music';

    } else if (name.match(typefilters.document)) {

        return 'file-text-o';

    } else if (name.match(typefilters.code)) {

        return 'code';

    } else if (name.match(typefilters.archive)) {

        return 'archive';

    } else {
        return 'paperclip';
    }
}