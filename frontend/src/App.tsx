import React from 'react';
import logo from './logo.svg';
// import './App.css';
import { Box, Button } from '@material-ui/core';
import { Navbar } from './components/Navbar';
import { Page } from './components/Page';

const App: React.FC = () => {
  return (
    <React.Fragment>
      <Navbar/>
      <Box paddingTop={'70px'}>
        <Page title={'Categorias'}>
          Conteúdo
        </Page>
      </Box>
    </React.Fragment>
  );
}

export default App;
