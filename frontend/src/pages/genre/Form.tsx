import { Checkbox, FormControl, FormControlLabel, FormHelperText, InputLabel, makeStyles, Select, TextField, Theme} from '@material-ui/core';
import { useForm } from 'react-hook-form';
import genreHttp from '../../utils/http/genre-http';
import categoryHttp from '../../utils/http/category-http';
import {useEffect, useState} from "react";
import { useHistory, useParams } from 'react-router';
import { useSnackbar } from 'notistack';
import *  as yup from '../../utils/vendor/yup';
import { Category, Genre } from '../../utils/models';
import SubmitActions from '../../components/SubmitActions';
import { DefaultForm } from '../../components/DefaultForm';

const useStyles = makeStyles((theme: Theme) => {
    return {
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

    const {register, handleSubmit, watch, setValue, getValues, errors, reset, trigger} = useForm<Genre>({
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
    const [genre, setGenre] = useState<Genre | null>(null);
    const [loading, setLoading] = useState<boolean>(false);
    
    const [getCategories, setCategories] = useState<Category[]>([]);

    useEffect(() => {
        register({name: "categories_id"})
        register({name: "is_active"})
    }, [register]);

    useEffect(() => {
        let isSubscribed = true;
        (async () => {
            setLoading(true);
    
            try {
                const promises = [categoryHttp.list({queryParams: {all: ''}})];

                if (id) {
                    promises.push(genreHttp.get(id));
                }

                const [categories, genres] = await Promise.all(promises);

                if (isSubscribed) {
                    setCategories(categories.data.data);
                    
                    if (id) {
                        let genreType = genres.data.data as Genre;
                        genreType.categories_id = [];
                        genres.data.data.categories
                        .map(category => genreType.categories_id.push(category.id))
                        
                        setGenre(genreType)
                        reset(genres.data.data)
                    }
                }
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

        return () => {
            isSubscribed = false;
        }
    }, []);
    

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
                {getCategories.map((category: Category) => (
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
