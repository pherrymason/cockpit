import axios from 'redaxios';

export default {
    get({ url, successFn, errorFn, config }) {
        axios
            .get(url, {
                withCredentials: true,
                headers: config ? config.headers : {},
            })
            .then(({ data }) => successFn(data))
            .catch(errorFn);
    },
    put({ url, body, successFn, errorFn }) {
        axios
            .put(url, body, {
                withCredentials: true,
                headers: {
                    'Content-Type': 'application/json',
                },
            })
            .then(({ data }) => successFn(data))
            .catch(errorFn);
    },
    post({
             url,
             body,
             config = { headers: { 'Content-Type': 'application/json' } },
             successFn,
             errorFn,
         }) {
        axios
            .post(url, body, {
                withCredentials: true,
                headers: config.headers,
            })
            .then(({ data }) => successFn(data))
            .catch(errorFn);
    },
    patch({ url, body, successFn, errorFn }) {
        axios({
            // We must force method in uppercase to avoid CORS error
            method: 'PATCH',
            url,
            data: body,
            withCredentials: true,
            headers: {
                'Content-Type': 'application/json',
            },
        })
            .then(({ data }) => successFn(data))
            .catch(errorFn);
    },
    delete({ url, successFn, errorFn }) {
        axios
            .delete(url, {
                withCredentials: true,
                headers: {
                    'Content-Type': 'application/json',
                },
            })
            .then(({ data }) => successFn(data))
            .catch(errorFn);
    },
};
