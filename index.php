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
$client->setAuthConfig('oauth-credentials.json');
$client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
$client->addScope(Google_Service_Drive::DRIVE_APPDATA);
$client->setIncludeGrantedScopes(true); 

// unset($_SESSION['access_token']); die;

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
  $drive = new Google_Service_Drive($client);
  // No va mas
  // $files = $drive->files->listFiles(array())->getItems();
  
  
  $files = $drive->files->listFiles(array(
		'spaces' => 'appDataFolder',
		'fields' => 'nextPageToken, files(id, name)',
		'pageSize' => 10
  ))->getFiles();
  
  echo "<pre>" . print_r($files, true) . "</pre>";
  
  
} else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/google-drive-test/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}