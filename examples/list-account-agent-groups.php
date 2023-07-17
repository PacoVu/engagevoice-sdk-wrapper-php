<?php
require('vendor/autoload.php');

const RC_CLIENT_ID="";
const RC_CLIENT_SECRET="";

const RC_JWT="";

const LEGACY_USERNAME= "";
const LEGACY_PASSWORD= "";

const MODE = "LEGACY";

try{
  if (MODE == "ENGAGE"){
    $ev = new EngageVoiceSDKWrapper\RestClient(RC_CLIENT_ID, RC_CLIENT_SECRET);
    $ev->login([ 'jwt' => RC_JWT ], function($response){
        list_account_agent_groups();
    });
  }else{
    $ev = new EngageVoiceSDKWrapper\RestClient();
    $ev->login([ "username" => LEGACY_USERNAME, "password" => LEGACY_PASSWORD ], function($response){
        list_account_agent_groups();
    });
  }
}catch (Exception $e) {
  ;//print $e->getMessage();
}

function list_account_agent_groups(){
    global $ev;
    $endpoint = "admin/accounts/~/agentGroups";
    try{
        $resp = $ev->get($endpoint);
        print ($resp."\r\n");
    }catch (Exception $e) {
        print $e->getMessage();
    }
}
