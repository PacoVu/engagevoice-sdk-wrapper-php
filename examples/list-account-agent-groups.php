<?php
require('../src/EngageVoiceSDKWrapper.php');

const RC_CLIENT_ID="";
const RC_CLIENT_SECRET="";

const RC_USERNAME="";
const RC_PASSWORD="";
const RC_EXTENSION="";

$ev = new EngageVoiceSDKWrapper\RestClient(RC_CLIENT_ID, RC_CLIENT_SECRET);

try{
    $ev->login(RC_USERNAME, RC_PASSWORD, RC_EXTENSION, function($response){
        list_account_agent_groups();
    });
}catch (Exception $e) {
    print $e->getMessage();
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
