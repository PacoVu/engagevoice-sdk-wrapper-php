# Engage Voice SDK Wrapper for PHP.

----
## Overview
Engage Voice SDK Wrapper for PHP is a utility class, which helps you easily integrate your PHP project with RingCentral Engage Voice Services. The SDK allows you to authenticate a user in two different modes, the ENGAGE mode and the LEGACY mode. In the ENGAGE mode, you must h

----
## Install the Engage Voice SDK Wrapper
```
$ composer require pacovu/engagevoice-sdk-wrapper:dev-master
```
----
## API References
**Constructor**
```
RestClient($clientId, $clientSecret)
```

*Description:*
* Creates and initializes an EngageVoice SDK wrapper object. If the `$clientId` and `$clientSecret` parameters are provided, the SDK will be set to the ENGAGE mode and you can login with a RingCentral MVP user login credentials. If the `$clientId` and `$clientSecret` parameters are omitted, the SDK will be set to the LEGACY mode and you can login the legacy server using the username and password.

*Parameters:*
* $clientId: set the `clientId` of a RingCentral app to enable login with RingCentral MVP user credentials.
* $clientSecret: set the `clientSecret` of a RingCentral app to enable login with RingCentral MVP user credentials.

*Example code for ENGAGE mode:*
```
require('vendor/autoload.php');

$ev = new EngageVoiceSDKWrapper\RestClient(RINGCENTRAL_CLIENT_ID, RINGCENTRAL_CLIENT_SECRET)
```

*Example code for LEGACY mode:*
```
require('vendor/autoload.php');

$ev = new EngageVoiceSDKWrapper\RestClient()
```

----
**Function login**
    login($options, function ($response) )

*Description:*
* Login using a user's credential. If the SDK mode is "Engage", the username and password must be the valid username and password of a RingCentral Office user.

*Parameters:*
* $options: An array of login credentials. For the "Engage" mode, set the `jwt` token. For the "Legacy" mode, set the `username` and `password`.

*Response:*


*Example code:*
```
// Login with RingCentral Office user credentials.
require('vendor/autoload.php');

$ev = new EngageVoiceSDKWrapper\RestClient(RC_CLIENT_ID, RC_CLIENT_SECRET);
RC_JWT= "personal-jwt-token";
$ev->login([ 'jwt' => RC_JWT ], function($response){
  // call the get or post function
  ...
});

// Login with Legacy user credentials
$ev = new EngageVoiceSDKWrapper\RestClient();

USERNAME= "your-username";
PASSWORD= "your-password";

$ev->login([ 'username' => USERNAME, 'password' => PASSWORD ]) {
  // call get or post function
  ...
});
```

**Function get**
```
get($endpoint, $params, "callback");
```
*Description:*
* Send an HTTP GET request to Engage Voice server.

*Parameters:*
* $endpoint: Engage Voice API endpoint.
* $params: a JSON object containing key/value pair parameters to be sent to an Engage Voice API, where the keys are the query parameters of the API.
* "callback": the name of a callback function. If specified, response is returned via the callback function.

*Response:*
API response in JSON object

*Example code:*
```
# Read account info.

$endpoint = "admin/accounts";
try{
    $resp = $ev->get($endpoint);
    print ($resp);
}catch (Exception $e) {
    print $e->getMessage();
}
```

**Function post**
```
post($endpoint, $params, "callback");
```
*Description:*
* Sends an HTTP POST request to Engage Voice server.

*Parameters:*
* $endpoint: Engage Voice API
* $params: a JSON object containing key/value pair parameters to be sent to an Engage Voice API, where the keys are the body parameters of the API.
* "callback": the name of a callback function. If specified, response is returned via the callback function.

*Response:*
API response in JSON object

*Example code:*

```
# Search for campaign leads.

$endpoint = "admin/accounts/~/campaignLeads/leadSearch";
$params = array ( 'firstName' => "Larry" );
try{
    $resp = $ev->post($endpoint, $params);
    print ($resp);
}catch (Exception $e) {
    print $e->getMessage();
}
```
## License
Licensed under the MIT License.
