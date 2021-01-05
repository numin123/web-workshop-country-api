<?php

namespace App\Jobs;

use App\Models\Country;
use EmojiFlag\EmojiFlag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class QueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $country = Country::all()->first();
        if (!$country) {
            $this->apiCountryToTable();
        } else {
            $notificationString = "QUEUEJOB working...\r\n".$country->name.' has been deleted.';
            $country->delete();
            Cache::forget('countriesCache');
            $this->notificationSecChat($notificationString);
        }

    }

    public function getApiCountryFromUrl()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://restcountries.eu/rest/v2/all",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "postman-token: 8aac7337-b628-edfa-9ad0-eafce4f9ad6e"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return json_decode($response);

    }

    public function apiCountryToTable()
    {

        $apiCountries = $this->getApiCountryFromUrl();
        $newCountryString = "Recovered countries\r\n";
        $n = 1;
        foreach ($apiCountries as $country) {
            $id = $country->flag;

            $countryTable = Country::onlyTrashed()->where("flag", $id)->first();

            if ($countryTable != null) {
                $countryTable->restore();
                $newCountryString = $newCountryString.$n.'. '
                    .EmojiFlag::emojiFlag(strtolower($country->alpha2Code)).' '.$countryTable->name.' '."\r\n";
                $n++;
            } else {
                $countryTable = Country::all()->where("flag", $id)->first();
            }

            if (!$countryTable) {
                $countryTable = new Country();
            }
            $countryTable->name = $country->name;
            $countryTable->capital = $country->capital;
            $countryTable->region = $country->region;
            $countryTable->population = $country->population;
            $countryTable->flag = $country->flag;

            $countryTable->save();
        }


        if ($n <= 1) {
            $newCountryString = "The country is complete!\r\nNo country was restored.";
        }

        $this->notificationSecChat($newCountryString);

        Cache::forget('countriesCache');

    }

    public function notificationSecChat($countryText)
    {
        $text = array("content" => $countryText,
            "mentioned_list" => ['1706829708'],
            "mentioned_email_list" => ['wenxuanmo@seagroup.com'],
            "at_all" => false);

        $data = json_encode(array("tag" => "text",
            "text" => $text));


        $this->callAPI("POST",
            "https://openapi.seatalk.io/webhook/group/1cY0CGNCQyKqgmbVVO1xgQ",
            $data);


    }

    function callAPI($method, $url, $data)
    {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'APIKEY: 111111111111111111111',
            'Content-Type: application/json',
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        if (!$result) {
            die("Connection Failure");
        }
        curl_close($curl);
        return $result;
    }
}
