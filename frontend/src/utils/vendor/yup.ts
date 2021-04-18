import { setLocale } from 'yup';
import { useCallback } from "react";

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


export const useYupValidationResolver = validationSchema =>
  useCallback(
    async data => {
      try {
        const values = await validationSchema.validate(data, {
          abortEarly: false
        });

        return {
          values,
          errors: {}
        };
      } catch (errors) {
        return {
          values: {},
          errors: errors.inner.reduce(
            (allErrors, currentError) => ({
              ...allErrors,
              [currentError.path]: {
                type: currentError.type ?? "validation",
                message: currentError.message
              }
            }),
            {}
          )
        };
      }
    },
    [validationSchema]
  );

export * from 'yup';