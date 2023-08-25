<?php

namespace App\Http\Controllers\Api;

use App\Models\Page;
use App\Models\User;
use App\Models\Slider;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use App\Models\Generalsetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use DB;

class ApiController extends Controller
{
    /******************************************************************************
     * Create User
     * @param Request $request
     * @return User 
     *****************************************************************************/
    public function createUser(Request $request)
    {
        try {
            if($request->login_type == 'apple' || $request->login_type == 'facebook' || $request->login_type == 'google')
            {
                $data = $request->only('login_type','id', 'email');
                $validateUser = Validator::make($data, [
                    'id' => 'required',
                    'login_type' => 'required'
                ]);
            }
            else{
                //Validated
                $data = $request->only('firstname', 'lastname', 'email', 'mobile', 'password', 'login_type');
                $validateUser = Validator::make($request->all(), 
                [
                    'firstname' => 'required|regex:/^[\pL\s\-]+$/u|min:3|max:30',
                    'lastname' => 'required|regex:/^[\pL\s\-]+$/u|min:3|max:30',
                    'email' => 'required|unique:users|max:70',
                    'mobile' => 'required|unique:users|min:10|max:12',
                    'password' => 'required',
                    'login_type' => 'required'
                ]);
            }

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'authenticate' => true,
                    'message' => $validateUser->messages()->first()
                ], 200);
            }
            if ($data['login_type']=="app"){
                $user = User::create([
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'mobile' => $request->mobile,
                    'email' => $request->email,
                    'avatar' => "images/avatars/default.png",
                    'role' => 3,
                    'password' => Hash::make($request->password),
                    'bc_id' => md5($request->password)
                ]);
    
                return response()->json([
                    'status' => true,
                    'message' => 'User created successfully',
                    'token' => $user->createToken("API TOKEN")->plainTextToken
                ], 200);
            }
            elseif ($data['login_type']=="google"){
                $validator = Validator::make($data, [
                    'id' => 'required',
                ]);
                if ($validator->fails()) {
                    return response()->json(['status'=>false,'message' => $validator->messages()->first()], 200);
                }
    
                $previousData = DB::table('users_deleted')->orderBy('id','desc')->where('google_id', $request->id)->first();
                if(!empty($previousData))
                {
                    if($previousData->firstname != '' && $previousData->email != '')
                    {
                        $insertData['firstname'] = $previousData->firstname;
                        $insertData['lastname'] = $previousData->lastname;
                        $insertData['email'] = $previousData->email;
                    }
                }
                else
                {
                    $insertData['firstname']=$request->firstname;
                    $insertData['lastname']=$request->lastname;
                    $insertData['email']=$request->email;
                }

                $insertData['google_id'] = $request->id;
                $insertData['avatar'] = "images/avatars/default.png";
                $insertData['password'] = Hash::make('immersive1');
                $insertData['bc_id'] = md5('immersive1');
                $insertData['role']=3;
                $error_msg = 'Registration are not created successfully';
                $exUser = User::where(['google_id' => $request->id])->withTrashed()->first();
                if(!empty($exUser))
                {
                    $exUser->update([
                        'deleted_at' => ''
                    ]);

                    $user = $exUser;
                    if($user->status == 1){
                        $message = 'User logged in successfully';
                        $error_msg = 'User logged in successfully';
                        $this->setLog($user);
                        return response()->json([
                            'status' => true,
                            'message' => 'User logged in successfully',
                            'token' => $user->createToken("API TOKEN")->plainTextToken
                        ], 200);
                    }
                    else{
                        return response()->json([            
                                'status' => false,            
                                'authenticate' => false,
                                'message' => "Account is inactive",            
                            ], 500);
                    }
                }
                else
                {
                    $exUser_ = User::where('email', $insertData['email'])->withTrashed()->count();
                    if($exUser_ > 0)
                    {
                        return response()->json([            
                                'status' => false,
                                'authenticate' => true,            
                                'message' => "The email has already been taken",            
                            ], 500);
                    }
                    else
                    {
                        $insertData['status']=1;
                        $user = User::create($insertData);
                        $this->setLog($user);
                        return response()->json([
                            'status' => true,
                            'message' => 'User created successfully',
                            'token' => $user->createToken("API TOKEN")->plainTextToken
                        ], 200);
                    }
                }
            }
            elseif ($data['login_type']=="apple"){
                $validator = Validator::make($data, [
                    'id' => 'required',
                ]);
                if ($validator->fails()) {
                    return response()->json(['status'=>false,'message' => $validator->messages()->first()], 200);
                }
    
                $previousData = DB::table('users_deleted')->orderBy('id','desc')->where('apple_id', $request->id)->first();
                if(!empty($previousData))
                {
                    if($previousData->firstname != '' && $previousData->email != '')
                    {
                        $insertData['firstname'] = $previousData->firstname;
                        $insertData['lastname'] = $previousData->lastname;
                        $insertData['email'] = $previousData->email;
                    }
                }
                else
                {
                    $insertData['firstname']=$request->firstname;
                    $insertData['lastname']=$request->lastname;
                    $insertData['email']=$request->email;
                }

                $insertData['apple_id'] = $request->id;
                $insertData['avatar'] = "images/avatars/default.png";
                $insertData['password'] = Hash::make('immersive2');
                $insertData['bc_id'] = md5('immersive2');
                $insertData['role']=3;
                $exUser = User::where(['apple_id' => $request->id])->withTrashed()->first();
                if(!empty($exUser))
                {
                    $exUser->update([
                        'deleted_at' => ''
                    ]);
                    $user = $exUser;
                    if($user->status == 1){
                        $this->setLog($user);
                        return response()->json([
                            'status' => true,
                            'message' => 'User logged in successfully',
                            'token' => $user->createToken("API TOKEN")->plainTextToken
                        ], 200);
                    }
                    else{
                        return response()->json([            
                                'status' => false,            
                                'authenticate' => false,
                                'message' => "Account is inactive",            
                            ], 500);
                    }
                }
                else
                {
                    $exUser_ = User::where('email', $insertData['email'])->withTrashed()->count();
                    if($exUser_ > 0)
                    {
                        return response()->json([            
                                'status' => false,    
                                'authenticate' => true,        
                                'message' => "The email has already been taken",            
                            ], 500);
                    }
                    else
                    {
                        $insertData['status']=1;
                        $user = User::create($insertData);
                        $this->setLog($user);
                        return response()->json([
                            'status' => true,
                            'message' => 'User created successfully',
                            'token' => $user->createToken("API TOKEN")->plainTextToken
                        ], 200);
                    }
                }
            }
            elseif ($data['login_type']=="facebook"){
                $validator = Validator::make($data, [
                    'id' => 'required',
                ]);
                if ($validator->fails()) {
                    return response()->json(['status'=>false,'message' => $validator->messages()->first()], 200);
                }
    
                $previousData = DB::table('users_deleted')->orderBy('id','desc')->where('fb_id', $request->id)->first();
                if(!empty($previousData))
                {
                    if($previousData->firstname != '' && $previousData->email != '')
                    {
                        $insertData['firstname'] = $previousData->firstname;
                        $insertData['lastname'] = $previousData->lastname;
                        $insertData['email'] = $previousData->email;
                    }
                }
                else
                {
                    $insertData['firstname']=$request->firstname;
                    $insertData['lastname']=$request->lastname;
                    $insertData['email']=$request->email;
                }

                $insertData['fb_id'] = $request->id;
                $insertData['avatar'] = "images/avatars/default.png";
                $insertData['password'] = Hash::make('immersive3');
                $insertData['bc_id'] = md5('immersive3');
                $insertData['role']=3;
                $exUser = User::where(['fb_id' => $request->id])->withTrashed()->first();
                if(!empty($exUser))
                {
                    $exUser->update([
                        'deleted_at' => ''
                    ]);
                    $user = $exUser;
                    if($user->status == 1){
                        $this->setLog($user);
                        return response()->json([
                            'status' => true,
                            'message' => 'User logged in successfully',
                            'token' => $user->createToken("API TOKEN")->plainTextToken
                        ], 200);
                    }
                    else{
                        return response()->json([            
                                'status' => false,            
                                'authenticate' => false,          
                                'message' => "Account is inactive",            
                            ], 500);
                    }
                }
                else
                {
                    $exUser_ = User::where('email', $insertData['email'])->withTrashed()->count();
                    if($exUser_ > 0)
                    {
                        return response()->json([            
                                'status' => false,
                                'authenticate' => true,            
                                'message' => "The email has already been taken",            
                            ], 500);
                    }
                    else
                    {
                        $insertData['status']=1;
                        $user = User::create($insertData);
                        $this->setLog($user);
                        return response()->json([
                            'status' => true,
                            'message' => 'User created successfully',
                            'token' => $user->createToken("API TOKEN")->plainTextToken
                        ], 200);
                    }
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /******************************************************************************
     * Login The User
     * @param Request $request
     * @return User
     *****************************************************************************/
    public function loginUser(Request $request)
    {
        try {
            if($request->login_type == 'apple' || $request->login_type == 'facebook' || $request->login_type == 'google')
            {
                $data = $request->only('login_type','id');
                $validateUser = Validator::make($data, [
                    'id' => 'required',
                    'login_type' => 'required'
                ]);
            }
            else{
                $validateUser = Validator::make($request->all(), 
                [
                    'email' => 'required|email',
                    'password' => 'required',
                    'login_type' => 'required'
                ]);
            }

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'authenticate' => true,
                    'message' => $validateUser->messages()->first()
                ], 200);
            }
            if($request->login_type == 'app'){
                if(!Auth::attempt($request->only(['email', 'password']))){
                    return response()->json([
                        'status' => false,
                        'authenticate' => true,
                        'message' => 'Email & password does not match with our record.',
                    ], 200);
                }
                $user = User::where('email', $request->email)->first();
                if($user->role == 2 || $user->role == 3){
                    if($user->status == 1){
                        $this->setLog($user);
                        return response()->json([
                            'status' => true,
                            'message' => 'User logged in successfully',
                            'token' => $user->createToken("API TOKEN")->plainTextToken
                        ], 200);
                    }
                    else{
                        return response()->json([            
                                'status' => false,            
                                'authenticate' => false,          
                                'message' => "Account is inactive",            
                            ], 200);
                    }
                }
                else{
                    return response()->json([
                        'status' => false,
                        'authenticate' => true,
                        'message' => 'Email & password does not match with our record.',
                    ], 200);
                }
            }
            else{
                if($request->login_type == 'google'){
                    $exUser = User::where(['google_id' => $request->id ])->withTrashed()->first();
                    if(!empty($exUser))
                    {
                        $exUser->update([
                            'deleted_at' => ''
                        ]);

                        $user = $exUser;
                        $message = 'User logged in successfully';
                        $error_msg = 'User logged in successfully';
                        if($user->status == 1){
                            $this->setLog($user);
                            return response()->json([
                                'status' => true,
                                'message' => 'User logged in successfully',
                                'token' => $user->createToken("API TOKEN")->plainTextToken
                            ], 200);
                        }
                        else{
                            return response()->json([            
                                    'status' => false,            
                                    'authenticate' => false,          
                                    'message' => "Account is inactive",            
                                ], 500);
                        }
                    }
                    else
                    {
                        $previousData = DB::table('users_deleted')->orderBy('id','desc')->where('google_id', $request->id)->first();
                        if(!empty($previousData))
                        {
                            if($previousData->firstname != '' && $previousData->email != '')
                            {
                                $insertData['firstname'] = $previousData->firstname;
                                $insertData['lastname'] = $previousData->lastname;
                                $insertData['email'] = $previousData->email;
                            }
                        }
                        else
                        {
                            $validateUser = Validator::make($request->all(), 
                            [
                                'email' => 'required|email|unique:users|max:70',
                                'id' => 'required',
                                'login_type' => 'required',
                                'firstname' => 'required'
                            ]);
                            if($validateUser->fails()){
                                return response()->json([
                                    'status' => false,
                                    'authenticate' => true,
                                    'message' => $validateUser->messages()->first()
                                ], 200);
                            }

                            $insertData['firstname']=$request->firstname;
                            $insertData['lastname']= @$request->lastname;
                            $insertData['email']=$request->email;
                        }

                        $insertData['google_id'] = $request->id;
                        $insertData['avatar'] = "images/avatars/default.png";
                        $insertData['password'] = Hash::make('immersive1');
                        $insertData['bc_id'] = md5('immersive1');
                        $insertData['role']=3;
                        $error_msg = 'Registration are not created successfully';
                        $exUser_ = User::where('email', $insertData['email'])->withTrashed()->count();
                        if($exUser_ > 0)
                        {
                            return response()->json([            
                                    'status' => false,    
                                    'authenticate' => true,            
                                    'message' => "The email has already been taken",            
                                ], 500);
                        }
                        else
                        {
                            $insertData['status']=1;
                            $user = User::create($insertData);
                        }
                    }
                }
                elseif($request->login_type == 'apple'){
                    $exUser = User::where(['apple_id' => $request->id ])->withTrashed()->first();
                    if(!empty($exUser))
                    {
                        $exUser->update([
                            'deleted_at' => ''
                        ]);

                        $user = $exUser;
                        $message = 'User logged in successfully';
                        $error_msg = 'User logged in successfully';
                        if($user->status == 1){
                            $this->setLog($user);
                            return response()->json([
                                'status' => true,
                                'message' => 'User logged in successfully',
                                'token' => $user->createToken("API TOKEN")->plainTextToken
                            ], 200);
                        }
                        else{
                            return response()->json([            
                                    'status' => false,            
                                    'authenticate' => false,          
                                    'message' => "Account is inactive",            
                                ], 500);
                        }
                    }
                    else{
                        $previousData = DB::table('users_deleted')->orderBy('id','desc')->where('apple_id', $request->id)->first();
                        if(!empty($previousData))
                        {
                            if($previousData->firstname != '' && $previousData->email != '')
                            {
                                $insertData['firstname'] = $previousData->firstname;
                                $insertData['lastname'] = $previousData->lastname;
                                $insertData['email'] = $previousData->email;
                            }
                        }
                        else
                        {
                            $validateUser = Validator::make($request->all(), 
                            [
                                'email' => 'required|email|unique:users|max:70',
                                'id' => 'required',
                                'login_type' => 'required',
                                'firstname' => 'required'
                            ]);
                            if($validateUser->fails()){
                                return response()->json([
                                    'status' => false,
                                    'authenticate' => true,
                                    'message' => $validateUser->messages()->first()
                                ], 200);
                            }

                            $insertData['firstname']=$request->firstname;
                            $insertData['lastname']= @$request->lastname;
                            $insertData['email']=$request->email;
                        }

                        $insertData['apple_id'] = $request->id;
                        $insertData['avatar'] = "images/avatars/default.png";
                        $insertData['password'] = Hash::make('immersive2');
                        $insertData['bc_id'] = md5('immersive2');
                        $insertData['role']=3;
                        $error_msg = 'Registration are not created successfully';
                        $exUser_ = User::where('email', $insertData['email'])->withTrashed()->count();
                        if($exUser_ > 0)
                        {
                            return response()->json([            
                                    'status' => false,       
                                    'authenticate' => true,         
                                    'message' => "The email has already been taken",            
                                ], 500);
                        }
                        else
                        {
                            $insertData['status']=1;
                            $user = User::create($insertData);
                        }
                    }
                }
                elseif($request->login_type == 'facebook'){
                    $exUser = User::where(['fb_id' => $request->id ])->withTrashed()->first();
                    if(!is_null($exUser))
                    {
                        $exUser->update([
                            'deleted_at' => ''
                        ]);

                        $user = $exUser;
                        $message = 'User logged in successfully';
                        $error_msg = 'User logged in successfully';
                        if($user->status == 1){
                            $this->setLog($user);
                            return response()->json([
                                'status' => true,
                                'message' => 'User logged in successfully',
                                'token' => $user->createToken("API TOKEN")->plainTextToken
                            ], 200);
                        }
                        else{
                            return response()->json([            
                                    'status' => false,            
                                    'authenticate' => false, 
                                    'message' => "Account is inactive",            
                                ], 500);
                        }
                    }
                    else{
                        $previousData = DB::table('users_deleted')->orderBy('id','desc')->where('fb_id', $request->id)->first();
                        if(!empty($previousData))
                        {
                            if($previousData->firstname != '' && $previousData->email != '')
                            {
                                $insertData['firstname'] = $previousData->firstname;
                                $insertData['lastname'] = $previousData->lastname;
                                $insertData['email'] = $previousData->email;
                            }
                        }
                        else
                        {
                            $validateUser = Validator::make($request->all(), 
                            [
                                'email' => 'required|email|unique:users|max:70',
                                'id' => 'required',
                                'login_type' => 'required',
                                'firstname' => 'required'
                            ]);
                            if($validateUser->fails()){
                                return response()->json([
                                    'status' => false,
                                    'authenticate' => true,
                                    'message' => $validateUser->messages()->first()
                                ], 200);
                            }

                            $insertData['firstname']=$request->firstname;
                            $insertData['lastname']= @$request->lastname;
                            $insertData['email']=$request->email;
                        }

                        $insertData['fb_id'] = $request->id;
                        $insertData['avatar'] = "images/avatars/default.png";
                        $insertData['password'] = Hash::make('immersive3');
                        $insertData['bc_id'] = md5('immersive3');
                        $insertData['role']=3;
                        $error_msg = 'Registration are not created successfully';
                        $exUser_ = User::where('email', $insertData['email'])->withTrashed()->count();
                        if($exUser_ > 0)
                        {
                            return response()->json([            
                                    'status' => false,     
                                    'authenticate' => true,           
                                    'message' => "The email has already been taken",            
                                ], 500);
                        }
                        else
                        {
                            $insertData['status']=1;
                            $user = User::create($insertData);
                        }
                    }
                }
                if(!empty($user)){
                    if($user->status == 1){
                        $this->setLog($user);
                        return response()->json([
                            'status' => true,
                            'message' => 'User created successfully',
                            'token' => $user->createToken("API TOKEN")->plainTextToken
                        ], 200);
                    }
                    else{
                        return response()->json([            
                                'status' => false,            
                                'authenticate' => false, 
                                'message' => "Account is inactive",            
                            ], 500);
                    }
                }
                else{
                    return response()->json([
                        'status' => false,
                        'authenticate' => true,
                        'message' => 'Record not found.',
                    ], 200);
                }
            }

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => $th->getMessage()
            ], 500);
        }
    }
	
    /******************************************************************************
    *   @function: Remove user 
    * 
    *****************************************************************************/
    public function removeAccount(Request $request)
    {
        $user = $request->user();        
        $userData = User::select('id','firstname','lastname','google_id','apple_id','email', 'fb_id', 'role')->where('id', $user->id)->first()->toarray();
        $postData = array(
                'id' => $userData['id'],
                'firstname' => $userData['firstname'],
                'lastname' => ($userData['lastname'] == "")?null:$userData['lastname'],
                'google_id' => ($userData['google_id'] == "")?null:$userData['google_id'],
                'apple_id' => ($userData['apple_id'] == "")?null:$userData['apple_id'],
                'fb_id' => ($userData['fb_id'] == "")?null:$userData['fb_id'],
                'email' => $userData['email'],
                'role' => $userData['role']
            );
        DB::table('users_deleted')->insert($postData);
        User::where('id', $user->id)->forceDelete();
        auth()->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => true,
            'message' => 'Account remove successfully!'
        ], 200);
    }

    /******************************************************************************
     * Function@ set log
     * 
     ******************************************************************************/
    public function setLog($user)
    {
        $ip = vIpInfo()->ip;
        $userLog = UserLog::where([['user_id', $user->id], ['ip', $ip]])->first();
        $log = new UserLog();
        if ($userLog != null) {
            $userLog->country = vIpInfo()->country;
            $userLog->country_code = vIpInfo()->country_code;
            $userLog->timezone = vIpInfo()->timezone;
            $userLog->location = vIpInfo()->location;
            $userLog->latitude = vIpInfo()->latitude;
            $userLog->longitude = vIpInfo()->longitude;
            $userLog->browser = vBrowser();
            $userLog->os = vPlatform();
            $userLog->update();
        } else {
            $log->user_id = $user->id;
            $log->ip = vIpInfo()->ip;
            $log->country = vIpInfo()->country;
            $log->country_code = vIpInfo()->country_code;
            $log->timezone = vIpInfo()->timezone;
            $log->location = vIpInfo()->location;
            $log->latitude = vIpInfo()->latitude;
            $log->longitude = vIpInfo()->longitude;
            $log->browser = vBrowser();
            $log->os = vPlatform();
            $log->save();
        }
    }

	/******************************************************************************
	*	Get Orders
	*****************************************************************************/
	public function getProfile(Request $request){   
		$user = $request->user();
        $response = [];
        try {
            $response['id'] = $user->id;
            $response['firstname'] = $user->firstname;
            $response['lastname'] = ($user->lastname == null)?"":$user->lastname;
            $response['email'] = $user->email;
            $response['mobile'] = ($user->mobile == null)?"":$user->mobile;
            $response['address'] = (@$user->address->address_1 == null)?"":@$user->address->address_1;
            $response['latitude'] = ($user->latitude == null)?"":$user->latitude;
            $response['longitude'] = ($user->longitude == null)?"":$user->longitude;
            $response['image'] = $user->avatar;
            return response()->json([
                'status' => true,
                'data'    => $response
            ], 200);
        } catch (e $exception) {
            return response()->json([
                'status' => false,
                'authenticate' => true,    
                'message' => 'Record not found',
                'data'  => array()
            ], 200);
        }
	}

    /******************************************************************************
     * @Function: Update profile 
     ******************************************************************************/
    public function updateProfile(Request $request){
        $user = $request->user();
        $validateUser = Validator::make($request->all(), 
        [
            'firstname' => 'required',
            'lastname' => 'required',
            'mobile' => 'required|min:10|max:15|unique:users,mobile,'.$user->id,
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => $validateUser->messages()->first()
            ], 200);
        }

        $rowAffected = $user->update([
                        'firstname' => $request->firstname,
                        'lastname' => $request->lastname,
                        'mobile' => $request->mobile
                    ]);
        return response()->json([
                'status' => true,
                'message' => 'Profile update successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
    }

    /******************************************************************************
     * @function: Logout
     *****************************************************************************/
    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();
        return response()->json([
                'status' => true,
                'message' => 'Logged out successfully'
            ], 200);
    }

    /****************************************************************************
     *@Function: Splace Screen     * 
     ****************************************************************************/
    public function splashScreen(Request $request)
    {   
        $result = Generalsetting::where(['id' => 1])->first();        
        if (!empty($result)) {
            $resp['splash_screen'] = 'assets/images/'.$result->splash_screen;
            return response()->json([
                'status' => true,
                'data' => $resp,
                'message' => 'Splash screen'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => 'Record not found.',
            ], 200);
        }              
    }

    /****************************************************************************
     *@Function: Splace Screen     * 
     ****************************************************************************/
    public function appVersion(Request $request)
    {   
        $result = Generalsetting::where(['id' => 1])->first();        
        if (!is_null($result)) {
            $resp['app_version'] = $result->app_version;
            return response()->json([
                'status' => true,
                'data' => $resp,
                'message' => 'App version'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => 'Record not found.',
            ], 200);
        }              
    }

    /****************************************************************************
     *@Function: Terms Of Use
     ****************************************************************************/
    public function termsOfUse(Request $request)
    {   
        $result = Page::where(['id' => 1])->first();        
        if (!empty($result)) {
            $response = [];
            $response['title'] = $result->title;
            $response['content'] = strip_tags($result->content);
            return response()->json([
                'status' => true,
                'message' => 'Terms Of Use',
                'data' => $response,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => 'Record not found.',
            ], 200);
        }              
    }

    /******************************************************************************
     *@Function: Privacy Policy     * 
     *****************************************************************************/
    public function privacyPolicy(Request $request)
    {   
        $result = Page::where(['id' => 2])->first();        
        if (!empty($result)) {
            $response = [];
            $response['title'] = $result->title;
            $response['content'] = strip_tags($result->content);
            return response()->json([
                'status' => true,
                'message' => 'Privacy policy',
                'data' => $response,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => 'Record not found.',
            ], 200);
        }              
    }

    /******************************************************************************
     *@Function: Privacy Policy     * 
     *****************************************************************************/
    public function contactUs(Request $request)
    {   
        $result = Page::where(['id' => 3])->first();        
        if (!empty($result)) {
            $response = [];
            $response['title'] = $result->title;
            $response['content'] = $result->content;
            return response()->json([
                'status' => true,
                'message' => 'Contact us',
                'data' => $response,
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => 'Record not found.',
            ], 200);
        }              
    }

    /******************************************************************************
     *@Function: Intro Screen     * 
     *****************************************************************************/
    public function introScreen(Request $request)
    {   
        $response = [];
        $result = Generalsetting::where(['id' => 1])->get();        
        if (count($result) > 0) {
            $data1['id'] = $result[0]->id;
            $data1['screen1'] = 'assets/images/'.$result[0]->intro_screen_1;
            $response[] = $data1;
            $data2['id'] = $result[0]->id;
            $data2['screen2'] = 'assets/images/'.$result[0]->intro_screen_2;
            $response[] = $data2;
            $data3['id'] = $result[0]->id;
            $data3['screen3'] = 'assets/images/'.$result[0]->intro_screen_3;
            $response[] = $data3;
           
            return response()->json([
                'status' => true,
                'data' => $response,
                'message' => 'Into screen list'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => 'Record not found.',
            ], 200);
        }              
    }

    /******************************************************************************
     *@Function: Slider
     *****************************************************************************/
    public function slider(Request $request)
    {   
        $validateUser = Validator::make($request->all(), 
        [
            'language_id' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => $validateUser->messages()->first()
            ], 200);
        }
        $response = [];
        $result = Slider::where(['language_id' => $request->language_id])->get();        
        if (count($result) > 0) {
            foreach ($result as $cat) {
                $data['id'] = $cat->id;
                $data['subtitle'] = $cat->subtitle_text;
                $data['title'] = $cat->title_text;
                $data['image'] = 'assets/images/sliders/'.$cat->photo;
                $response[] = $data;
            }           
            return response()->json([
                'status' => true,
                'data' => $response,
                'message' => 'Slider list'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => 'Record not found.',
            ], 200);
        }              
    }

    /******************************************************************************
     *@Function: Product list
     *****************************************************************************/
    public function productList(Request $request)
    {   
        $validateUser = Validator::make($request->all(), 
        [
            'language_id' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => $validateUser->messages()->first()
            ], 200);
        }
        $response = [];
        $result = Product::select('id', 'photo', 'name', 'slug', 'price')->where(['status' => 1, 'language_id' => $request->language_id])->orderByDesc('id')->get();
        $currency = DB::table('currencies')->where('is_default','=',1)->first();
        if (count($result) > 0) {
            foreach ($result as $res) {
                $data['id'] = $res->id;
                $data['name'] = $res->name;
                $data['slug'] = $res->slug;
                $data['sign'] = $currency->sign;
                $data['price'] = round($res->price * $currency->value , 2);
                $data['image'] = 'assets/images/products/'.$res->photo;
                $response[] = $data;
            }           
            return response()->json([
                'status' => true,
                'data' => $response,
                'message' => 'Product list'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => 'Record not found.',
            ], 200);
        }              
    }

    /******************************************************************************
     *@Function: Product details
     *****************************************************************************/
    public function productDetails(Request $request)
    {   
        $validateUser = Validator::make($request->all(), 
        [
            'language_id' => 'required',
            'product_id' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => $validateUser->messages()->first()
            ], 200);
        }
        $response = [];
        $result = Product::select('id', 'photo', 'name', 'slug', 'price')->where(['status' => 1, 'id' => $request->product_id, 'language_id' => $request->language_id])->orderByDesc('id')->get();
        $currency = DB::table('currencies')->where('is_default','=',1)->first();
        if (count($result) > 0) {
            foreach ($result as $res) {
                $data['id'] = $res->id;
                $data['name'] = $res->name;
                $data['slug'] = $res->slug;
                $data['sign'] = $currency->sign;
                $data['price'] = round($res->price * $currency->value , 2);
                $data['image'] = 'assets/images/products/'.$res->photo;
                $response[] = $data;
            }           
            return response()->json([
                'status' => true,
                'data' => $response,
                'message' => 'Product details'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => 'Record not found.',
            ], 200);
        }              
    }

    /******************************************************************************
     *@Function: Categories
     *****************************************************************************/
    public function categories(Request $request)
    {   
        $validateUser = Validator::make($request->all(), 
        [
            'language_id' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => $validateUser->messages()->first()
            ], 200);
        }
        $response = [];
        $result = Category::where(['status' => 1, 'language_id' => $request->language_id])->get();        
        if (count($result) > 0) {
            foreach ($result as $cat) {
                $data['id'] = $cat->id;
                $data['name'] = $cat->name;
                $data['slug'] = $cat->slug;
                $data['icon'] = 'assets/images/categories/'.$cat->photo;
                $response[] = $data;
            }           
            return response()->json([
                'status' => true,
                'data' => $response,
                'message' => 'Categories list'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => 'Record not found.',
            ], 200);
        }              
    }

    /******************************************************************************
     *@Function: Sub Categories
     *****************************************************************************/
    public function subCategories(Request $request)
    {   
        $validateUser = Validator::make($request->all(), 
        [
            'category_id' => 'required',
            'language_id' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => $validateUser->messages()->first()
            ], 200);
        }
        $response = [];
        $result = Subcategory::where(['status' => 1, 'category_id' => $request->category_id, 'language_id' => $request->language_id])->get();        
        if (count($result) > 0) {
            foreach ($result as $cat) {
                $data['id'] = $cat->id;
                $data['name'] = $cat->name;
                $data['slug'] = $cat->slug;
                $data['icon'] = 'assets/images/sub-categories/'.$cat->icon;
                $response[] = $data;
            }           
            return response()->json([
                'status' => true,
                'data' => $response,
                'message' => 'Sub categories list'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => 'Record not found.',
            ], 200);
        }              
    }

    /******************************************************************************
     *@Function: Chaild Categories
     *****************************************************************************/
    public function childCategories(Request $request)
    {   
        $validateUser = Validator::make($request->all(), 
        [
            'subcategory_id' => 'required',
            'language_id' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => $validateUser->messages()->first()
            ], 200);
        }
        $response = [];
        $result = Childcategory::where(['status' => 1, 'subcategory_id' => $request->subcategory_id, 'language_id' => $request->language_id])->get();        
        if (count($result) > 0) {
            foreach ($result as $cat) {
                $data['id'] = $cat->id;
                $data['name'] = $cat->name;
                $data['slug'] = $cat->slug;
                $data['icon'] = 'assets/images/child-category/'.$cat->icon;
                $response[] = $data;
            }           
            return response()->json([
                'status' => true,
                'data' => $response,
                'message' => 'Clild categories list'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' => 'Record not found.',
            ], 200);
        }              
    }

    /******************************************************************************
     * @Function: Forgot password 
     ******************************************************************************/
    public function forgotPassword(Request $request)
    {
        $data = $request->only('email');
        $validator = Validator::make($data, [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json(['status'=>false,'message' => $validator->messages()->first()], 200);
        }
        $user = User::where(['email'=>$request->email])->first();
        if (!empty($user)) {
            $password['password']= Str::random(12);
            $user->password= Hash::make($password['password']);
            $user->bc_id= md5($password['password']);
            $user->save();
            $email=$user->email;
            $password['email']=$email;
            $password['user_name']=ucfirst($user->firstname);
            $html= view('mail.forgot_password',$password);
         
            $this->send_email($email,'Forgot Password',$html);
            return response()->json([
                'status' => true,
                'message' => "Password has been send to registered email Id",
            ]);
        }else {
            return response()->json([
                'status' => false,
                'authenticate' => true,
                'message' =>"This email is not registered",
            ], 500);
        }
    }

    /***************************************************************************** 
    * Send Email 
    *****************************************************************************/
    public static function send_email($email,$subject,$message)
    {   
        $params = array(

            'to'        => $email,   

            'subject'   => $subject,

            'html'      => $message,

            'from'      => 'support@easycare.manageprojects.in',
            
            'fromname'  => 'EasyCare'

        );

        $request =  'https://api.sendgrid.com/api/mail.send.json';

        $headr = array();

        $pass = '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';

        $headr[] = 'Authorization: Bearer '.$pass;
    
        $session = curl_init($request);

        curl_setopt ($session, CURLOPT_POST, true);

        curl_setopt ($session, CURLOPT_POSTFIELDS, $params);

        curl_setopt($session, CURLOPT_HEADER, false);

        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        // add authorization header

        curl_setopt($session, CURLOPT_HTTPHEADER,$headr);

        $response = curl_exec($session);

        curl_close($session);

        return true;
    }
}
