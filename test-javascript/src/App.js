import React, { useState } from 'react';
import axios from 'axios';
import './App.css';

function App() {

  const apiURL = "http://search-api.fie.future.net.uk/widget.php?id=review&site=TRD&model_name=iPad_Air";

  const [model_infos, setModel_infos] = useState(null);

  const fetchData = async () => {
    const response = await axios.get(apiURL)

      setModel_infos(response.data)
  }

  return (
      <div className="App">
        <h1>Game of Thrones Books</h1>
        <h2>Fetch a list from an API and display it</h2>

        {/_ Fetch data from API _/}

        <div>
            <button className="fetch-button" onClick={fetchData}>
                Fetch Data
            </button>
        </div>

        {/_ Display data from API _/}
        <div className="model_infos">
          {model_infos &&
          model_infos.map((model_info) => {

            return (
                <div className="details">
                    <p>Seller logo: {model_info.logo_url}</p>
                    <p>Seller name: {model_info.brand}</p>
                    <p>Name of product: {model_info.name}</p>
                    <p>Prices: {model_info.prices.AUD} AUD - {model_info.prices.GBP} GBP</p>
                    <p>Affiliate link: {model_info.review_link}</p>
                </div>
            );
          })}
        </div>
      </div>
  );
}
export default App;
