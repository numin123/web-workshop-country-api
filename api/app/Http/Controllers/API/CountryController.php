<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\QueueJob;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function __construct()
    {
        //$this->middleware('');
    }

    public function index(Request $request)
    {

        $countryJob = (new QueueJob())->delay(Carbon::now()->addSeconds(2));
        dispatch($countryJob);

//        $user = User::all()->where('id', Cookie::get('id'))->first();
//        dd($user->countries());
        if ($this->tokenLogin($request)) {
            $data = ['countries' => $this->getDataCountryForUser()];
            $response = ['data' => $data,
                'status' => true,
                're' => "OK u r login".Cookie::get('id')];
            return response()->json($response, 200);
        } else {
            $data = ['countries' => []];
            $response = ['data' => $data,
                'status' => true,
                'message' => "login pls"];
            return response()->json($response, 400);
        }


    }

    public function tokenLogin($request) {
        $token = $request->header('accessToken');
        $tokenUserAuth = Cookie::get('token');
        if ($tokenUserAuth == $token) {
            return true;
        }
        return false;
    }

    public function getDataCountryForUser()
    {
        return Cache::remember('countriesCache', 60*60, function () {

            $user = User::all()->where('id', Cookie::get('id'))->first();

            if ($user->status == "ADMIN") {
                return $this->getDataCountry();
            }

            return $user->countries();
        });
    }

    public function getDataCountry()
    {
        return Cache::remember('countriesCache', 60*60, function () {
            $data = Country::all();
            foreach ($data as $country) {
                $userName = User::where('id', $country->id)->first();
                $name = $userName->email;
                $country['user'] = $name;
            }

            return $data;
        });
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try{

//            dd($request->name);

//            if (!$this->tokenLogin($request)) {
//                $data = ['countries' => $this->getDataCountry()];
//                $response = ['data' => $data,
//                    'status' => true,
//                    'message' => ""];
//                return response()->json($response, 200);
//            }


            $stringRequest = $request->name
                .$request->capital
                .$request->region
                .$request->population;

            $reEmo = $this->removeEmoji($stringRequest);


            if (strlen($reEmo) != strlen($stringRequest)) {

                $response = ['data' => null,
                    'status' => false,
                    'message' => 'Emojis are not allowed.'];

                return response()->json($response, 400);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|max:100|string',
                'capital' => 'max:100',
                'region' => 'max:100',
                'population' => 'required|integer'
            ],[
                'name.required'   => 'Please enter a valid name.',
                'population.required'   => 'Please specify the population correctly.'
            ]);

            if ($validator->fails()) {

                $response = ['data' => null,
                    'status' => false,
                    'message' => $validator->messages()->first()];

                return response()->json($response, 400);
            }

            if (blank($request->name)) {

                $response = ['data' => null,
                    'status' => false,
                    'message' => "ERROR EMOJI"];

                return response()->json($response, 400);
            }

            $country = Country::onlyTrashed()->where("name", $request->name)->first();



            if ($country != null) {
                $country->restore();
                $country->name = $request->name;
                $country->capital = $request->capital;
                $country->region = $request->region;
                $country->population = $request->population;


                $country->save();
                Cache::forget('countriesCache');
                $name = $country->name;

                $data = ['countries' => $this->getDataCountry()];
                $response = ['data' => $data,
                    'status' => true,
                    'message' => "Restore $name"];

                return response()->json($response, 200);

            }

            $country = new Country();
            $country->name = $request->name;
            $country->capital = $request->capital;
            $country->region = $request->region;

            if ($request->capital == null) {
                $country->capital = "-";
            }
            if ($request->region == null) {
                $country->region = "-";
            }

            $country->population = $request->population;
            $country->user_id = Cookie::get('id');


            if (!$request->flag) {
                $country->flag = 'https://climate.onep.go.th/wp-content/uploads/2020/01/default-image-300x169.png';
            }


            $country->save();

            Cache::forget('countriesCache');

            $data = ['countries' => $this->getDataCountryForUser()];
            $response = ['data' => $data,
                'status' => true,
                'message' => $validator->messages()->first()];

            return response()->json($response, 200);

        }catch (\Exception $x){

            return response()->json([
                'status' => false,
                'message' => $x->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function show(Country $country)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Country  $country
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {

        try {

            $stringRequest = $request->name
                .$request->capital
                .$request->region
                .$request->population;

            $reEmo = $this->removeEmoji($stringRequest);


            if (strlen($reEmo) != strlen($stringRequest)) {

                $response = ['data' => null,
                    'status' => false,
                    'message' => 'Emojis are not allowed.'];

                return response()->json($response, 400);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|max:100|string',
                'capital' => 'max:100',
                'region' => 'max:100',
                'population' => 'required|integer'
            ],[
                'name.required'   => 'Please enter a valid name.',
                'population.required'   => 'Please specify the population correctly.'
            ]);

            if ($validator->fails()) {

                $response = ['data' => null,
                    'status' => false,
                    'message' => $validator->messages()->first()];

                return response()->json($response, 400);
            }

            if (blank($this->removeEmoji($request->name))) {

                $response = ['data' => null,
                    'status' => false,
                    'message' => "ERROR EMOJI"];

                return response()->json($response, 400);
            }


            if (blank($id)) {

                $response = ['data' => null,
                    'status' => false,
                    'message' => "ERROR ID"];

                return response()->json($response, 400);

            }

            $country = Country::all()->where("id", $id)->first();

            if (!$country) {

                $response = ['data' => null,
                    'status' => false,
                    'message' => "ERROR ID"];

                return response()->json($response, 400);
            }

            $oldName = $country->name;
            $oldCapital = $country->capital;
            $oldRegion = $country->region;
            $oldPopulation = $country->population;

            $country->name = $request->name;
            $country->capital = $request->capital;
            $country->region = $request->region;
            $country->population = $request->population;

            if ($oldName === $request->name) {
                $nameLine = "Name has not changed.";
            } else {
                $nameLine = $oldName." changed to ".$request->name;
            }

            if ($oldCapital === $request->capital) {
                $capitalLine = "Capital has not changed.";
            } else {
                $capitalLine = $oldCapital." changed to ".$request->capital;
            }

            if ($oldRegion === $request->region) {
                $regionLine = "Region has not changed.";
            } else {
                $regionLine = $oldRegion." changed to ".$request->region;
            }

            if ($oldPopulation == $request->population) {
                $populationLine = "Population has not changed.";
            } else {
                $populationLine = $oldPopulation." changed to ".$request->population;
            }

            if ($request->capital == null) {
                $country->capital = "-";
            }
            if ($request->region == null) {
                $country->region = "-";
            }


            $country->save();
            Cache::forget('countriesCache');

            $data = ['countries' => $this->getDataCountryForUser()];
            $response = ['data' => $data,
                'status' => true,
                'message' => $nameLine."<br/>"
                            .$capitalLine."<br/>"
                    .$regionLine."<br/>"
                    .$populationLine."<br/>"];

            return response()->json($response, 200);

        } catch (\Exception $x) {
            return response()->json([
                'status' => false,
                'message' => $x->getMessage(),
            ]);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Country  $country
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {

        try{

            $country = Country::all()->where('id', $id)->first();
            if (!$country) {
                return response()->json([
                    'status' => false,
                    'message' => "Not found Country",
                ]);
            }
            $name = $country->name;
            $country->delete();
            Cache::forget('countriesCache');

        }catch (\Exception $x){
            return response()->json([
                'status' => false,
                'message' => $x->getMessage(),
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => "Deleted $name",
            'data' => [
                'countries' => $this->getDataCountryForUser(),
            ]
        ]);

    }

    public function destroySelection(Request $request)
    {
        $selectCountry = $request->selectionCountry ;

        try{

            foreach ($selectCountry as $id) {

                $country = Country::all()->where("id",$id)->first();
                if (!$country) {
                    return response()->json([
                        'status' => false,
                        'message' => "Not found Country",
                    ]);
                }
                $name = $country->name;
                $country->delete();

            }


            Cache::forget('countriesCache');

        }catch (\Exception $x){
            return response()->json([
                'status' => false,
                'message' => $x->getMessage(),
            ]);
        }

        $data = ['countries' => $this->getDataCountryForUser()];
        $response = ['data' => $data,
            'status' => true,
            'message' => "OK ALL DELETE"];

        return response()->json($response, 200);

    }

    public static function removeEmoji($text) {

        $clean_text = "";

        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);

        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);

        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);

        return $clean_text;
    }


}
