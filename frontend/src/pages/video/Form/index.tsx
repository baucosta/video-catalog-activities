// @flow 
import { Card, CardContent, Checkbox, FormControlLabel, Grid, TextField, Typography, useMediaQuery, Theme, useTheme, makeStyles } from '@material-ui/core';
import React, { MutableRefObject, useEffect, useRef, useState, createRef } from "react";
import { useForm } from 'react-hook-form';
import *  as yup from '../../../utils/vendor/yup';
import { useHistory, useParams } from 'react-router';
import { useSnackbar } from 'notistack';
import { Video, VideoFileFieldsMap } from '../../../utils/models';
import SubmitActions from '../../../components/SubmitActions';
import { DefaultForm } from '../../../components/DefaultForm';
import videoHttp from '../../../utils/http/video-http';
import RatingField from './RatingField';
import UploadField from './UploadField';
import { zipObject } from 'lodash';
import { InputFileComponent } from '../../../components/InputFile';

const useStyles = makeStyles((theme: Theme) => ({
    cardUpload: {
        borderRadius: "4px",
        backgroundColor: "#f5f5f5",
        margin: theme.spacing(2, 0)
    },
    cardOpened: {
        borderRadius: "4px",
        backgroundColor: "#f5f5f5",
    },
    cardContentOpened: {
        paddingBottom: theme.spacing(2) + 'px !important'
    },
}));

const validationSchema = yup.object().shape({
    title: yup.string()
        .label('Título')
        .required()
        .max(255),
    description: yup.string()
        .label('Sinopse')
        .required(),
    year_launched: yup.number()
        .label('Ano de lançamento')
        .required()
        .min(1),
    duration: yup.number()
        .label('Duração')
        .required()
        .min(1),
    rating: yup.string()
        .label('Classificação')
        .required(),
});

const fileFields = Object.keys(VideoFileFieldsMap);

