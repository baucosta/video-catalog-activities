// @flow 
import { FormControl, FormControlLabel, FormHelperText, FormLabel, makeStyles, Radio, RadioGroup, TextField, Theme } from '@material-ui/core';
import * as React from 'react';
import { useForm } from 'react-hook-form';
import castMemberHttp from '../../utils/http/cast-member-http';
import {useEffect, useState} from "react";
import *  as yup from '../../utils/vendor/yup';
import { useHistory, useParams } from 'react-router';
import { useSnackbar } from 'notistack';
import { CastMember, CastMemberTypeMap } from '../../utils/models';
import SubmitActions from '../../components/SubmitActions';
import { DefaultForm } from '../../components/DefaultForm';

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
    const resolver = yup.useYupValidationResolver(validationSchema);

    const {register, handleSubmit, setValue, getValues, errors, reset, watch, trigger} = useForm<CastMember>({
        resolver
    });

    const classes = useStyles();

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

    useEffect(() => {
        if (!id) {
          return ;
        }

        (async function getCastMember() {
            setLoading(true);

            try {
                const {data} = await castMemberHttp.get(id);
                setCastMember(data.data)
                reset(data.data)
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
            const http = !castMember
                ? castMemberHttp.create(formData)
                : castMemberHttp.update(castMember.id, formData)

          const {data} = await http;
           
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
        } catch(error) {
          console.log(error);
          snackbar.enqueueSnackbar(
            'Erro ao salvar membro do elenco',
            {variant: 'error'}
          );
        } finally {
          setLoading(false);
        }
    }

    return (
        <DefaultForm GridItemProps={{xs: 12, md: 6}} onSubmit={handleSubmit(onSubmit)}>
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
            
            <FormControl error={errors.type !== undefined} component="fieldset" className={classes.submit}>
                <FormLabel component="legend">Tipo</FormLabel>
                <RadioGroup name="type" onChange={handleChange}>
                    {CastMemberTypeMap.map(data => (
                        <FormControlLabel 
                            label={data.description} 
                            key={data.type}
                            value={data.type.toString()} 
                            disabled={loading}
                            control={
                                <Radio 
                                    color={"primary"} 
                                    checked={watch('type') == data.type}
                                />
                            } 
                        />
                    ))}
                </RadioGroup>
                {
                    errors.type
                        ? <FormHelperText id="type-helper-text">{errors.type.message}</FormHelperText>
                        : null
                }
            </FormControl>

            <SubmitActions 
                disabledButtons={loading} 
                handleSave={() => 
                    trigger().then(isValid => {
                        isValid && onSubmit(getValues(), null);
                    })
                }
            >
            </SubmitActions>
        </DefaultForm>
    );
};