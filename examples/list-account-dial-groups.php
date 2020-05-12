<?php
require('vendor/autoload.php');

const RC_CLIENT_ID="";
const RC_CLIENT_SECRET="";

const RC_USERNAME="";
const RC_PASSWORD="";
const RC_EXTENSION="";

const LEGACY_USERNAME= "";
const LEGACY_PASSWORD= "";

const MODE = "ENGAGE";

if (MODE == "ENGAGE"){
  $ev = new EngageVoiceSDKWrapper\RestClient(RC_CLIENT_ID, RC_CLIENT_SECRET);
  $username= RC_USERNAME;
  $password = RC_PASSWORD;
  $extensionNum = RC_EXTENSION;
}else{
  $ev = new EngageVoiceSDKWrapper\RestClient();
  $username= LEGACY_USERNAME;
  $password = LEGACY_PASSWORD;
  $extensionNum = "";
}

try{
    $ev->login($username, $password, $extensionNum, function($response){
        get_account_dial_groups();
    });
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
