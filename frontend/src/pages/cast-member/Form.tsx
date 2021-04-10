// @flow 
import { Box, Button, FormControl, FormControlLabel, FormLabel, makeStyles, Radio, RadioGroup, TextField, Theme } from '@material-ui/core';
import * as React from 'react';
import {ButtonProps} from "@material-ui/core/Button";
import { useForm } from 'react-hook-form';
import castMemberHttp from '../../utils/http/cast-member-http';
import {CastMemberTypeMap} from './Table';
import {useEffect} from "react";

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        }
    }
})

export const Form = () => {

    const classes = useStyles();

    const buttonProps: ButtonProps = {
        className: classes.submit,
        variant: "contained",
        color: 'secondary',
    }

    const {register, handleSubmit, setValue, getValues} = useForm();

    useEffect(() => {
        register({name: "type"})
    }, [register])

    const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        setValue("type", parseInt((event.target as HTMLInputElement).value));
    };

    function onSubmit(formData, event) {
        console.log(formData);
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
                inputRef={register} />
            
            <FormControl component="fieldset" className={classes.submit}>
                <FormLabel component="legend">Tipo</FormLabel>
                <RadioGroup name="type" onChange={handleChange}>
                    {CastMemberTypeMap.map(data => (
                        <FormControlLabel 
                            label={data.description} 
                            key={data.value}
                            value={data.value.toString()} 
                            control={<Radio color={"primary"} />} 
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