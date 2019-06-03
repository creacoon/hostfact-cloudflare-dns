<?php

include './cloudflare/CloudflareConnection.php';
include 'TestVariables.php';

$error_emoticon = '⚠️';
$success_emotion = '☑️';

$cc = new CloudflareConnection($username, $api_key);

$user = $cc->currentUser();
echo($user['success'] !== true ? $error_emoticon : $success_emotion)."️ Get current user\n";
showErrors($user['errors']);

$zones = $cc->getZones([]);
echo($zones['success'] !== true ? $error_emoticon : $success_emotion)." Get zones\n";
showErrors($zones['errors']);

$dns_records = $cc->getDnsRecordsForZone($zones['result'][0]['id'], []);
echo($dns_records['success'] !== true ? $error_emoticon : $success_emotion)." Get DNS records\n";
showErrors($dns_records['errors']);

$zone_created = $cc->createZone($test_domain_creation_on, $account_id);
echo($zone_created['success'] !== true ? $error_emoticon : $success_emotion)." Create Zone\n";
showErrors($zone_created['errors']);

if ($zone_created['success']) {
    $zone_deleted = $cc->deleteZone($zone_created['result']['id']);
    echo($zone_deleted['success'] !== true ? $error_emoticon : $success_emotion)." Delete Zone\n";
    showErrors($zone_deleted['errors']);
}

function showErrors(array $errors)
{
    foreach ($errors as $error) {
        echo '* '.$error['code'].' : '.$error['message']."\r\n";
    }
}
