import { setLocale } from 'yup';

setLocale({
    mixed: {
        required: '${path} é requerido',
    },
    string: {
        max: '${path} precisa ter no máximo ${max} caracteres',
    },
    number: {
        min: '${path} precisa ser no mínimo ${min}',
    },
});

export * from 'yup';