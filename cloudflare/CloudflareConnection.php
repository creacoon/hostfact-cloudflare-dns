<?php

class CloudflareConnection
{
    private $username;
    private $api_key;
    private $host;
    private $debug;

    /**
     * CloudflareConnection constructor.
     *
     * @param string $username
     * @param string $apiKey
     */
    public function __construct(string $username, string $apiKey)
    {
        $this->username = $username;
        $this->api_key = $apiKey;
        $this->host = 'https://api.cloudflare.com/client/v4/';

        $this->debug = false;
    }

    /**
     * @return bool
     */
    public function validateLogin(): bool
    {
        $user = $this->currentUser();

        return $user && $user['success'] === true;
    }

    /**
     * @return array
     */
    public function currentUser(): ?array
    {
        return $this->apiCall('user');
    }

    /**
     * @return array|null
     */
    public function current(): ?array
    {
        return $this->apiCall('user');
    }

    /**
     * @param string $id
     * @param array  $parameters
     *
     * @return array|null
     */
    public function getDnsRecordsForZone(string $id, array $parameters): ?array
    {
        $parameters['per_page'] = 100;
        $query_string = http_build_query($parameters);

        return $this->apiCall('zones/'.$id.'/dns_records?'.$query_string);
    }

    /**
     * @param array $parameters
     *
     * @return array|null
     */
    public function getZones(array $parameters): ?array
    {
        return $this->apiCall('zones?'.http_build_query($parameters));
    }

    /**
     * @param string $domain
     *
     * @return array|null
     */
    public function createZone(string $domain, string $accountId): ?array
    {
        $data = [
            'name'       => $domain,
            'account'    => ['id' => $accountId],
            'jump_start' => true,
            'type'       => 'full',
        ];

        return $this->apiCall('zones', 'POST', $data);
    }

    /**
     * @param string $zoneId
     *
     * @return array|null
     */
    public function deleteZone(string $zoneId): ?array
    {
        return $this->apiCall('zones/'.$zoneId, 'DELETE');
    }

    /**
     * @param string $zoneId
     * @param string $id
     * @param array  $data
     *
     * @return array|null
     */
    public function createDnsRecord(string $zoneId, array $data): ?array
    {
        return $this->apiCall('zones/'.$zoneId.'/dns_records', 'POST', $data);
    }

    /**
     * @param string $zoneId
     * @param string $id
     * @param array  $data
     *
     * @return array|null
     */
    public function updateDnsRecord(string $zoneId, string $id, array $data): ?array
    {
        return $this->apiCall('zones/'.$zoneId.'/dns_records/'.$id, 'PUT', $data);
    }

    /**
     * @param string $zoneId
     * @param string $id
     *
     * @return array|null
     */
    public function deleteDnsRecord(string $zoneId, string $id): ?array
    {
        return $this->apiCall('zones/'.$zoneId.'/dns_records/'.$id, 'DELETE');
    }

    /**
     * @param string $path
     * @param string $method
     * @param array  $data
     *
     * @return array|null
     */
    private function apiCall(string $path, string $method = 'GET', array $data = []): ?array
    {
        $ch = curl_init();

        if (false === $ch) {
            return null;
        }

        $request_headers = [
            'X-Auth-Email: '.$this->username,
            'X-Auth-Key: '.$this->api_key,
            'Content-Type: application/json',
        ];

        if (in_array($method, ['PUT', 'POST'])) {
            $json_data = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

            $request_headers[] = 'Content-Length: '.strlen($json_data);
        }

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        curl_setopt($ch, CURLOPT_URL, $this->host.$path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        if (curl_errno($ch)) {
            return null;
        }

        $response = curl_exec($ch);

        if ($this->debug) {
            $info = curl_getinfo($ch);
            echo $info['url'];
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}
