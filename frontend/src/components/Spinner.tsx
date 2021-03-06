import * as React from 'react';
import {Fade, LinearProgress, MuiThemeProvider, Theme} from "@material-ui/core";
import {useContext} from "react";

function makeLocalTheme(theme: Theme): Theme {
    return {
        ...theme,
        palette: {
            ...theme.palette,
            primary: theme.palette.error,
            type: 'dark'
        }
    }
}


const Spinner = () => {
    return (
        <MuiThemeProvider theme={makeLocalTheme}>
            <LinearProgress
                color={'primary'}
                style={{
                    position: 'fixed',
                    width: '100%',
                    zIndex: 9999
                }}
            />
        </MuiThemeProvider>
    );
};

export default Spinner;
