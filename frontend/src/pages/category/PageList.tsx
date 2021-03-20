import * as React from 'react';
import { Box, Fab } from '@material-ui/core';
import { Page } from '../../components/Page';
import {Link} from "react-router-dom";
import AddIcon from '@material-ui/icons/Add';
import Table from './Table';

const List = () => {
    return (
        <Page title={'Listagem de Categorias'}>
            <Box dir={'rtl'}>
                <Fab
                    title="Adicionar categoria"
                    size="small"
                    component={Link}
                    to="/categories/create">
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