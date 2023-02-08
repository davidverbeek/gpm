<?php
require_once("../../vendor/autoload.php");

function getAttachment($messageId, $partId, $userId)
    {
        try {
            $client = getClient();
            $gmail = new Google_Service_Gmail($client);
            $message = $gmail->users_messages->get($userId, $messageId);
            $message_payload_details = $message->getPayload()->getParts();
            $attachmentDetails = array();
            $attachmentDetails['attachmentId'] = $message_payload_details[$partId]['body']['attachmentId'];
            $attachmentDetails['headers'] = $message_payload_details[$partId]['headers'];
            $attachment = $gmail->users_messages_attachments->get($userId, $messageId, $attachmentDetails['attachmentId']);
            $attachmentDetails['data'] = $attachment->data;
            return ['status' => true, 'data' => $attachmentDetails];
        } catch (\Google_Service_Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }


    function base64_to_jpeg($base64_string, $content_type) {
        $find = ["_","-"]; $replace = ["/","+"];
        $base64_string = str_replace($find,$replace,$base64_string);
        $url_str = 'data:'.$content_type.','.$base64_string;
        $base64_string = "url(".$url_str.")";
        $data = explode(',', $base64_string);
        return base64_decode( $data[ 1 ] );
    }

function getClient() {
    $client = new Google_Client();
    $client->setApplicationName('Web client jyoti');
    $client->setScopes('https://www.googleapis.com/auth/gmail.readonly');
    $client->setAuthConfig('client_secret.json');   
    $client->setRedirectUri ("http://gpmlocal.com/reports/gyzsmanagement/read_mail.php");
    $client->setApprovalPrompt('force');
    $client->setAccessType('offline');
    $tokenPath = __DIR__ . '/token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);

        if ($client->isAccessTokenExpired()) {
            $new_token = $client->fetchAccessTokenWithRefreshToken($accessToken);            
            $_SESSION['access_token'] = $client->getAccessToken();
            file_put_contents($tokenPath, json_encode($_SESSION['access_token']));
           // $_SESSION['refresh_token'] = $client->getRefreshToken();
        }
    } else {
        if (! isset($_GET['code'])) {
            $redirect = $client->createAuthUrl();
            header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
          } else {           
            $client->authenticate($_GET['code']);
            $_SESSION['access_token'] = $client->getAccessToken();
            $_SESSION['refresh_token'] = $client->getRefreshToken();            
          }
         
    }

/*
            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
       */
    return $client;
}

/**
* Expands the home directory alias '~' to the full path.
* @param string $path the path to expand.
* @return string the expanded path.
*/
/* function expandHomeDirectory($path) {
    $homeDirectory = getenv('HOME');
    if (empty($homeDirectory)) {
        $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
    }
    return str_replace('~', realpath($homeDirectory), $path);
} */

// Get the API client and construct the service object.
$client = getClient();
$gmail = new Google_Service_Gmail($client);

$optParams = [];
$optParams['maxResults'] = 100;
$optParams['labelIds'] = 'INBOX'; // Only show messages in Inbox
//$optParams['pageToken'] = '1';

$optParams['q'] = "has:attachment larger_than:3000000";
$optParams['q'] = "subject:Price list ".date('d-m-Y');
$messages = $gmail->users_messages->listUsersMessages('me',$optParams);

foreach ($messages as $message_thread) {
    $message = $gmail->users_messages->get('me', $message_thread['id']);
    $message_parts = $message->getPayload()->getParts();
    $files = array();
    $attachId = $message_parts[1]['body']['attachmentId'];
    $attach = $gmail->users_messages_attachments->get('me', $message['id'], $attachId);
    foreach ($message_parts as $key => $value) {
        if ( isset($value->body->attachmentId) && !isset($value->body->data)) {
          array_push($files, $value['partId']);
        }
    }   
}

if(isset($_GET['messageId']) && $_GET['part_id']) { // This is After Clicking an Attachment
    $attachment = getAttachment($_GET['messageId'], $_GET['part_id'], 'me');
    $content_type = "";
    foreach ($attachment['data']['headers'] as $key => $value) {
        if($value->name == 'Content-Type'){ $content_type = $value->value; }
        header($value->name.':'.$value->value);
    }
    $content_type_val = current(explode("/",$content_type));
    $media_types = ["video", "image", "application"];
    if(in_array($content_type_val, $media_types )){
        echo base64_to_jpeg($attachment['data']['data'], $content_type); // Only for Image files
    } else {
      echo base64_decode($attachment['data']['data']); // Other than Image Files
    }
} else { // Listing All Attachments
        if(!empty($files)) {
            foreach ($files as $key => $value) {
                echo '<a target="_blank" href="gmail_api_tuesday.php?messageId='.$message['id'].'&part_id='.$value.'">Attachment '.($key+1).'</a><br/>';
            }
        }
}