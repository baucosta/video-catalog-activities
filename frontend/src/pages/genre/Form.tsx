import { Box, Button, Checkbox, FormControl, InputLabel, makeStyles, Select, TextField, Theme} from '@material-ui/core';
import * as React from 'react';
import {ButtonProps} from "@material-ui/core/Button";
import { useForm } from 'react-hook-form';
import genreHttp from '../../utils/http/genre-http';
import categoryHttp from '../../utils/http/category-http';
import {Category} from '../category/Table';
import {useEffect, useState} from "react";

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        },
        formControl: {
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

    const {register, handleSubmit, watch, setValue, getValues} = useForm({
        defaultValues: {
            categories_id: []
        }
    });

    const [data, setData] = useState<Category[]>([]);

    useEffect(() => {
        register({name: "categories_id"})

        categoryHttp
            .list<{data: Category[]}>()
            .then(({data}) => setData(data.data))
    }, [register]);


    const buttonProps: ButtonProps = {
        className: classes.submit,
        variant: "contained",
        color: 'secondary',
    }

    

    const handleChangeMultiple = (event: React.ChangeEvent<{ value: unknown }>) => {
        const { options } = event.target as HTMLSelectElement;
        const value: string[] = [];

        for (let i = 0, l = options.length; i < l; i += 1) {
            if (options[i].selected) {
                value.push(options[i].value);
            }
        }
        setValue("categories_id", value);
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
            
            <FormControl fullWidth className={classes.formControl}>
                <InputLabel shrink htmlFor="select-multiple-native">
                    Categorias
                </InputLabel>
                <Select
                    name="categories_id"
                    value={watch('categories_id')}
                    multiple
                    native
                    onChange={handleChangeMultiple}
                    inputProps={{
                        id: 'select-multiple-native',
                    }}
                >
                {data.map((category: Category) => (
                    <option key={category.id} value={category.id}>
                        {category.name}
                    </option>
                ))}
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
