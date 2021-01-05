<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        Cookie::queue(Cookie::forget('token'));
        Cookie::queue(Cookie::forget('id'));

        return redirect('http://test.new.country.com');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function redirectToProvider()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleProviderCallback()
    {
        try {

            $user = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login');
        }

        $existingUser = User::all()->where('email', $user->email)->first();

        if (!$existingUser){

            $existingUser                  = new User;
            $existingUser->name            = $user->name;
            $existingUser->email           = $user->email;
            $existingUser->google_id       = $user->id;
            $existingUser->avatar          = $user->avatar;
            $existingUser->avatar_original = $user->avatar_original;

            $existingUser->save();
        }

        //$credentials = array($user->email,$user->id);
        $token = auth('api')->login($existingUser);
        if (!$token) {

            $status = false;
            $errors = [
                "login" => "Invalid username or password",
            ];
            $message = "Login Failed";
            return response()->json($status,400);
        } else {
            $this->setCookie($token, $existingUser->id);
            $errors = "";
            $status = true;
            $message = "Login Successfull";
            $data = [

                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ];
        }

        return $this->sendResult($token,$existingUser,$message,$data,$errors,$status);
    }

    public function setCookie($token, $id)
    {
        Cookie::queue('token', $token, 60);
        Cookie::queue('id', $id, 60);
        return response('Set cookie');
    }

    protected function sendResult($token,User $user,$message,$data = [],$errors = [],$status = true)

    {

        $errorCode = $status ? 200 : 422;

        $result = [
            'id_user' => $user->id,

            'name_user' => $user->name,

            "message" => $message,

            "status" => $status,

            "data" => $data,

            "errors" => $errors

        ];

        response()->json($result,$errorCode);

        Auth::loginUsingId($data);
//
//        return response()->json($result,$errorCode);

        return redirect()->away('http://test.new.country.com?token='.$token.'&username='.$user->name)
            ->with($result);

    }

    private function getToken($email, $password)
    {
        $token = null;
        //$credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt(['email'=>$email, 'password'=>$password])) {
                return response()->json([
                    'response' => 'error',
                    'message' => 'Password or email is invalid',
                    'token'=> $token
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'response' => 'error',
                'message' => 'Token creation failed',
            ]);
        }

        return $token;
    }
}
