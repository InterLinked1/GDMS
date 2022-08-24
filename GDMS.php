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

class GDMS {

	/* Client parameters */
	private $username;
	private $password;
	private $apiID;
	private $secretKey;

	/* Login parameter */
	private $accessToken;

	private $debug;

	/* API parameters */
	private $domain;
	private $version;

	/*!
	 * \param apiID Also "Client ID" from GDMS portal
	 * \param secretKey from GDMS portal
	 * \param username GDMS username (not email address)
	 * \param password SHA256(MD5(GDMS account password))
	 * \param eu = European Union region (default: false - US region)
	 */
	public function __construct(int $apiID, String $secretKey, String $username, String $password, bool $eu = false) {
		$this->domain = $eu ? "eu.gdms.cloud" : "www.gdms.cloud";
		$this->version = '1.0.0';
		$this->username = $username;
		$this->password = $password;
		$this->apiID = $apiID;
		$this->secretKey = $secretKey;
		$this->accessToken = null;
		$this->debug = false;
	}

	/*! \brief Enable SDK debugging */
	public function setDebug(bool $enabled) {
		$this->debug = $enabled;
	}

	private function debug(String $str) {
		if ($this->debug) {
			fprintf(STDERR, "%s\n", $str);
		}
	}

	/*! \brief Perform a GET request */
	private static function get(String $url) {
		$ch = curl_init();
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json"
			)
		);
		curl_setopt_array($ch, $options);
		$response = curl_exec($ch);
		if (curl_error($ch)) {
			echo curl_error($ch);
			curl_close($ch);
		} else {
			curl_close($ch);
			$response = json_decode($response, true);
			return $response;
		}
		return NULL;
	}

	/*! \brief Calculate the Grandstream timestamp API parameter */
	private static function timestamp() : int {
		return round(microtime(true) * 1000);
	}

	/*! \brief Calculate the Grandstream signature API parameter */
	private function signature(int $timestamp, String $body = "") : String {
		/* parameters must be alphabetically ordered */
		$str = "&access_token=" . $this->accessToken . "&client_id=" . $this->apiID . "&client_secret=" . $this->secretKey . "&timestamp=" . $timestamp . "&";
		if (strlen($body) > 0) {
			$bodyHash = hash("sha256", $body);
			$str = $str . $bodyHash . "&";
		}
		$hash = hash("sha256", $str);
        return $hash;
	}

	/*! \brief The basic API parameters that must be included in most "normal" requests (besides login, etc.) */
	private function basicParams(String $body) : String {
		$timestamp = static::timestamp();
		$signature = $this->signature($timestamp, $body);
		/* parameters must be alphabetically ordered */
		return "access_token=" . $this->accessToken . "&signature=$signature&timestamp=$timestamp";
	}

	/*! \brief Perform a POST request */
	private static function post(String $endpoint, array $params = array()) {
		$json = null;
		if (!empty($params)) {
			$json = json_encode($params);
		}
		$ch = curl_init();
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $json,
			CURLOPT_URL => $endpoint,
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json"
			)
		);
		curl_setopt_array($ch, $options);
		$response = curl_exec($ch);
		if (curl_error($ch)) {
			echo curl_error($ch);
			curl_close($ch);
		} else {
			curl_close($ch);
			$response = json_decode($response, true);
			return $response;
		}
		return NULL;
	}

	private function gsPost(String $endpoint, array $params = array()) {
		$body = '';
		if (!empty($params)) {
			$body = json_encode($params);
		}
		$url = 'https://' . $this->domain . '/oapi/v' . $this->version . '/' . $endpoint . '?' . $this->basicParams($body);
		$this->debug("POST: $url\r\n$body");
		return static::post($url, $params);
	}

	/*! \note Used for login */
	private function gsGet0(String $endpoint, array $params) {
		$body = http_build_query($params);
		$url = 'https://' . $this->domain . '/oapi/' . $endpoint . '?' . $body;
		$this->debug("GET: $url");
		return static::get($url);
	}

	private function gsGet(String $endpoint, array $params = array()) {
		$body = http_build_query($params); /* https://doc.grandstream.dev/GDMS-API/EN/#api-157061470296601000002 */
		$url = 'https://' . $this->domain . '/oapi/' . 'v' . $this->version . '/' . $endpoint . '?' . $this->basicParams($body);
		if (strlen($body) > 0) {
			$url .= "&" . $body;
		}
		$this->debug("GET: $url");
		$res = static::get($url);
		return $res;
	}

	/*!
	 * \brief Log in to the Grandstream API
	 * \retval Return -1 on failure, 0 on success
	 */
	public function login() {
		$params = array();
		$params['grant_type'] = 'password';
		$params['password'] = $this->password;
		$params['username'] = $this->username;
		$params['client_id'] = $this->apiID;
		$params['client_secret'] = $this->secretKey;
		$res = $this->gsGet0('oauth/token', $params);
		if ($res) {
			$this->accessToken = $res['access_token'];
			$this->debug("Logged in successfully");
			return 0;
		}
		return -1;
	}

	/*! \brief List of organizations */
	public function organizationList() {
		return $this->gsGet('org/list');
	}

	/*! \brief List of sites
	 * \param orgID Organization ID. Optional, but if provided, return only sites in this organization. Default is the default org (not all orgs)
	 */
	public function siteList(int $orgID = 0) {
		$params = array();
		if ($orgID > 0) {
			$params['orgId'] = $orgID;
		}
		return $this->gsPost('site/list', $params);
	}

	/*! \brief List of devices */
	public function deviceList() {
		return $this->gsPost('device/list');
	}

	/*! \brief Add a single device to GDMS
	 * \param mac MAC address (with or without colons)
	 * \param sn Serial Number
	 * \param siteID Site ID
	 * \param deviceName Device name (optional)
	 * \param orgID Organization ID (optional)
	 */
	public function addDevice(String $mac, String $sn, int $siteID, String $deviceName = '', int $orgID = 0) {
		$params = array();
		$params[0] = array(); /* Unlike most others, add accepts an array */
		$params[0]['mac'] = $mac;
		$params[0]['sn'] = $sn;
		$params[0]['siteId'] = $siteID;
		if (strlen($deviceName) > 0) {
			$params[0]['deviceName'] = $deviceName;
		}
		if ($orgID > 0) {
			$params[0]['orgId'] = $orgID;
		}
		return $this->gsPost('device/add', $params);
	}

	/*! \brief Edit or view current details of single device in GDMS
	 * \param mac MAC address (with or without colons)
	 * \param sn Serial Number
	 * \param siteID Site ID
	 * \param deviceName Device name (optional)
	 * \param orgID Organization ID (optional)
	 * \note Even though this is for editing, this is the best way to view details of a device, including those that are offline
	 */
	public function editDevice(String $mac, String $sn, int $siteID, String $deviceName = '', int $orgID = 0) {
		$params = array();
		$params['mac'] = $mac;
		$params['sn'] = $sn;
		$params['siteId'] = $siteID;
		if (strlen($deviceName) > 0) {
			$params['deviceName'] = $deviceName;
		}
		if ($orgID > 0) {
			$params['orgId'] = $orgID;
		}
		return $this->gsPost('device/edit', $params);
	}

	/*!
	 * \brief Get device details
	 * \param isFirst Whether to request device details for first time (1 = submit request, 0 = retrieve info, within 1 minute of first request)
	 * \note This only works for devices that are currently online
	 */
	public function deviceDetails(String $mac, bool $isFirst) {
		$params = array();
		$params['mac'] = $mac;
		$params['isFirst'] = $isFirst ? 1 : 0;
		return $this->gsPost('device/detail', $params);
	}

	/*! \brief Get device account statuses */
	public function deviceAccountStatus(String $mac) {
		$params = array();
		$params['mac'] = $mac;
		return $this->gsPost('device/account/status', $params);
	}

	/*! \brief Get device account configs */
	public function deviceAccountConfig(String $mac) {
		$params = array();
		$params['mac'] = $mac;
		return $this->gsPost('device/account/info', $params);
	}

	/*! \brief Add task */
	private function addTask(String $taskName, int $taskType, String $mac, int $execType, String $fwDownloadURL = '', int $orgID = 0) {
		$params = array();
		$params['taskName'] = $taskName;
		$params['taskType'] = $taskType;
		$params['macList'] = array($mac);
		$params['execType'] = $execType;
		if (strlen($fwDownloadURL) > 0) {
			$params['firmwareDownloadUrl'] = $fwDownloadURL;
		}
		if ($orgID > 0) {
			$params[0]['orgId'] = $orgID;
		}
		return $this->gsPost('task/add', $params);
	}

	/*! \brief Reboot device */
	public function deviceReboot(String $mac, int $orgID = 0) {
		$uniqueTaskName = static::timestamp() . "_" . $mac . "_Reboot";
		return $this->addTask($uniqueTaskName, 1, $mac, 1, '', $orgID);
	}

	/*! \brief Factory reset device */
	public function deviceFactoryReset(String $mac, int $orgID = 0) {
		$uniqueTaskName = static::timestamp() . "_" . $mac . "_Reset";
		return $this->addTask($uniqueTaskName, 2, $mac, 1, '', $orgID);
	}
}
?>