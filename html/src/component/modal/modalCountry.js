import { Textbox } from "react-inputs-validation";
import { Button } from "@material-ui/core";
import Modal from "react-modal";
import React, {  } from "react";
import 'react-inputs-validation/lib/react-inputs-validation.min.css';
import {makeStyles} from "@material-ui/core/styles";


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


function MyButton(props) {
    const { color, ...other } = props;
    const classes = useStylesBt(props);
    return <Button className={classes.root} {...other} />;
}

const ModalCountry = (props) => {


    const styleInputCss = {
        width: '100%', height: '50px', fontsize: '200px', size: '90px'
    }

    return <Modal
            class="seal2-modal"
            isOpen={props.setModalIsOpenAdd}
            style={props.styleModal}
            >
            <p style={{ fontSize:'60px',
                        textAlign: 'center' }}>{props.nameFrom}</p>
            <form id="form-save-data">

                    <Textbox
                        customStyleInput={styleInputCss}
                        id="outlined-basic"
                        label="Name"
                        size={100}
                        variant="outlined"
                        fullWidth={true}
                        attributesInput={{
                            id: 'name',
                            name: 'name',
                            type: 'text',
                            placeholder: 'Name...',
                        }}
                        value={props.valueName}
                        validate={props.validate}
                        onBlur={e => {
                            console.log(e);
                        }}
                        placeholder="Name..."
                        onChange={props.onChangeName}
                        validationOption={{
                            name: 'name',
                            check: true,
                            max: 100,
                            min: 1,
                            required: true
                        }}
                    />

                <br/> <br/>

                    <Textbox
                        customStyleInput={styleInputCss}
                        id="outlined-basic"
                        label="Capital"
                        variant="outlined"
                        fullWidth={true}
                        attributesInput={{
                            id: 'Capital',
                            name: 'capital',
                            type: 'text',
                            placeholder: 'Capital',
                        }}
                        value={props.valueCapital}
                        placeholder="Capital"
                        onChange={props.onChangeCapital}
                        validationOption={{
                            name: 'capital',
                            check: true,
                            max: 100,
                            min: 0,
                            required: false
                        }}
                        onBlur={e => {
                            console.log(e);
                        }}
                        validate={props.validate}
                    />
                <br/> <br/>

                    <Textbox
                        customStyleInput={styleInputCss}
                        id="outlined-basic"
                        label="Region"
                        variant="outlined"
                        fullWidth={true}
                        attributesInput={{
                            id: 'Region',
                            name: 'region',
                            type: 'text',
                            placeholder: 'Region',
                        }}
                        value={props.valueRegion}
                        placeholder="Region"
                        onChange={props.onChangeRegion}
                        validationOption={{
                            name: 'region',
                            check: true,
                            max: 100,
                            min: 0,
                            required: false
                        }}
                        onBlur={e => {
                            console.log(e);
                        }}
                        validate={props.validate}
                    />



                <br/> <br/>


                    <Textbox
                        customStyleInput={styleInputCss}
                        id="outlined-basic"
                        label="Population"
                        variant="outlined"
                        fullWidth={true}
                        attributesInput={{
                            id: 'Population',
                            name: 'population',
                            type: 'number',
                            placeholder: 'Population',
                        }}
                        value={props.valuePopulation}
                        placeholder="Population"
                        onChange={props.onChangePopulation}
                        validationOption={{
                            name: 'population',
                            check: true,
                            max: 15,
                            min: 1,
                            required: true
                        }}
                        onBlur={e => {
                            console.log(e);
                        }}
                        validate={props.validate}
                    /><br/>

                <br/><MyButton variant="contained" size="large" color="blue" fullWidth={true}
                             onClick={props.onClickOk}>Save</MyButton><br/><br/>
                <MyButton variant="contained" size="large" color="red" fullWidth={true}
                        onClick={props.onClickClose}>Close</MyButton>
            </form>

        </Modal>



}

export  default ModalCountry