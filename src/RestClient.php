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
      /*
        if (count($this->accountInfo) > 0)
            return $this->accountInfo[0]->accountId;
        return "";
      */
    }

    function setAccessToken($accessToken, $callback=null){
        $this->accessToken = $accessToken;
        $this->__readAccount();
        return ($callback == null) ? $this->accountInfo : $callback($this->accountInfo);
    }

    public function login($username, $password, $extension=null, $callback=null) {
        if ($this->mode == "Engage"){
          if ($this->accessToken != null){
              $this->__readAccount();
          } else {
              $accessToken = $this->__rc_login($username, $password, $extension);
              $url = self::EV_SERVER_URL . "/api/auth/login/rc/accesstoken?";
              $body = 'rcAccessToken='.$accessToken."&rcTokenType=Bearer";
              $headers = array (
                        'Content-Type: application/x-www-form-urlencoded'
                    );
              try {
                  $resp = $this->__post($url, $headers, $body);
                  $tokensObj = json_decode($resp['body']);
                  $this->accessToken = $tokensObj->accessToken;
                  $this->accountInfo = $tokensObj->agentDetails;
                  $this->accountId = $tokensObj->agentDetails[0]->accountId;
                  return ($callback == null) ? $tokensObj : $callback($resp);
              }catch (\Exception $e) {
                  throw $e;
              }
          }
        }else{
          if ($this->accessToken != null){
              return ($callback == null) ? $this->accessToken : $callback($this->accessToken);
          }else{
              $this->__generateAuthToken($username, $password);
              //jsonObj = json.loads(res._content)
              return ($callback == null) ? $this->accessToken : $callback($this->accessToken);
          }
        }
    }

    public function get($endpoint, $params=null, $callback=""){
        if ($this->accessToken == null){
            return ($callback == "") ? "Login required!" : $callback("Login required!");
        }

        if (strpos($endpoint, '~') !== false){
            $pattern = '/~/';
            $endpoint = preg_replace('/~/', $this->getAccountId(), $endpoint);
        }
        $url = $this->server . $endpoint;
        print ($url."\r\n");
        if ($params != null)
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

        $resp = $this->_get($url, $headers);
        return ($callback == "") ? $resp['body'] : $callback($resp);
    }

    public function post($endpoint, $params=null, $callback=""){
        if ($this->accessToken == null){
            return ($callback == "") ? "Login required!" : $callback("Login required!");
        }
        try {
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
                  'Accept: application/json, text/plain, */*',
                  'Authorization: Bearer ' . $this->accessToken
                );
            if ($this->mode == "Legacy")
                $headers = array (
                  'Content-Type: application/json',
                  'Accept: application/json',
                  'X-Auth-Token: ' . $this->accessToken
                );

            try {
                $resp = $this->__post($url, $headers, $body);
                return ($callback == "") ? $resp['body'] : $callback($resp);
            }catch (\Exception $e) {
                throw $e;
            }
        }catch (\Exception $e) {
            throw $e;
        }
    }

    private function __post($url, $headers, $body){
      try{
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
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
            if ($httpCode == 200) {
                return $array;
            }else if ($httpCode == 401){
                print ("EV access token expired");
                print($strResponse);
            }else{
                throw new \Exception($strResponse);
            }
        }
      }catch (\Exception $e) {
          throw $e;
      }
    }

    private function __rc_login($username, $password, $extension){
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
            $resp = $this->__post($url, $headers, $body);
            $date = new \DateTime();
            $jsonObj = json_decode($resp['body']);
            $tokensObj = array(
              "tokens" => $jsonObj,
              "timestamp" => $date->getTimestamp()
            );
            file_put_contents(self::RC_TOKEN_FILE, json_encode($tokensObj, JSON_PRETTY_PRINT));
            return $jsonObj->access_token;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function __generateAuthToken($username, $password) {
      $url = $this->server. "auth/login";
      $body = "username=" . $username . "&password=" . $password;
      $headers = array ('Content-Type: application/x-www-form-urlencoded');
      try{
          $resp = $this->__post($url, $headers, $body);
          $jsonObj = json_decode($resp['body']);
          $this->accountId = $jsonObj->accounts[0]->accountId;
          $this->__readPermanentsToken($jsonObj->authToken);
          return $jsonObj;
      }catch (\Exception $e) {
          throw $e;
      }
    }

    private function __readPermanentsToken($authToken){
      $url = $this->server. "admin/token";
      $headers = array ('X-Auth-Token: '. $authToken);
      try{
        $resp = $this->_get($url, $headers);
        $jsonObj = json_decode($resp['body']);
        if (count($jsonObj)){
            $this->accessToken = $jsonObj[0];
            return $jsonObj;
        }else{
            $this->__generatePermanentToken($authToken);
        }
      }catch (\Exception $e){
          throw $e;
      }
    }

    private function __generatePermanentToken($authToken){
        $url = $this->server. "admin/token";
        $headers = array ('X-Auth-Token: '. $authToken);
        try{
            $resp = $this->__post($url, $headers);
            $this->accessToken = $resp['body'];
        }catch (\Exception $e) {
            throw $e;
        }
    }

    private function __readAccount(){
        $url = $this->server . "admin/accounts";
        $headers = array (
                    'Accept: application/json',
                    'Authorization: Bearer ' . $this->accessToken
                  );

        if ($this->mode == "Legacy")
            $headers = array (
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'X-Auth-Token: ' . $this->accessToken
                    );
        $resp = $this->_get($url, $headers);
        $this->accountInfo = json_decode($resp['body']);
        if (count($this->accountInfo)){
          $this->accountId = $this->accountInfo[0]->accountId;
        }
    }

    private function _get($url, $headers) {
      try {
          $ch = curl_init($url);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
          curl_setopt($ch, CURLOPT_TIMEOUT, 600);

          $strResponse = curl_exec($ch);
          $curlErrno = curl_errno($ch);
          if ($curlErrno) {
              throw new \Exception($ecurlError);
          } else {
              $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
              $array = array (
                  'status' => $httpCode,
                  'headers' => curl_getinfo($ch),
                  'body'	=> $strResponse
                );
              curl_close($ch);
              if ($httpCode == 200) {
                  return $array;
              }else{
                  throw new \Exception($strResponse);
              }
          }

      } catch (\Exception $e) {
          throw $e;
      }
    }
}
