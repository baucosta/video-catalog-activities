// @flow 
import { Box, Button, Checkbox, FormControl, InputLabel, makeStyles, Select, TextField, Theme } from '@material-ui/core';
import * as React from 'react';
import {ButtonProps} from "@material-ui/core/Button";
import { useForm } from 'react-hook-form';
import genreHttp from '../../utils/http/genre-http';

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        },
        selectField: {
            margin: theme.spacing(1),
        },
    }
})

export const Form = () => {

    const classes = useStyles({
        defaultValues: {
            is_active: true
        }
    });

    const buttonProps: ButtonProps = {
        className: classes.submit,
        variant: "outlined",
    }

    const {register, handleSubmit, setValue, getValues} = useForm();

    const handleChange = (event, value) => {
        console.log(event.target.value);
        setValue("type", value);
    };

    function onSubmit(formData, event) {
        console.log(formData);
        genreHttp
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
            
            <FormControl fullWidth className={classes.selectField}>
                <InputLabel htmlFor="age-native-simple">Age</InputLabel>
                <Select
                    {...register("categories")}
                    native
                    onChange={handleChange}
                    inputProps={{
                        name: 'age',
                        id: 'age-native-simple',
                    }}>
                    <option aria-label="None" value="" />
                    <option value={10}>Ten</option>
                    <option value={20}>Twenty</option>
                    <option value={30}>Thirty</option>
                </Select>
            </FormControl>
            
            <Checkbox 
                name="is_active"
                inputRef={register}
                defaultChecked
                />
            Ativo?

            <Box dir={"rtl"}>
                <Button {...buttonProps} type="submit">Salvar e continuar editando</Button>
                <Button {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Salvar</Button>
            </Box>
        </form>
    );
};