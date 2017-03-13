<?php

//require __DIR__ . '/vendor/autoload.php'; // Google Drive API
require_once __DIR__ . '/google-api-php-client-2.1.1/vendor/autoload.php';


// HTTPS Authentication
$masterToken = getMasterTokenForAccount("mail", "pwd");
// $appSignature = '38a0f7d505fe18fec64fbf343ecaaaf310dbd799';
$appSignature = '01d845b26b688d8ef647205a5944e9407e52e06e';
// $appID = 'com.whatsapp';
$appID = 'com.lge.qmemoplus';
$accessToken = getGoogleDriveAccessToken($masterToken, $appID, $appSignature);

if ($accessToken === false) return;

// Initializing the Google Drive Client
$client = new Google_Client();
$client->setAccessToken($accessToken);
$client->addScope(Google_Service_Drive::DRIVE_APPDATA);
$client->addScope(Google_Service_Drive::DRIVE_FILE);
$client->setClientId("");    // client id and client secret can be left blank
$client->setClientSecret(""); // because we're faking an android client
$service = new Google_Service_Drive($client);

// Print the names and IDs for up to 10 files.
$optParams = array(
    'spaces' => 'appDataFolder',
    'fields' => 'nextPageToken, files(id, name)',
    'pageSize' => 10
);
$results = $service->files->listFiles($optParams);

if (count($results->getFiles()) == 0) 
{
    print "No files found.\n";
} 
else 
{
    print "Files:<br>";
    foreach ($results->getFiles() as $file) 
    {
        print $file->getName() . " (" . $file->getId() . ")" . '<br>';
    }
}

/*
$fileId = '1kTFG5TmgIGTPJuVynWfhkXxLPgz32QnPJCe5jxL8dTn0';
$content = $service->files->get($fileId, array('alt' => 'media' ));
echo var_dump($content);
*/

function getGoogleDriveAccessToken($masterToken, $appIdentifier, $appSignature)
{
    if ($masterToken === false) return false;

    $url = 'https://android.clients.google.com/auth';
    $deviceID = '0000000000000000';
    $requestedService = 'oauth2:https://www.googleapis.com/auth/drive.appdata https://www.googleapis.com/auth/drive.file';
    $data = array('Token' => $masterToken, 'app' => $appIdentifier, 'client_sig' => $appSignature, 'device' => $deviceID, 'google_play_services_version' => '8703000', 'service' => $requestedService, 'has_permission' => '1');

    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\nConnection: close",
            'method' => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => TRUE,
            'protocol_version'=>'1.1',
             //'proxy' => 'tcp://127.0.0.1:8080', // optional proxy for debugging
             //'request_fulluri' => true
        ),
        'ssl' => array(
            'verify_peer' => true, // You could skip all of the trouble by changing this to false, but it's WAY uncool for security reasons.
            'cafile' => 'C:\xampp\cacert.pem',
            //'CN_match' => 'example.com', // Change this to your certificates Common Name (or just comment this line out if not needed)
            'ciphers' => 'HIGH:!SSLv2:!SSLv3',
            'disable_compression' => true,
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if (strpos($http_response_header[0], '200 OK') === false) 
    { 
        /* Handle error */
        print 'An error occured while requesting an access token: ' . $result . "\r\n";
        return false;
    }

    $startsAt = strpos($result, "Auth=") + strlen("Auth=");
    $endsAt = strpos($result, "\n", $startsAt);
    $accessToken = substr($result, $startsAt, $endsAt - $startsAt);

    return "{\"access_token\":\"" . $accessToken . "\", \"refresh_token\":\"TOKEN\", \"token_type\":\"Bearer\", \"expires_in\":360000, \"id_token\":\"TOKEN\", \"created\":" . time() . "}";
}

function getMasterTokenForAccount($email, $password) 
{
    $url = 'https://android.clients.google.com/auth';
    $deviceID = '0000000000000000';
    $data = array('Email' => $email, 'Passwd' => $password, 'app' => 'com.google.android.gms', 'client_sig' => '38918a453d07199354f8b19af05ec6562ced5788', 'parentAndroidId' => $deviceID);

    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\nConnection: close",
            'method' => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => TRUE,
            'protocol_version'=>'1.1',
             //'proxy' => 'tcp://127.0.0.1:8080', // optional proxy for debugging
             //'request_fulluri' => true
        ),
        'ssl' => array(
            'verify_peer' => true, // You could skip all of the trouble by changing this to false, but it's WAY uncool for security reasons.
            'cafile' => 'C:\xampp\cacert.pem',
            //'CN_match' => 'example.com', // Change this to your certificates Common Name (or just comment this line out if not needed)
            'ciphers' => 'HIGH:!SSLv2:!SSLv3',
            'disable_compression' => true,
        )

    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if (strpos($http_response_header[0], '200 OK') === false) 
    { 
        /* Handle error */
        print 'An error occured while trying to log in: ' . $result . "\r\n";
        return false;
    }

    $startsAt = strpos($result, "Token=") + strlen("Token=");
    $endsAt = strpos($result, "\n", $startsAt);
    $token = substr($result, $startsAt, $endsAt - $startsAt);

    return $token;
}