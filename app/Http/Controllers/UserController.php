<?php

    namespace App\Http\Controllers;

    use App\User;
    use App\Traits\WhatsappMessageTrait;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Validator;
    use JWTAuth;
    use Auth;
    use Tymon\JWTAuth\Exceptions\JWTException;

    class UserController extends Controller
    {
        use WhatsappMessageTrait; 

        public function authenticate(Request $request)
        {
            $credentials = $request->only('email', 'password');

            try{
                if (! $token = JWTAuth::attempt($credentials)) {
                        return response()->json(['error' => 'invalid_credentials'], 400);
                } 

                try{
                        $code = $this->generateCode();
                        $this->saveOtpCode($code);
                        $this->sendOtp("+2348063146940", $code);
                }catch(JWTException $e){
                        return response()->json(['error sending opt', 500]);
                }
            }catch (JWTException $e){
                
                return response()->json(['error' => 'could_not_create_token'], 500);
            }

        //     return response()->json(compact('token'));
            return response()->json(["message"=>"OTP sent to whatsapp successfully, verfity to continue", "token" => $token], 200);
        }

        public function register(Request $request)
        {
                $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
                ]);

                if($validator->fails()){
                        return response()->json($validator->errors()->toJson(), 400);
                }

                $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
                ]);

                $token = JWTAuth::fromUser($user);

                return response()->json(compact('user','token'),201);
        }

        public function getAuthenticatedUser()
        {
                try {

                        if (! $user = JWTAuth::parseToken()->authenticate()) {
                                return response()->json(['user_not_found'], 404);
                        }

                } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

                        return response()->json(['token_expired'], $e->getStatusCode());

                } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

                        return response()->json(['token_invalid'], $e->getStatusCode());

                } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

                        return response()->json(['token_absent'], $e->getStatusCode());

                }

                return response()->json(compact('user'));
        }

        /**
         * Verifies the OTP code 
         * @params $incomingCode $email
         * @return true || error
         */
        protected function verifyOtp(Request $request, $email){
                $savedCode = User::where('email', $request->email)->first();
                if($savedCode->otp === $request->code){

                       //todo get authenticated user here 
                       return response()->json("Account Verified", 200); 
                }
                return response()->json(['message'=> 'Invalid OTP Provided'], 500);
        }

        /**
         * save OTP code in database 
         * @params $incomingCode $email
         * @return true || error
         */
        protected function  saveOtpCode($code){
                $user = Auth::user();
                $user->otp = $code;
                $user->save();
        }

    }