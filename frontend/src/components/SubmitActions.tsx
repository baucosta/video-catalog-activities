// @flow 
import * as React from 'react';
import { Box, Button, makeStyles, Theme} from '@material-ui/core';
import {ButtonProps} from "@material-ui/core/Button";

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        },
    }
})

interface SubmitActionsProps {
    disabledButtons?: boolean; 
    handleSave: () => void
}
  
const SubmitActions: React.FC<SubmitActionsProps> = (props) => {
    const classes = useStyles();

    const buttonProps: ButtonProps = {
        className: classes.submit,
        variant: "contained",
        color: 'secondary',
        disabled: props.disabledButtons === undefined ? false : props.disabledButtons
    };

    return (
        <Box dir={"rtl"}>
            <Button {...buttonProps} type="submit">Salvar e continuar editando</Button>
            <Button {...buttonProps} onClick={props.handleSave}>Salvar</Button>
        </Box>
    );
};

export default SubmitActions;