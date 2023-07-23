import { coeffect, registerCoeffectHandler } from 'reffects';

const COEFFECT_NAME = 'environment';

export default function registerEnvironmentCoeffect(variables) {
    registerCoeffectHandler(COEFFECT_NAME, () => ({
        [COEFFECT_NAME]: {
            apiEndpoint: variables.API_ENDPOINT,
        },
    }));
}

export function environment() {
    return coeffect(COEFFECT_NAME);
}