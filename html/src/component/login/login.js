import * as React from "react";
import {makeStyles} from "@material-ui/core/styles";
import {Button} from "@material-ui/core";




const useStylesBt = makeStyles({
    root: {
        background: (props) =>
            props.color === 'red'
                ? 'linear-gradient(45deg, #FE6B8B 30%, #FF8E53 90%)'
                : 'linear-gradient(45deg, #2196F3 30%, #21CBF3 90%)',
        border: 0,
        borderRadius: 3,
        boxShadow: (props) =>
            props.color === 'red'
                ? '0 3px 5px 2px rgba(255, 105, 135, .3)'
                : '0 3px 5px 2px rgba(33, 203, 243, .3)',
        color: 'white',
        height: 48,
        padding: '0 30px',
        margin: 8,
    },
});





const FormLogin = (props) => {


    const params = (new URL(window.location)).searchParams;


    function MyButton(props) {
        const { color, ...other } = props;
        const classes = useStylesBt(props);
        return <Button className={classes.root} {...other} />;
    }
        return <div>
            <h1>{params.get('username')}</h1>

                    <MyButton
                    href="/api/login/google"
                     >LOGIN</MyButton>
                    <MyButton
                        href="/api/logout"
                    >LOGOUT</MyButton><br/><br/>
                </div>
}


export  default FormLogin