import React from 'react';
// import './App.css';
import { Box, CssBaseline, MuiThemeProvider } from '@material-ui/core';
import { Navbar } from './components/Navbar';
import { BrowserRouter } from 'react-router-dom';
import AppRouter from './routes/AppRouter';
import Breadcrumbs from './components/Breadcrumbs';
import theme from './theme';

const App: React.FC = () => {
  return (
    <React.Fragment>
      <MuiThemeProvider theme={theme}>
        <CssBaseline/>
        <BrowserRouter>
          <Navbar/>
          <Box paddingTop={'70px'}>
            <Breadcrumbs/>
            <AppRouter/>
          </Box>
        </BrowserRouter>
      </MuiThemeProvider>
    </React.Fragment>
  );
}

export default App;
