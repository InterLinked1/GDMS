<?php
/*
 * PHP SDK for GDMS (Grandstream Device Management System) API
 * 
 * Copyright (C) 2022, Naveen Albert
 *
 * GDMS (Grandstream Device Management System) is a trademark of Grandstream Networks, Inc.
 * This project is not in any way affiliated with Grandstream Networks, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/* Include the GDMS class */
require_once('GDMS.php');

/* TODO: Replace these with your information */
$apiID = 100555; /* 6-digit GDMS Developer API ID */
$secretKey = 'UYURsdf898fDF3e8f7dusf89dsgfgsdf'; /* 32-character GDMS secret key */
$username = 'jsmith'; /* Your GDMS account username */
$password = 'p@ssw0rd'; /* Your GDMS account password */



$passwordHash = hash("sha256", md5($password)); /* Don't be fooled, using a password hash is NOT more secure than just your password. Protect it. */

$gdms = new GDMS($apiID, $secretKey, $username, $passwordHash);
$gdms->setDebug(true);
$gdms->login();

/* Get organizations */
$resp = $gdms->organizationList();
print_r($resp['data']['result']);

/* Get sites in default organization */
$resp = $gdms->siteList();
print_r($resp['data']['result']);

/* Print all devices: print_r($resp['data']['result']); */
$resp = $gdms->deviceList();
echo "You have " . count($resp['data']['result']) . " devices\n";

/* Get account statuses */
$resp = $gdms->deviceAccountStatus('00AABBCCDDFF');
print_r($resp);

/* Get account configs */
$resp = $gdms->deviceAccountConfig('00AABBCCDDFF');
print_r($resp);

/* Add a device to GDMS */
/* Note: The MAC address can contain colons or not. Up to you. */
$resp = $gdms->addDevice('00AABBCCDDFF', '207GHQXG70CCDDFF', 12345, '', 45789);
print_r($resp);

/* View the current details (yes, using the editDevice function) */
$resp = $gdms->editDevice('00AABBCCDDFF', '207GHQXG70CCDDFF', 12345, '', 45789);
print_r($resp);

/* Reboot the ATA */
$resp = $gdms->deviceReboot('00AABBCCDDFF');
print_r($resp);
?>