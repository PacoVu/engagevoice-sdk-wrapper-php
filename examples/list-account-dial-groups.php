<?php
require('../src/engagevoice_sdk_wrapper.php');

const RC_CLIENT_ID="";
const RC_CLIENT_SECRET="";

const RC_USERNAME="";
const RC_PASSWORD="";
const RC_EXTENSION="";

$ev = new engagevoice\RestClient(RC_CLIENT_ID, RC_CLIENT_SECRET);
try{
    $ev->login(RC_USERNAME, RC_PASSWORD, RC_EXTENSION, function($response){
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
