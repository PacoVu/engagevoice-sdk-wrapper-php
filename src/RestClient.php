<?php
namespace EngageVoiceSDKWrapper;

class RestClient {
    const RC_TOKEN_FILE = "rc_tokens.txt";

    const RC_SERVER_URL = "https://platform.ringcentral.com";
    const EV_SERVER_URL = "https://engage.ringcentral.com";
    const EV_SERVER_AND_PATH = "https://engage.ringcentral.com/voice/api/v1/";

    const LEGACY_SERVER_AND_PATH = "https://portal.vacd.biz/api/v1/";

    private $mode = "";
    private $server = "";
    private $clientId = "";
    private $clientSecret = "";

    private $accessToken = null;
    private $accountId = null;
    private $accountInfo = null;

    public function __construct($clientId=null, $clientSecret=null) {
        if ($clientId == null || $clientSecret == null){
          $this->server = self::LEGACY_SERVER_AND_PATH;
          $this->mode = "Legacy";
        }else{
          $this->server = self::EV_SERVER_AND_PATH;
          $this->clientId = $clientId;
          $this->clientSecret = $clientSecret;
          $this->mode = "Engage";
        }
    }

    public function getAccountInfo() {
        return $this->accountInfo;
    }

    public function getAccountId() {
      return $this->accountId;
    }

    function setAccessToken($accessToken, $callback=null){
        $this->accessToken = $accessToken;
        $this->readAccount();
        return ($callback == null) ? $this->accountInfo : $callback($this->accountInfo);
    }
    public function login($username, $password, $extension=null, $callback=null) {
        if ($this->mode == "Engage"){
            $rcAccessToken = $this->rcLogin($username, $password, $extension);
            $resp = $this->exchangeAccessTokens($rcAccessToken);
            if ($resp)
              return ($callback == null) ? json_decode($resp['body']) : $callback($resp);
            else
              return ($callback == null) ? $resp : $callback($resp);
        }else{
          if ($this->accessToken != null){
              return ($callback == null) ? $this->accessToken : $callback($this->accessToken);
          }else{
              $this->generateAuthToken($username, $password);
              return ($callback == null) ? $this->accessToken : $callback($this->accessToken);
          }
        }
    }

    public function get($endpoint, $params=null, $callback=""){
        if ($this->accessToken == null){
            return ($callback == "") ? "Login required!" : $callback("Login required!");
        }
        $apiEndpoint = $endpoint;
        if (strpos($endpoint, '~') !== false){
            $pattern = '/~/';
            $apiEndpoint = preg_replace('/~/', $this->getAccountId(), $endpoint);
        }
        $url = $this->server . $apiEndpoint;
        print ($url."\r\n");
        if ($params !== null)
            $url .= "?".http_build_query($params);

        $headers = array (
                    'Accept: application/json',
                    'Authorization: Bearer ' . $this->accessToken
                  );

        if ($this->mode == "Legacy")
            $headers = array (
                    'Accept: application/json',
                    'X-Auth-Token: ' . $this->accessToken
                  );

        $resp = $this->sendRequest('GET', $url, $headers);
        if ($resp != null){
          return ($callback == "") ? $resp['body'] : $callback($resp);
        }else{
          return ($callback == "") ? "AcccessToken expired. Login required!" : $callback("AcccessToken expired. Login required!");
        }
    }

    public function post($endpoint, $params=null, $callback=""){
        if ($this->accessToken == null){
            return ($callback == "") ? "Login required!" : $callback("Login required!");
        }

        if (strpos($endpoint, '~') !== false){
            $pattern = '/~/';
            $endpoint = preg_replace('/~/', $this->getAccountId(), $endpoint);
        }
        $url = $this->server . $endpoint;
        $body = array();
        if ($params != null)
            $body = json_encode($params);

        $headers = array (
                  'Content-Type: application/json',
                  'Authorization: Bearer ' . $this->accessToken
                );
        if ($this->mode == "Legacy")
            $headers = array (
                  'Content-Type: application/json',
                  'X-Auth-Token: ' . $this->accessToken
            );

        $resp = $this->sendRequest('POST', $url, $headers, $body);
        if ($resp != null){
          return ($callback == "") ? $resp['body'] : $callback($resp);
        }else{
          return ($callback == "") ? "AcccessToken expired. Login required!" : $callback("AcccessToken expired. Login required!");
        }
    }
    public function put($endpoint, $params=null, $callback=""){
        if ($this->accessToken == null){
            return ($callback == "") ? "Login required!" : $callback("Login required!");
        }
        if (strpos($endpoint, '~') !== false){
            $pattern = '/~/';
            $endpoint = preg_replace('/~/', $this->getAccountId(), $endpoint);
        }
        $url = $this->server . $endpoint;
        $body = array();
        if ($params != null)
            $body = json_encode($params);
        $headers = array (
                  'Content-Type: application/json',
                  'Authorization: Bearer ' . $this->accessToken
                );
        if ($this->mode == "Legacy")
            $headers = array (
                  'Content-Type: application/json',
                  'X-Auth-Token: ' . $this->accessToken
                );

        $resp = $this->sendRequest('PUT', $url, $headers, $body);
        if ($resp != null){
          return ($callback == "") ? $resp['body'] : $callback($resp);
        }else{
          return ($callback == "") ? "AcccessToken expired. Login required!" : $callback("AcccessToken expired. Login required!");
        }
    }

