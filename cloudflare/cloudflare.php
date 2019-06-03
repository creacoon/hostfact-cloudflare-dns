<?php

include 'CloudflareConnection.php';

class cloudflare
{
    public $Error;
    public $Warning;
    public $Success;
    
	public function __construct()
	{
		$this->Error    = array();
		$this->Warning  = array();
		$this->Success  = array();

		$this->loadLanguageArray(LANGUAGE_CODE);
	}

	/** Settings for the integration, these are shown when you add/edit a DNS integration
	 *
	 * @return string
	 */
	public function getPlatformSettings()
	{
		// example of html for the integration settings
		$html = '';
		$html .= '<strong class="title">' . __('username', 'cloudflare') . '</strong>';
		$html .= '<input type="text" class="text1 size1" name="module[dnsmanagement][Settings][username]" value="' . ((isset($this->Settings->username)) ? htmlspecialchars($this->Settings->username) : '') . '"><br><br>';
		$html .= '<strong class="title">' . __('api_key', 'cloudflare') . '</strong>';
		$html .= '<input type="text" class="text1 size1" name="module[dnsmanagement][Settings][api_key]" value="' . ((isset($this->Settings->api_key)) ? htmlspecialchars($this->Settings->api_key) : '') . '"><br><br>';
		$html .= '<strong class="title">' . __('account_id', 'cloudflare') . '</strong>';
		$html .= '<input type="text" class="text1 size1" name="module[dnsmanagement][Settings][account_id]" value="' . ((isset($this->Settings->account_id)) ? htmlspecialchars($this->Settings->account_id) : '') . '"><br><br>';
		$html .= '<strong class="title">' . __('allow_zone_removal', 'cloudflare') . '</strong>';
		$html .= '<select class="text1 size4" name="module[dnsmanagement][Settings][allow_zone_removal]">
            <option value="no" ' . ($this->Settings->allow_zone_removal === 'no' ? 'selected' : '') . '>' . __('no', 'cloudflare') . '</option>
            <option value="yes" ' . ($this->Settings->allow_zone_removal === 'yes' ? 'selected' : '') . '>' . __('yes', 'cloudflare') . '</option>
          </select/><br><br>';

		return $html;
	}

	/** Get the DNS templates from the DNS platform
	 *
	 * @return array|bool
	 */
	public function getDNSTemplates()
    {
    	// if the DNS platform does not support DNS templates by it self, just do a:
		return false;
		// the WeFact Hosting user then has to create the DNS templates with each DNS record in WeFact Hosting
    }

	/** This function is called before a add/edit/show of a DNS integration
	 *  For example, you can use this to encrypt a password
	 *
	 * @param $edit_or_show
	 * @param $settings
	 * @return mixed
	 */
	public function processSettings($edit_or_show, $settings)
	{
		return $settings;
	}

	/** Create a DNS zone with DNS records on the DNS platform
	 *
	 * @param $domain
	 * @param $dns_zone
	 * @return bool
	 */
	public function createDNSZone($domain, $dns_zone)
    {
        $cloudflare_connection = new CloudflareConnection($this->Settings->username, $this->Settings->api_key);

        $zone = $cloudflare_connection->createZone($domain, $this->Settings->account_id);

        if ($zone['result'] === success) {
            return $this->saveDNSZone($domain, $dns_zone);
        }

        return false;
    }

	/** This function will be called when a domain register, transfer or nameserver change has failed
	 *  It can be used to revert any data that is set by the createDNSZone function (eg the creation of a DNS zone)
	 *
	 * @param $domain
	 * @param $create_dns_zone_data
	 * @return bool
	 */
	public function undoCreateDNSZone($domain, $create_dns_zone_data)
	{
		return false;
	}

	/** Retrieve the DNS zone with its DNS records from the DNS platform
	 *
	 * @param $domain
	 * @return array|bool
	 */
	public function getDNSZone($domain)
    {
        $cloudflare_connection = new CloudflareConnection($this->Settings->username, $this->Settings->api_key);
        $zones = $cloudflare_connection->getZones(['name' => $domain]);

        if ($zones['success'] === true && count($zones['result']) === 1) {
            $zone = $zones['result'][0];
            $dns_records = $cloudflare_connection->getDnsRecordsForZone($zone['id'], []);

            if ($dns_records['success'] !== true) {
                return false;
            }

            $dns_zone = [];
			$i = 0;
			foreach($dns_records['result'] as $record) {
				// if the record is not supported, it should be marked as readonly
				if (!in_array(strtoupper($record['type']), $this->SupportedRecordTypes, true)) {
					$record_type = 'records_readonly';
				} else {
					$record_type = 'records';
				}

				$dns_zone[$record_type][$i]['id']          = $record['id'];
				$dns_zone[$record_type][$i]['name']        = $record['name'];
				$dns_zone[$record_type][$i]['type']        = $record['type'];
				$dns_zone[$record_type][$i]['value']       = $record['content'];
				$dns_zone[$record_type][$i]['priority']    = $record['priority'] ?? '';
				$dns_zone[$record_type][$i]['ttl']         = $record['ttl'];
				$i++;
			}

			return $dns_zone;
        }

		return false;
    }

