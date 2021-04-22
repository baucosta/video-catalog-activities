// @flow 
import { Box, Button, Checkbox, FormControlLabel, makeStyles, TextField, Theme } from '@material-ui/core';
import React, { useEffect, useState } from "react";
import {ButtonProps} from "@material-ui/core/Button";
import { useForm } from 'react-hook-form';
import categoryHttp from '../../utils/http/category-http';
import *  as yup from '../../utils/vendor/yup';
import { useHistory, useParams } from 'react-router';
import { useSnackbar } from 'notistack';
import { Category } from './Table';

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        }
    }
});



const validationSchema = yup.object().shape({
    name: yup.string().label('Nome').required("Nome é requerido").max(255)
});

export const Form = () => {
    const resolver = yup.useYupValidationResolver(validationSchema);

    const {register, handleSubmit, setValue, getValues, errors, reset, watch} = useForm<Category>({
        resolver,
        defaultValues: {
          is_active: true
        }
    });

    const classes = useStyles({
        defaultValues: {
            is_active: true
        }
    });

    const snackbar = useSnackbar();
    const history = useHistory();
    const { id } = useParams<{ id: string }>();
    const [category, setCategory] = useState<{id: string} | null>(null);
    const [loading, setLoading] = useState<boolean>(false);

    const buttonProps: ButtonProps = {
      className: classes.submit,
      variant: "contained",
      color: 'secondary',
      disabled: loading
    }


    useEffect(() => {
      register({name: "is_active"})
    }, [register]);

    useEffect(() => {
      if (!id) {
        return ;
      }

      (async function getCategory() {
        setLoading(true);

        try {
            const {data} = await categoryHttp.get(id);
            setCategory(data.data);
            reset(data.data);
        } catch(error) {
            console.log(error);

            snackbar.enqueueSnackbar(
              'Não foi possível carregar as informações',
              {variant: 'error'}
            );
        } finally {
            setLoading(false)
        }
      })()
      
    }, []);

    async function onSubmit(formData, event) {
        setLoading(true);

        try {
          const http = !category
            ? categoryHttp.create(formData)
            : categoryHttp.update(category.id, formData);

          const {data} = await http;
           
          snackbar.enqueueSnackbar(
            'Categoria salva com sucesso',
            {variant: 'success'}
          );

          setTimeout(() => {
            event ? (
              id 
                ? history.replace(`/categories/${data.data.id}/edit`)
                : history.push(`/categories/${data.data.id}/edit`)
            )
            : history.push('/categories')
          });
        } catch(error) {
          console.log(error);
          snackbar.enqueueSnackbar(
            'Erro ao salvar categoria',
            {variant: 'error'}
          );
        } finally {
          setLoading(false);
        }
    }

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <TextField
                name="name"
                label="Nome"
                fullWidth
                variant={"outlined"} 
                inputRef={register}
                InputLabelProps={{shrink: true}}
                disabled={loading}
                error={errors.name !== undefined}
                helperText={errors.name && errors.name.message} 
            />

            <TextField
                name="description"
                label="Descrição" 
                multiline
                rows="4"
                fullWidth
                variant={"outlined"}
                margin={"normal"} 
                inputRef={register}
                InputLabelProps={{shrink: true}} 
                disabled={loading}
            />
            
            <FormControlLabel 
                disabled={loading}
                control={
                  <Checkbox 
                    name="is_active"
                    color={"primary"}
                    onChange={
                      () => setValue('is_active', !getValues()['is_active'])
                    }
                    checked={watch('is_active')}
                  />
                }
                label={'Ativo?'}
                labelPlacement={'end'}
            />

            <Box dir={"rtl"}>
                <Button 
                    {...buttonProps} 
                    type="submit">
                        Salvar e continuar editando
                </Button>
                <Button 
                    color={"primary"} 
                    {...buttonProps} 
                    onClick={() => onSubmit(getValues(), null)}>
                        Salvar
                </Button>
            </Box>
        </form>
    );
};