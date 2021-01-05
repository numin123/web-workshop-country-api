import * as React from 'react';
import { DataGrid } from '@material-ui/data-grid';
import { Button, IconButton } from "@material-ui/core";

import DeleteIcon from '@material-ui/icons/Delete';
import EditIcon from '@material-ui/icons/Edit';

import Swal from 'sweetalert2'
import withReactContent from 'sweetalert2-react-content'
import {useEffect, useState} from "react";
import { Pagination } from '@material-ui/lab';
import { makeStyles } from '@material-ui/core/styles';

import PropTypes from 'prop-types';

import ModalCountryBox from '../modal/modalCountry'
import FormLogin from '../login/login'
import { CSVLink } from "react-csv";
import { useCookies } from 'react-cookie';
import Cookies from 'universal-cookie';



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

const useStylesTable = makeStyles({
    root: {
        // background: (props) =>
        //     props.color === 'red'
        //         ? 'linear-gradient(45deg, #FE6B8B 30%, #FF8E53 90%)'
        //         : 'linear-gradient(45deg, #2196F3 30%, #21CBF3 90%)',
        border: 0,
        borderRadius: 3,
        boxShadow: (props) =>
            props.color === 'red'
                ? '0 10px 10px 15px rgba(255, 105, 135, .3)'
                : '0 10px 10px 15px rgba(33, 203, 243, .3)',
        //color: 'white',
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

function MyButtonCSV(props) {
    const { color, ...other } = props;
    const classes = useStylesBt(props);
    return <CSVLink className={classes.root} {...other} />;
}

function MyButtonIcon(props) {
    const { color, ...other } = props;
    const classes = useStylesBt(props);
    return <IconButton className={classes.root} {...other} />;
}

function MyTable(props) {
    const { color, ...other } = props;
    const classes = useStylesTable(props);
    return <DataGrid className={classes.root} {...other} />;
}

MyButton.propTypes = {
    color: PropTypes.oneOf(['blue', 'red']).isRequired,
};


const DataGridCountry= (props) => {

    const [cookies, setCookie, removeCookie] = useCookies(["user"]);

    const useStyles = makeStyles({
        root: {
            display: 'flex',
        },
    });

    function CustomPagination(props) {
        const { pagination, api } = props;
        const classes = useStyles();

        return (
            <Pagination
                className={classes.root}
                color="primary"
                page={pagination.page}
                count={pagination.pageCount}
                onChange={(event, value) => api.current.setPage(value)}
            />
        );
    }

    CustomPagination.propTypes = {
        /**
         * ApiRef that let you manipulate the grid.
         */
        api: PropTypes.shape({
            current: PropTypes.object.isRequired,
        }).isRequired,
        /**
         * The object containing all pagination details in [[PaginationState]].
         */
        pagination: PropTypes.shape({
            page: PropTypes.number.isRequired,
            pageCount: PropTypes.number.isRequired,
            pageSize: PropTypes.number.isRequired,
            paginationMode: PropTypes.oneOf(['client', 'server']).isRequired,
            rowCount: PropTypes.number.isRequired,
        }).isRequired,
    };

    const [modalIsOpenEdit, setIsOpenEdit] = useState(false);
    const [modalIsOpenAdd, setIsOpenAdd] = useState(false);
    const [dataFrom, setDataSelection] = useState([]);
    const [countryName, setCountryName] = useState("");
    const [countryCapital, setCountryCapital] = useState("");
    const [countryRegion, setCountryRegion] = useState("");
    const [countryPopulation, setCountryPopulation] = useState();
    const [selectionCountry, setSelection] = React.useState(undefined);
    const [selectionCountryName, setSelectionName] = React.useState("");

    const [filteredCountries, setFilteredCountries] = useState([]);
    const [searchName, setSearchCountryName] = useState("");
    const [searchCapital, setSearchCountryCapital] = useState("");
    const [searchRegion, setSearchCountryRegion] = useState("");
    const [searchPopulation, setSearchCountryPopulation] = useState("");

    let [page, setPage] = React.useState(1);
    const [validate, setValidate] = React.useState(false)

    const loadDataCountries = async () => {

        fetch('http://test.new.country.com/api/countries', {

            headers: {
                'accessToken': cookies.user,
                'Authorization': "Bearer",
                'Content-Type': 'application/json',
                "expiresIn": 86400
            },
        })
                .then(function (response) {
                    if(response.ok) {
                        return response.json();
                    }
                    console.log(dataCountries);
                    throw new Error('Network response was not ok.');
                }).then(function(data) {
                setDataCountries(data.data.countries);
            console.log("test");
                console.log(dataCountries);
            }).catch(function(error) {
                console.log('There has been a problem with your fetch operation: ',
                    error.message);
            });
    }



    const [dataCountries, setDataCountries] = useState([]);

    const params = (new URL(window.location)).searchParams;

    useEffect( () => {
        if (params.get('token')) {
            setCookie("user", params.get('token'), { path: '/' });
            console.log(cookies.user); // Pacman
        }

        loadDataCountries();
    }, []);

    useEffect(() => {
        if (!dataCountries) return;
        setFilteredCountries(
            dataCountries.filter((value) =>
                value.name.toLowerCase().includes(searchName.toLowerCase())
                        )
        );
        // setFilteredCountries(
        //     dataCountries.filter((value) =>
        //         value.capital.toLowerCase().includes(searchCapital.toLowerCase())
        //     )
        // );
        // setFilteredCountries(
        //     dataCountries.filter((value) =>
        //         value.region.toLowerCase().includes(searchRegion.toLowerCase())
        //     )
        // );
        // setFilteredCountries(
        //     dataCountries.filter((value) =>
        //         value.population.toLowerCase().includes(searchPopulation.toLowerCase())
        //     )
        // );

    }, [searchName, dataCountries]);

    const MySwal = withReactContent(Swal)

    function submitEdit(value) {
        if (value === undefined || value === null) {
            return
        }
        setCountryName(value.name)
        setCountryCapital(value.capital)
        setCountryRegion(value.region)
        setCountryPopulation(value.population)
        setDataSelection(value);
        setIsOpenEdit(true);
    }


    function submitDelete(value) {
        MySwal.fire({
            title: 'Are you sure?',
            text: 'Are you sure to delete ' + value.name + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteCountry(value.id).then(r =>
                    Swal.fire(
                        'Deleted!',
                        'Your ' + value.name + 'has been deleted.',
                        'success'
                    ))
            }
        })
    }

    function submitDeleteSelection() {
        if (selectionCountry === undefined || selectionCountry === null) {
            return Swal.fire({
                title: 'Not selected',
                text: 'Please select the country you want to delete.',
                icon: 'warning',
                showCancelButton: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
            })

        }

        let c = 0;
        let stringName = "";

        dataCountries.forEach((value, index, self) => {
            if (selectionCountry[c] == value.id) {
                stringName += value.name + ", "
                c++;
                if (c == selectionCountry.length) {
                    return ;
                }
            }
        });
        console.log(stringName)

        stringName = stringName.substr(0,stringName.length-2);
        console.log(stringName)

        setSelectionName(stringName)

        Swal.fire({
            title: 'Are you sure?',
            text: 'Are you sure you want to delete ' + stringName + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteAllSelection(stringName)
            }
        })
    }

    const deleteCountry = async (id) => {
        fetch('http://test.new.country.com/api/countries/' + id , {
            method: 'DELETE',
        }).then(function (response) {
            if(response.ok) {
                return response.json();
            }
            throw new Error('Network response was not ok.');
        }).then(function(data) {
            setDataCountries(data.data.countries);
        }).catch(function(error) {
            console.log('There has been a problem with your fetch operation: ',
                error.message);
        });

    }

    const deleteAllSelection = async (stringName) => {

        console.log(selectionCountryName)
        if (selectionCountry === undefined || selectionCountry === null) {
            return
        }
        fetch('http://test.new.country.com/api/destroySelection', {
            method: 'DELETE',
            body: JSON.stringify({
                selectionCountry: selectionCountry
            }),
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8; application/json',
            }
        }).then(function (response) {
            if(response.ok) {
                Swal.fire(
                    'Deleted!',
                    stringName + ' deleted.',
                    'success'
                )
                //setDataCountries(data.data.countries)
                setSelection();
                return response.json();
            } else {
                console.log("status error")
                Swal.fire(
                    'ERROR!',
                    stringName + ' not found.',
                    'error'
                )
            }
            throw new Error('Network response was not ok.');
        }).then(function(data) {
            setDataCountries(data.data.countries);
        }).catch(function(error) {
            console.log('There has been a problem with your fetch operation: ',
                error.message);
        });
    }

    const createCountry = async () => {
        setValidate(true);

        if (dataFrom === undefined || dataFrom === null) {
            return
        }

        let dataGet = document.getElementById('form-save-data');
        let formData = new FormData(dataGet);
        formData.append("token", cookies.user);

        fetch ('http://test.new.country.com/api/countries', {
            method: 'POST',
            body: formData,
        }).then(function (response) {
            response.json().then(
                function (data) {
                    console.log(data.message);
                    if(data.status === true) {
                        setIsOpenAdd(false);
                        Swal.fire(
                            'Successful create!',
                            formData.get('name') + ' has been created.',
                            'success'
                        )
                        setDataCountries(data.data.countries);
                    } else {
                        Swal.fire(
                            'ERROR!',
                            data.message,
                            'error'
                        )
                    }
                }
            )

        }).then(function (test) {
            throw new Error('Network response was not ok.');
        }).then(function(data) {
            console.log(data);
        }).catch(function(error) {
            console.log('There has been a problem with your fetch operation: ',
                error.message);
        });

    }

    const editCountry = async () => {
        setValidate(true);
        if (dataFrom === undefined || dataFrom === null) {
            return
        }

        let dataGet = document.getElementById('form-save-data');
        let formData = new FormData(dataGet);
        formData.append("_method","PATCH")

        let dataCountry = {
            'name':formData.get('name'),
            'capital':formData.get('capital'),
            'region':formData.get('region'),
            'population':formData.get('population')
        }

        console.log(dataCountry)

        const response = await fetch ('http://test.new.country.com/api/countries/' + dataFrom.id, {
            method: 'PATCH',
            body: JSON.stringify({
                name:formData.get('name'),
                capital:formData.get('capital'),
                region:formData.get('region'),
                population:formData.get('population')
            }),
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8; application/json-patch+json',
            }
        }).then(function (response) {
            response.json().then(
                function (data) {
                    console.log(data.message);
                    if(data.status === true) {
                        setIsOpenAdd(false);
                        Swal.fire(
                            'Successful!',
                            data.message,
                            'success'
                        )
                        setIsOpenEdit(false);
                        setDataCountries(data.data.countries);
                    } else {
                        Swal.fire(
                            'ERROR!',
                            data.message,
                            'error'
                        )
                    }
                }
            )

        }).then(function (test) {
            throw new Error('Network response was not ok.');
        }).then(function(data) {
            setDataCountries(data.data.countries);
        }).catch(function(error) {
            console.log('There has been a problem with your fetch operation: ',
                error.message);
        });
    }


    const columns = [
        { field: 'id', headerName: '#', width: 70 },

        {
            field: 'flag',
            headerName: 'Flag', headerAlign: 'left',
            sortable: false,
            renderCell: (params) => (
                <img
                    style={{
                        width: 80,
                        height: 50,
                        objectFit: 'fill'
                    }}

                    src={params.row.flag}
                    alt={'flag'}/>
            ),

        },

        { field: 'name', headerName: 'Name', width: 170 },
        { field: 'capital', headerName: 'Capital', width: 100 },
        { field: 'region', headerName: 'Region', width: 100 },
        {
            field: 'population',
            headerName: 'Population',
            type: 'number',
            width: 100,
        },
        {
            field: 'edit',
            headerName: 'Edit',
            headerAlign: 'center',
            sortable: false,
            width: 90,

            renderCell: (params) => (

                <MyButtonIcon
                    color="blue"
                    align={'center'}
                    style={{ width: '50%', margin:'auto'}}
                    startIcon={<EditIcon/>}
                    onClick={() => submitEdit(params.row)}
                ><EditIcon fontSize="large" />
                </MyButtonIcon>
            ),
        },{
            field: 'delete',
            headerName: 'Delete',
            headerAlign: 'center',
            sortable: false,
            width: 90,

            renderCell: (params) => (

                <MyButtonIcon
                    color="red"
                    aria-label="delete"
                    align={'center'}
                    style={{ width: '50%', margin:'auto'}}
                    startIcon={<DeleteIcon/>}
                    onClick={() => submitDelete(params.row)}
                ><DeleteIcon fontSize="large" />
                </MyButtonIcon>
            ),
        },
    ];

    function exportTasks(_this) {
        let _url = (_this).data('href');
        window.location.href = _url;
    }

    const exportCountry = async () => {
        fetch('http://test.new.country.com/api/export')
            .then(function (response) {
                if(response.ok) {
                    return response.json();
                }
                throw new Error('Network response was not ok.');
            }).then(function(data) {
            setDataCountries(data.data.countries);
        }).catch(function(error) {
            console.log('There has been a problem with your fetch operation: ',
                error.message);
        });
    }




    return (
        <div>
            <br/><br/>
            <FormLogin/>

            <MyButtonCSV
                style={{ height:'100px' }}
                data={dataCountries
                }>Download CSV</MyButtonCSV><br/><br/>

            <MyButton
                color="red"
                align={'center'}
                style={{ width: '50%', margin:'auto'}}
                onClick={() => setIsOpenAdd(true)}
            >ADD COUNTRY
            </MyButton><br/><br/>
            <MyButton
                color="blue"
                variant="contained" color="secondary"
                align={'center'}
                style={{ width: '50%', margin:'auto'}}
                onClick={() => submitDeleteSelection()}
            >DELETE SELECTION COUNTRY
            </MyButton><br/><br/>
            <label>
                Search : { ' ' }
            </label>
            <input
                style={{ width: '80%'}}
                type="text"
                className="swal2-input"
                placeholder="Search Countries"
                onChange={(e) => setSearchCountryName(e.target.value)}
            /><br/><br/>
            {/*<input*/}
            {/*    style={{ width: '80%'}}*/}
            {/*    type="text"*/}
            {/*    className="swal2-input"*/}
            {/*    placeholder="Search Countries"*/}
            {/*    onChange={(e) => setSearchCountryCapital(e.target.value)}*/}
            {/*/><br/><br/>*/}
            {/*<input*/}
            {/*    style={{ width: '80%'}}*/}
            {/*    type="text"*/}
            {/*    className="swal2-input"*/}
            {/*    placeholder="Search Countries"*/}
            {/*    onChange={(e) => setSearchCountryRegion(e.target.value)}*/}
            {/*/><br/><br/>*/}
            {/*<input*/}
            {/*    style={{ width: '80%'}}*/}
            {/*    type="text"*/}
            {/*    className="swal2-input"*/}
            {/*    placeholder="Search Countries"*/}
            {/*    onChange={(e) => setSearchCountryPopulation(e.target.value)}*/}
            {/*/><br/><br/>*/}

        <div style={{ height: 400, width: '100%' }}>
            <MyTable

                showColumnRightBorder={false}
                showCellRightBorder={false}
                rows={filteredCountries}
                loading={filteredCountries.length === 0}
                columns={columns}
                pageSize={20}
                checkboxSelection
                autoHeight={true}
                rowHeight={80}
                components={{
                    pagination: CustomPagination,
                }}
                onSelectionChange={(newSelection) => {
                    setSelection(newSelection.rowIds);
                }}
                page={page}
                onPageChange={(params) => {
                    if (params.pageCount < 1) {
                        setPage(1);
                    } if (params.pageCount >= 2) {
                        setPage(2);
                    } if (params.pageCount >= 10) {
                        setPage(1);
                    }

                }}/>


            <ModalCountryBox
                nameFrom = {'Add Country From'}
                setModalIsOpenAdd = {modalIsOpenAdd}
                onChangeName = {() =>
                    (name, e) => {
                        setCountryName({name});
                    }}
                onChangeCapital = {() =>
                    (capital, e) => {
                        setCountryCapital({capital});
                    }}
                onChangeRegion = {() =>
                    (region, e) => {
                        setCountryRegion({region});
                    }}
                onChangePopulation = {(population, e) => {
                    setCountryPopulation({population});
                }}
                onClickClose = {() => setIsOpenAdd(false)}
                onClickOk = {() => createCountry()}
                styleModal = {useStylesTable()}
                validate = {validate}
                setValidate = {setValidate}

            />

            <ModalCountryBox
                nameFrom = 'Edit Country From'
                setModalIsOpenAdd = {modalIsOpenEdit}
                onChangeName = {
                    (value) => {
                        setCountryName(value);
                    }}
                onChangeCapital = {
                    (value) => {
                        setCountryCapital(value);
                    }}
                onChangeRegion = {
                    (value) => {
                        setCountryRegion(value);
                    }}
                onChangePopulation = {
                    (value) => {
                        setCountryPopulation(value);
                    }}
                onClickClose = {() => setIsOpenEdit(false)}
                onClickOk= {() => editCountry()}
                valueName = {countryName}
                valueCapital = {countryCapital}
                valueRegion = {countryRegion}
                valuePopulation = {countryPopulation}
                validate = {validate}
                setValidate = {setValidate}
            /><br/><br/>


        </div><br/><br/></div>


    );
}

export default DataGridCountry