	/** Edit the DNS zone at the DNS platform
	 *
	 * @param $domain
	 * @param $dns_zone
	 * @return bool
	 */
	public function saveDNSZone($domain, $dns_zone)
    {
        if (isset($dns_zone['records']) && count($dns_zone['records']) > 0) {
            $cloudflare_connection = new CloudflareConnection($this->Settings->username, $this->Settings->api_key);
            $zones = $cloudflare_connection->getZones(['name' => $domain]);

            if ($zones['success'] === true && count($zones['result']) === 1) {
                $zone = $zones['result'][0];
                $dns_records = $cloudflare_connection->getDnsRecordsForZone($zone['id'], []);

                if ($dns_records['success'] !== true) {
                    return false;
                }

                $cf_record_ids = array_column($dns_records['result'], 'id');
                $updated_record_ids = array_column($dns_zone['records'], 'id');
                $deleted_record_ids = array_diff($cf_record_ids, $updated_record_ids);

                foreach ($deleted_record_ids as $id) {
                    $cloudflare_connection->deleteDnsRecord($zone['id'], $id);
                }

                foreach($dns_zone['records'] as $record) {
                    $cf_record['name'] = $record['name'];
                    $cf_record['type'] = $record['type'];
                    $cf_record['content'] = $record['value'];
                    $cf_record['ttl'] = $record['ttl'];
                    $cf_record['priority'] = trim($record['priority']) === '' ? 1 : $record['priority'];

                    if (isset($record['id'])) {
                        $cloudflare_connection->updateDnsRecord($zone['id'], $record['id'], $cf_record);
                    } else {
                        $cloudflare_connection->createDnsRecord($zone['id'], $cf_record);
                    }
                }
            }
        }

        return true;
    }

	/** This function is called when a domain is removed directly or by a termination procedure
	 *
	 * @param $domain
	 * @return bool
	 */
	public function removeDNSZone($domain)
    {
        if ($this->Settings->allow_zone_removal === 'yes') {
            $cloudflare_connection = new CloudflareConnection($this->Settings->username, $this->Settings->api_key);

            $zones = $cloudflare_connection->getZones(['name' => $domain]);

            if ($zones['success'] === true && count($zones['result']) === 1) {
                $zone = $zones['result'][0];
                $zone_delete_result = $cloudflare_connection->deleteZone($zone['id']);

                return $zone_delete_result['success'];
            }

            return false;
        }

        return true;
    }

	/**
	 * @param $language_code
	 */
	public function loadLanguageArray($language_code)
	{
		$_LANG = array();

		switch($language_code)
		{
			case 'nl_NL':
				$_LANG['dns templates could not be retrieved'] = 'De DNS templates konden niet worden opgehaald van het DNS platform of er zijn er geen aanwezig';
				$_LANG['username'] = 'Gebruikersnaam';
				$_LANG['api_key'] = 'API Key';
				$_LANG['account_id'] = 'Account ID';
				$_LANG['allow_zone_removal'] = 'DNS zone verwijderen bij verwijderen domein';
				$_LANG['yes'] = 'Ja';
				$_LANG['no'] = 'Nee';
			break;
            
			default: // In case of other language, use English
				$_LANG['dns templates could not be retrieved'] = 'DNS template could not be retrieved from the DNS platform or there were no DNS templates';
				$_LANG['username'] = 'Username';
				$_LANG['api_key'] = 'API Key';
				$_LANG['account_id'] = 'Account ID';
				$_LANG['allow_zone_removal'] = 'Remove DNS zone at domain deletion';
				$_LANG['yes'] = 'Ja';
				$_LANG['no'] = 'Nee';
			break;
		}
		
		// Save to global array
		global $_module_language_array;
		$_module_language_array['cloudflare'] = $_LANG;
	}

	/**
	 * Use this function to prefix all errors messages with your VPS platform
	 *
	 * @param 	string	 $message	The error message
	 * @return 	boolean 			Always false
	 */
	private function __parseError($message)
	{
		$this->Error[] = 'Cloudflare: ' . $message;
		return false;
	}

	/** This function is used to check if the login credentials for the DNS platform are correct
	 *
	 * @return bool
	 */
	public function validateLogin()
    {
        $cloudflare_connection = new CloudflareConnection($this->Settings->username, $this->Settings->api_key);

        return $cloudflare_connection->validateLogin();
    }
}
