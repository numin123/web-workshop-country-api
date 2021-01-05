<?php

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use function PHPUnit\Framework\isJson;
use EmojiFlag\EmojiFlag;

class cronjob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->apiCountryToTable();
        return 0;
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

            if ($country->capital == "") {
                $countryTable->capital = '-';
            } else {
                $countryTable->capital = $country->capital;
            }

            if ($country->region == "") {
                $countryTable->region = '-';
            } else {
                $countryTable->region = $country->region;
            }

            $countryTable->population = $country->population;
            $countryTable->flag = $country->flag;

            $countryTable->save();
        }


        if ($n <= 1) {
            $newCountryString = "The country is complete!\r\nNo country was restored.";
        }

        //$this->notificationSecChat($newCountryString);

        Cache::forget('countriesCache');

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
