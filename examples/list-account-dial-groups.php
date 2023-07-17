<?php
require('vendor/autoload.php');

const RC_CLIENT_ID="";
const RC_CLIENT_SECRET="";

const RC_JWT="";

const LEGACY_USERNAME= "";
const LEGACY_PASSWORD= "";

const MODE = "ENGAGE";
try{
  if (MODE == "ENGAGE"){
    $ev = new EngageVoiceSDKWrapper\RestClient(RC_CLIENT_ID, RC_CLIENT_SECRET);
    $ev->login([ 'jwt' => RC_JWT ], function($response){
        get_account_dial_groups();
    });
  }else{
    $ev = new EngageVoiceSDKWrapper\RestClient();
    $ev->login([ "username" => LEGACY_USERNAME, "password" => LEGACY_PASSWORD ], function($response){
        get_account_dial_groups();
    });
  }
}catch (Exception $e) {
  print $e->getMessage();
}

function get_account_dial_groups(){
    global $ev;
    $endpoint = "admin/accounts/~/dialGroups";
    try{
        $resp = $ev->get($endpoint);
        print ($resp);
    }catch (Exception $e) {
        print $e->getMessage();
    }
}
