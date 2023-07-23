import {
    effects,
    getEffectHandler,
    registerEffectHandler,
    registerEventHandler,
} from 'reffects';
import { http, registerHttpBatteries } from 'reffects-batteries';

const HTTP_NOT_AUTHENTICATED = 401;
const HTTP_SERVICE_UNAVAILABLE = 503;

export default function registerHttpEffects({ httpClient }) {
    registerHttpBatteries(httpClient);
    registerEffectHandler(
        'http.post.multipart',
        withFormDataBody(getEffectHandler('http.post'))
    );
}


export function withFormDataBody(effectHandler) {
    return ({ body, files = [], ...data }) => {
        const formData = addObject(body);

        files.forEach(({ name, file }) => {
            formData.append(name, file);
        });

        const decoratedData = {
            ...data,
            body: formData,
        };

        effectHandler(decoratedData);
    };
}

export function postMultipart({ files, ...params }) {
    return {
        'http.post.multipart': {
            ...http.post(params)['http.post'],
            files,
        },
    };
}

function createEvent(response, event) {
    if (!Array.isArray(event)) {
        return {
            id: event,
            payload: [response],
        };
    }

    const [id, ...args] = event;
    return { id, payload: [response].concat(args) };
}
