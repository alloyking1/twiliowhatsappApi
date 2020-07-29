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
        public function sendOtp($recipient){
            $twilio_whatsapp_number = getenv('TWILIO_WHATSAPP_NUMBER');
            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_AUTH_TOKEN");
            $message = "This is your 2FA verification code ".$this->generateCode();

            $client = new Client($account_sid, $auth_token);
            return $client->messages->create($recipient, array('from' => "whatsapp:$twilio_whatsapp_number", 'body' => $message));

        }

        public function generateCode(){
            return mt_rand();
        }
    }