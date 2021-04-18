// @flow 
import { Box, Button, FormControl, FormControlLabel, FormLabel, makeStyles, Radio, RadioGroup, TextField, Theme } from '@material-ui/core';
import * as React from 'react';
import {ButtonProps} from "@material-ui/core/Button";
import { useForm } from 'react-hook-form';
import castMemberHttp from '../../utils/http/cast-member-http';
import {CastMember, CastMemberTypeMap} from './Table';
import {useEffect, useState} from "react";
import *  as yup from '../../utils/vendor/yup';
import { useHistory, useParams } from 'react-router';
import { useSnackbar } from 'notistack';

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        }
    }
})

const validationSchema = yup.object().shape({
    name: yup.string().label('Nome').required("Nome é requerido").max(255),
    type: yup.number().required("Tipo é requerido").positive().integer(),
});


export const Form = () => {

    const classes = useStyles();

    const resolver = yup.useYupValidationResolver(validationSchema);

    const {register, handleSubmit, setValue, getValues, errors, reset, watch} = useForm<CastMember>({
        resolver
    });

    useEffect(() => {
        register({name: "type"})
    }, [register])

    const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        setValue("type", parseInt((event.target as HTMLInputElement).value));
    };

    const snackbar = useSnackbar();
    const history = useHistory();
    const { id } = useParams<{ id: string }>();
    const [castMember, setCastMember] = useState<{id: string} | null>(null);
    const [loading, setLoading] = useState<boolean>(false);

    const buttonProps: ButtonProps = {
        className: classes.submit,
        variant: "contained",
        color: 'secondary',
        disabled: loading
    }

    useEffect(() => {
        if (!id) {
          return ;
        }
  
        setLoading(true);
  
        castMemberHttp
              .get(id)
              .then(({data}) => {
                    setCastMember(data.data)
                    reset(data.data)
              })
              .finally(() => setLoading(false))
      }, []);

    function onSubmit(formData, event) {
        setLoading(true);

        const http = !castMember
          ? castMemberHttp.create(formData)
          : castMemberHttp.update(castMember.id, formData)

          http
            .then(({data}) => {
              snackbar.enqueueSnackbar(
                'Membro do elenco salvo com sucesso',
                {variant: 'success'}
              );

              setTimeout(() => {
                event ? (
                  id 
                    ? history.replace(`/cast_members/${data.data.id}/edit`)
                    : history.push(`/cast_members/${data.data.id}/edit`)
                )
                : history.push('/cast_members')
              });
            })
            .catch((error) => {
              console.log(error);
              snackbar.enqueueSnackbar(
                'Erro ao salvar membro do elenco',
                {variant: 'error'}
              );
            })
            .finally(() => setLoading(false))

        castMemberHttp
            .create(formData)
            .then((response) => console.log(response))
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
            
            <FormControl component="fieldset" className={classes.submit}>
                <FormLabel component="legend">Tipo</FormLabel>
                <RadioGroup name="type" onChange={handleChange}>
                    {CastMemberTypeMap.map(data => (
                        <FormControlLabel 
                            label={data.description} 
                            key={data.value}
                            value={data.value.toString()} 
                            disabled={loading}
                            control={
                                <Radio 
                                    color={"primary"} 
                                    checked={watch('type') == data.value}
                                />
                            } 
                        />
                    ))}
                </RadioGroup>
            </FormControl>

            <Box dir={"rtl"}>
                <Button {...buttonProps} type="submit">Salvar e continuar editando</Button>
                <Button {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Salvar</Button>
            </Box>
        </form>
    );
};