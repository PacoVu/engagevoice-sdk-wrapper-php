# Engage Voice SDK Wrapper for PHP.

----
## Overview
Engage Voice SDK Wrapper for PHP is a utility class, which helps you easily integrate your PHP project with RingCentral Engage Voice Services.

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
* Creates and initializes an EngageVoice SDK wrapper object.

*Parameters:*
* $clientId: set the `clientId` of a RingCentral app to enable login with RingCentral user credentials.
* $clientSecret: set the `clientSecret` of a RingCentral app to enable login with RingCentral user credentials.

*Example code:*
```
require('vendor/autoload.php');

$ev = new EngageVoiceSDKWrapper\RestClient(RINGCENTRAL_CLIENT_ID, RINGCENTRAL_CLIENT_SECRET)
```
----
**Function login**
    login($username, $password, $extensionNumber)

*Description:*
* Login using a user's credential. If the mode was set "Engage", the username and password must be the valid username and password of a RingCentral Office user.

*Parameters:*
* $username: username of a user in Legacy service or in RingCentral Office service.
* $password: password of a user in Legacy service or in RingCentral Office service.
* $extensionNumber: an extension number of a user. To be used if the `username` is a RingCentral company main number.

*Response:*


*Example code:*
```
// Login with RingCentral Office user credentials.
require('vendor/autoload.php');

$ev = new EngageVoiceSDKWrapper\RestClient(RC_CLIENT_ID, RC_CLIENT_SECRET);

$ev->login(RC_USERNAME, RC_PASSWORD, RC_EXTENSION, function($response){

// Login with Legacy user credentials

$ev = new EngageVoiceSDKWrapper\RestClient(null, null, "Legacy");

$ev->login(USERNAME, PASSWORD);
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
* "callback": the name of a callback function. If specified, response is returned to callback function.

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
* "callback": the name of a callback function. If specified, response is returned to callback function.

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
