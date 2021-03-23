import * as React from 'react';
import { Box, Fab } from '@material-ui/core';
import { Page } from '../../components/Page';
import {Link} from "react-router-dom";
import AddIcon from '@material-ui/icons/Add';
import Table from './Table';

const List = () => {
    return (
        <Page title={'Membros do Elenco'}>
            <Box dir={'rtl'}>
                <Fab
                    title="Adicionar membro"
                    size="small"
                    component={Link}
                    to="/cast-members/create">
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