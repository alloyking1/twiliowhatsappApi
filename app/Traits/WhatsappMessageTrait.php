<?php

    namespace App\Traits;
    use GuzzleHttp\Exception\RequestException;
    use Twilio\Rest\Client;
    
    trait WhatsappMessageTrait {            

        /**
         * send 2fa verification whatsapp message
         * @param null
         * @request null
         */
        public function sendOtp($recipient, $code){

            $sid    = getenv("TWILIO_SID"); 
            
            $token  = getenv("TWILIO_AUTH_TOKEN"); 
            $twilio = new Client($sid, $token); 
            $from = getenv("TWILIO_WHATSAPP_NUMBER");
            
            $message = $twilio->messages 
                ->create("whatsapp:".$recipient, // to 
                    array( 
                        "from" => "whatsapp:".$from,       
                        "body" => "Your OTP verification code  is ". $code 
                    ) 
                ); 

        }

        public function generateCode(){
            return mt_rand();
        }

    }