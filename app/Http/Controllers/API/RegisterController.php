<?php
   
namespace App\Http\Controllers\API;
   
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController as BaseController;
   
class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;
   
        return $this->sendResponse($success, 'User register successfully.');
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request): JsonResponse
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')->plainTextToken; 
            $success['name'] =  $user->name;
   
            return $this->sendResponse($success, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }

    public function result(Request $request)
    {
        echo('hello');die;
        // Invalidate the user's session
        Auth::logout();

        // Invalidate the session token if you are using sessions
        $request->session()->invalidate();

        // Regenerate the CSRF token to avoid session fixation
        $request->session()->regenerateToken();

        // Redirect to the login page or wherever you want
        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        // $request->user()->currentAccessToken()->delete();
        // Revoke all tokens for the user
        $request->user()->tokens()->delete();

        // Return a successful response
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
