import * as React from 'react';
import { Box, Fab } from '@material-ui/core';
import { Page } from '../../components/Page';
import {Link} from "react-router-dom";
import AddIcon from '@material-ui/icons/Add';
import Table from './Table';

const List = () => {
    return (
        <Page title={'Listagem de Vídeos'}>
            <Box dir={'rtl'} paddingBottom={2}>
                <Fab
                    title="Adicionar vídeo"
                    color={'secondary'}
                    size="small"
                    component={Link}
                    to="/videos/create">
                        <AddIcon/>
                </Fab>
            </Box>
                <Table/>
            <Box>
                
            </Box>
        </Page>
    );
};

export default List;