    public function delete($endpoint, $params=null, $callback=""){
        if ($this->accessToken == null){
            return ($callback == "") ? "Login required!" : $callback("Login required!");
        }
        if (strpos($endpoint, '~') !== false){
            $pattern = '/~/';
            $endpoint = preg_replace('/~/', $this->getAccountId(), $endpoint);
        }
        $url = $this->server . $endpoint;
        $body = array();
        if ($params != null)
            $body = json_encode($params);
        $headers = array (
                  'Content-Type: application/json',
                  'Authorization: Bearer ' . $this->accessToken
                );
        if ($this->mode == "Legacy")
            $headers = array (
                  'Content-Type: application/json',
                  'X-Auth-Token: ' . $this->accessToken
                );

        $resp = $this->sendRequest('DELETE', $url, $headers, $body);
        if ($resp != null){
          return ($callback == "") ? $resp['body'] : $callback($resp);
        }else{
          return ($callback == "") ? "AcccessToken expired. Login required!" : $callback("AcccessToken expired. Login required!");
        }
    }
    private function sendRequest($method, $url, $headers, $body=""){
      try{
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        if ($body != "")
          curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $strResponse = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            throw new \Exception($curlErrno);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $array = array (
                'status' => $httpCode,
                'headers' => curl_getinfo($ch),
                'body'	=> $strResponse
              );
            curl_close($ch);
            if ($httpCode == 200 || $httpCode == 201) {
                return $array;
            }else if ($httpCode == 401){
                print ("EV access token expired\r\n");
                return null;
            }else{
                throw new \Exception($strResponse);
            }
        }
      }catch (\Exception $e) {
          throw $e;
      }
    }

    private function rcLogin($username, $password, $extension){
        $rcAccessToken = $this->checkSavedTokens();
        if ($rcAccessToken != null){
            return $rcAccessToken;
        }else{ // login RC
          $url = self::RC_SERVER_URL."/restapi/oauth/token";
          $basic = $this->clientId .":". $this->clientSecret;
          $headers = array (
              'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
              'Accept: application/json',
              'Authorization: Basic ' . base64_encode($basic)
            );
          if ($extension == null)
            $body = http_build_query(array (
                'grant_type' => 'password',
                'username' => urlencode($username),
                'password' => $password
              ));
          else
            $body = http_build_query(array (
                'grant_type' => 'password',
                'username' => urlencode($username),
                'password' => $password,
                'extension' => $extension
              ));

          if (file_exists(self::RC_TOKEN_FILE)){
              $saved_tokens = file_get_contents(self::RC_TOKEN_FILE);
              $tokensObj = json_decode($saved_tokens);
              $date = new \DateTime();
              $expire_time= $date->getTimestamp() - $tokensObj->timestamp;
              if ($expire_time < $tokensObj->tokens->expires_in){
                return $tokensObj->tokens->access_token;
              }else if ($expire_time <  $tokensObj->tokens->refresh_token_expires_in) {
                  $body = http_build_query(array (
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $tokensObj->tokens->refresh_token
                  ));
              }
          }
          try {
              $resp = $this->sendRequest('POST', $url, $headers, $body);
              if ($resp){
                  $date = new \DateTime();
                  $jsonObj = json_decode($resp['body']);
                  $tokensObj = array(
                    "tokens" => $jsonObj,
                    "timestamp" => $date->getTimestamp()
                  );
                  file_put_contents(self::RC_TOKEN_FILE, json_encode($tokensObj, JSON_PRETTY_PRINT));
                  return $jsonObj->access_token;
              }else{
                return null;
              }
          } catch (\Exception $e) {
              throw $e;
          }
        }
    }
    private function checkSavedTokens(){
        if (file_exists(self::RC_TOKEN_FILE)){
            $saved_tokens = file_get_contents(self::RC_TOKEN_FILE);
            $tokensObj = json_decode($saved_tokens);
            $date = new \DateTime();
            $expire_time= $date->getTimestamp() - $tokensObj->timestamp;
            if ($expire_time < $tokensObj->tokens->expires_in){
              return $tokensObj->tokens->access_token;
            }else if ($expire_time <  $tokensObj->tokens->refresh_token_expires_in) {
                $url = self::RC_SERVER_URL."/restapi/oauth/token";
                $basic = $this->clientId .":". $this->clientSecret;
                $headers = array (
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'Accept: application/json',
                    'Authorization: Basic ' . base64_encode($basic)
                  );
                $body = http_build_query(array (
                  'grant_type' => 'refresh_token',
                  'refresh_token' => $tokensObj->tokens->refresh_token
                ));
                try {
                    $resp = $this->sendRequest('POST', $url, $headers, $body);
                    if ($resp){
                      $date = new \DateTime();
                      $jsonObj = json_decode($resp['body']);
                      $tokensObj = array(
                        "tokens" => $jsonObj,
                        "timestamp" => $date->getTimestamp()
                      );
                      file_put_contents(self::RC_TOKEN_FILE, json_encode($tokensObj, JSON_PRETTY_PRINT));
                      return $jsonObj->access_token;
                    }else{
                      return null;
                    }
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        }else{
          return null;
        }
    }

    private function exchangeAccessTokens($rcAccessToken){
        $url = self::EV_SERVER_URL . "/api/auth/login/rc/accesstoken?";
        $body = 'rcAccessToken='.$rcAccessToken."&rcTokenType=Bearer";
        $headers = array (
                  'Content-Type: application/x-www-form-urlencoded'
              );
        try {
            $resp = $this->sendRequest('POST', $url, $headers, $body);
            if ($resp){
              $tokensObj = json_decode($resp['body']);
              $this->accessToken = $tokensObj->accessToken;
              $this->accountInfo = $tokensObj->agentDetails;
              $this->accountId = $tokensObj->agentDetails[0]->accountId;
              return $resp;
            }else{
              return null;
            }
        }catch (\Exception $e) {
            throw $e;
        }
    }

    private function generateAuthToken($username, $password) {
      $url = $this->server. "auth/login";
      $body = "username=" . $username . "&password=" . $password;
      $headers = array ('Content-Type: application/x-www-form-urlencoded');
      try{
          $resp = $this->sendRequest('POST', $url, $headers, $body);
          if ($resp){
            $jsonObj = json_decode($resp['body']);
            $this->accountId = $jsonObj->accounts[0]->accountId;
            $this->readPermanentsToken($jsonObj->authToken);
            return $jsonObj;
          }else{
            return null;
          }
      }catch (\Exception $e) {
          throw $e;
      }
    }

    private function readPermanentsToken($authToken){
      $url = $this->server. "admin/token";
      $headers = array ('X-Auth-Token: '. $authToken);
      try{
        $resp = $this->sendRequest('GET', $url, $headers);
        if ($resp){
          $jsonObj = json_decode($resp['body']);
          if (count($jsonObj)){
              $this->accessToken = $jsonObj[0];
              return $jsonObj;
          }else{
              return $this->generatePermanentToken($authToken);
          }
        }else{
          return null;
        }
      }catch (\Exception $e){
          throw $e;
      }
    }

    private function generatePermanentToken($authToken){
        $url = $this->server. "admin/token";
        $headers = array ('X-Auth-Token: '. $authToken);
        try{
            $resp = $this->sendRequest('POST', $url, $headers);
            if ($resp){
              $this->accessToken = $resp['body'];
              return $resp['body'];
            }else{
              return null;
            }
        }catch (\Exception $e) {
            throw $e;
        }
    }

    private function readAccount(){
        $url = $this->server . "admin/accounts";
        $headers = array (
                    'Accept: application/json',
                    'Authorization: Bearer ' . $this->accessToken
                  );

        if ($this->mode == "Legacy")
            $headers = array (
                    'Content-Type: application/json',
                    'X-Auth-Token: ' . $this->accessToken
                    );

        $resp = $this->sendRequest('GET', $url, $headers);
        if ($resp){
          $this->accountInfo = json_decode($resp['body']);
          if (count($this->accountInfo)){
            $this->accountId = $this->accountInfo[0]->accountId;
          }
        }
        return $resp;
    }
}
