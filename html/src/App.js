import logo from './logo.svg';
import './App.css';

import React, {useState, useEffect} from "react";
import DataGridCountry from './component/table/countriesTable';


function App() {


    const styleTableOne = {
        align:'center', margin:'auto',
        width: '90%'
    }

  return (
    <div className="App">
      {/*<header className="App-header">*/}
      {/*  */}
      {/*</header>*/}
      <div style={styleTableOne}>
        <DataGridCountry/>
      </div>
        <br/><br/>
    </div>
  );
}

export default App;
