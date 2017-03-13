<?php

if (!function_exists('curl_reset'))
{
    function curl_reset(&$ch)
    {
        $ch = curl_init();
    }
}

// require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__ . '/google-api-php-client-2.1.1/vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfigFile('oauth-credentials.json');
$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/google-drive-test/oauth2callback.php');
$client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
$client->addScope(Google_Service_Drive::DRIVE_APPDATA);
$client->setIncludeGrantedScopes(true); 

if (! isset($_GET['code'])) {
  $auth_url = $client->createAuthUrl();
  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/google-drive-test/';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}