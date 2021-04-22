import { Box, Button, Checkbox, FormControl, FormControlLabel, FormHelperText, InputLabel, makeStyles, Select, TextField, Theme} from '@material-ui/core';
import {ButtonProps} from "@material-ui/core/Button";
import { useForm } from 'react-hook-form';
import genreHttp from '../../utils/http/genre-http';
import categoryHttp from '../../utils/http/category-http';
import {Category} from '../category/Table';
import {useEffect, useState} from "react";
import { useHistory, useParams } from 'react-router';
import { useSnackbar } from 'notistack';
import *  as yup from '../../utils/vendor/yup';
import { Genre } from './Table';

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

const validationSchema = yup.object().shape({
    name: yup.string().label('Nome').required("Nome é requerido").max(255),
    categories_id: yup.array().required('Uma categoria é requerida'),
});


export const Form = () => {
    const resolver = yup.useYupValidationResolver(validationSchema);

    const {register, handleSubmit, watch, setValue, getValues, errors, reset} = useForm<Genre>({
        resolver,
        defaultValues: {
            categories_id: [],
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
    const [genre, setGenre] = useState<{id: string} | null>(null);
    const [loading, setLoading] = useState<boolean>(false);
    
    const [data, setData] = useState<Category[]>([]);
      
    useEffect(() => {
        register({name: "categories_id"})

        categoryHttp
            .list<{data: Category[]}>()
            .then(({data}) => setData(data.data))
    }, [register]);

    useEffect(() => {
        register({name: "is_active"})
    }, [register]);

    useEffect(() => {
        if (!id) {
          return ;
        }
  
        (async function getGenre() {
            setLoading(true);
    
            try {
                const {data} = await genreHttp.get(id);
                let genreType = data.data as Genre;

                genreType.categories_id = [];
                data.data.categories
                .map(category => genreType.categories_id.push(category.id))

                setGenre(genreType)
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


    const buttonProps: ButtonProps = {
        className: classes.submit,
        variant: "contained",
        color: 'secondary',
        disabled: loading
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

    async function onSubmit(formData, event) {
        setLoading(true);

        try {
            const http = !genre
                ? genreHttp.create(formData)
                : genreHttp.update(genre.id, formData)

            const {data} = await http;
           
            snackbar.enqueueSnackbar(
                'Gênero salvo com sucesso',
                {variant: 'success'}
            );

            setTimeout(() => {
                event ? (
                    id 
                    ? history.replace(`/genres/${data.data.id}/edit`)
                    : history.push(`/genres/${data.data.id}/edit`)
                )
                : history.push('/genres')
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
            
            <FormControl error={errors.categories_id !== undefined} fullWidth className={classes.formControl}>
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
                    <option 
                        key={category.id} 
                        value={category.id}
                    >
                        {category.name}
                    </option>
                ))}
                </Select>

                {
                    errors.categories_id
                        ? <FormHelperText id="type-helper-text">Preencha a categoria</FormHelperText>
                        : null
                }
            </FormControl>
            
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
                <Button {...buttonProps} type="submit">Salvar e continuar editando</Button>
                <Button {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Salvar</Button>
            </Box>
        </form>
    );
};