export const Form = () => {
    const resolver = yup.useYupValidationResolver(validationSchema);

    const {
        register, 
        handleSubmit, 
        getValues,
        setValue, 
        errors, 
        reset, 
        watch, 
        trigger
    } = useForm<Video>({
        resolver,
        defaultValues: {
          opened: true
        }
    });

    const snackbar = useSnackbar();
    const classes = useStyles();
    const history = useHistory();
    const { id } = useParams<{ id: string }>();
    const [video, setVideo] = useState<Video | null>(null);
    const [loading, setLoading] = useState<boolean>(false);
    const theme = useTheme()
    const isGreaterMd = useMediaQuery(theme.breakpoints.up('md'));
    const uploadsRef = useRef(
        zipObject(fileFields, fileFields.map(() => createRef()))
    ) as MutableRefObject<{ [key: string]: MutableRefObject<InputFileComponent> }>;

    useEffect(() => {
        [
            'rating',
            'opened',
            'cast_members',
            'genres',
            'categories',
            ...fileFields
        ].forEach(name => register({name}));
    }, [register]);

    useEffect(() => {
      if (!id) {
        return ;
      }
      let isSubscribed = true;

      (async() => {
        setLoading(true);

        try {
            const {data} = await videoHttp.get(id);
            if (isSubscribed) {
                setVideo(data.data);
                reset(data.data);
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
      })();
      return () => {
          isSubscribed = false;
      }
      
    }, []);

    async function onSubmit(formData, event) {
        setLoading(true);

        try {
          const http = !video
            ? videoHttp.create(formData)
            : videoHttp.update(video.id, formData);

          const {data} = await http;
           
          snackbar.enqueueSnackbar(
            'Vídeo salva com sucesso',
            {variant: 'success'}
          );

          setTimeout(() => {
            event ? (
              id 
                ? history.replace(`/videos/${data.data.id}/edit`)
                : history.push(`/videos/${data.data.id}/edit`)
            )
            : history.push('/videos')
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
        <DefaultForm GridItemProps={{xs: 12}} onSubmit={handleSubmit(onSubmit)}>
            <Grid container spacing={5}>
                <Grid item xs={12} md={6}>
                    <TextField
                        name="title"
                        label="Títlo"
                        fullWidth
                        variant={"outlined"} 
                        inputRef={register}
                        InputLabelProps={{shrink: true}}
                        disabled={loading}
                        error={errors.title !== undefined}
                        helperText={errors.title && errors.title.message} 
                    />

                    <TextField
                        name="description"
                        label="Sinopse" 
                        multiline
                        rows="4"
                        fullWidth
                        variant={"outlined"}
                        margin={"normal"} 
                        inputRef={register}
                        InputLabelProps={{shrink: true}} 
                        disabled={loading}
                        error={errors.description !== undefined}
                        helperText={errors.description && errors.description.message} 
                    />

                    <Grid container spacing={1}>
                        <Grid item xs={6}>
                            <TextField
                                name="year_launched"
                                label="Ano de lançamento" 
                                type="number"
                                margin={"normal"} 
                                variant={"outlined"}
                                fullWidth
                                inputRef={register}
                                disabled={loading}
                                InputLabelProps={{shrink: true}} 
                                error={errors.year_launched !== undefined}
                                helperText={errors.year_launched && errors.year_launched.message} 
                            />
                        </Grid>
                        <Grid item xs={6}>
                            <TextField
                                name="duration"
                                label="Duração" 
                                type="number"
                                margin={"normal"} 
                                variant={"outlined"}
                                fullWidth
                                inputRef={register}
                                disabled={loading}
                                InputLabelProps={{shrink: true}} 
                                error={errors.duration !== undefined}
                                helperText={errors.duration && errors.duration.message} 
                            />
                        </Grid>
                    </Grid>
                    Elenco
                    <br />
                    Gêneros e Categorias
                </Grid>

                <Grid item xs={12} md={6}>
                    <RatingField
                        value={watch('rating')}
                        error={errors.rating}
                        disabled={loading}
                        setValue={(value) => setValue('rating', value)}
                        FormControlProps={{
                            margin: isGreaterMd ? 'none' : 'normal'
                        }}
                    />
                    <br />
                    <Card className={classes.cardUpload}>
                        <CardContent>
                            <Typography color="primary" variant="h6">
                                Imagens
                            </Typography>
                            <UploadField
                                ref={uploadsRef.current['thumb_file']}
                                accept={'image/*'}
                                label={'Thumb'}
                                setValue={(value) => setValue('thumb_file', value)}
                            />
                            <UploadField
                                ref={uploadsRef.current['banner_file']}
                                accept={'image/*'}
                                label={'Banner'}
                                setValue={(value) => setValue('banner_file', value)}
                            />
                        </CardContent>
                    </Card>

                    <Card className={classes.cardUpload}>
                        <CardContent>
                            <Typography color="primary" variant="h6">
                                Videos
                            </Typography>
                            <UploadField
                                ref={uploadsRef.current['trailer_file']}
                                accept={'video/mp4'}
                                label={'Trailer'}
                                setValue={(value) => setValue('trailer_file', value)}
                            />
                            <UploadField
                                ref={uploadsRef.current['video_file']}
                                accept={'video/mp4'}
                                label={'Principal'}
                                setValue={(value) => {
                                    setValue('video_file', value)
                                    console.log(getValues());
                                }}
                            />
                        </CardContent>
                    </Card>
                    <br />

                    <FormControlLabel 
                        control={
                            <Checkbox 
                                name="opened"
                                color={"primary"}
                                onChange={
                                    () => setValue('opened', !getValues()['opened'])
                                }
                                checked={watch('opened')}
                                disabled={loading}
                            />
                        }
                        label={
                            <Typography color="primary" variant={"subtitle2"}>
                                Quer que este conteúdo apareça na seção lançamentos
                            </Typography>
                        }
                        labelPlacement={'end'}
                    />
                </Grid>
            </Grid>

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