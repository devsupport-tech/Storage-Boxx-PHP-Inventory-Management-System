<?php
/**
 * Supabase Integration Module
 * Provides Supabase client functionality for Storage Boxx
 */

class Supabase {
    private $url;
    private $anonKey;
    private $serviceRoleKey;
    private $projectId;
    private $headers;

    public function __construct() {
        $this->url = $_ENV['SUPABASE_URL'] ?? '';
        $this->anonKey = $_ENV['SUPABASE_ANON_KEY'] ?? '';
        $this->serviceRoleKey = $_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? '';
        $this->projectId = $_ENV['SUPABASE_PROJECT_ID'] ?? '';
        
        $this->headers = [
            'Content-Type: application/json',
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . $this->anonKey
        ];
    }

    /**
     * Execute a REST API request to Supabase
     */
    private function request($method, $endpoint, $data = null, $useServiceRole = false) {
        $url = $this->url . '/rest/v1/' . $endpoint;
        
        $headers = $this->headers;
        if ($useServiceRole) {
            $headers = array_map(function($header) {
                if (strpos($header, 'apikey:') === 0) {
                    return 'apikey: ' . $this->serviceRoleKey;
                }
                if (strpos($header, 'Authorization:') === 0) {
                    return 'Authorization: Bearer ' . $this->serviceRoleKey;
                }
                return $header;
            }, $headers);
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            throw new Exception("Supabase request error: " . $error);
        }

        $result = json_decode($response, true);
        
        if ($httpCode >= 400) {
            throw new Exception("Supabase API error: " . ($result['message'] ?? 'Unknown error'));
        }

        return $result;
    }

    /**
     * Select data from a table
     */
    public function select($table, $columns = '*', $filters = [], $useServiceRole = false) {
        $endpoint = $table . '?select=' . $columns;
        
        foreach ($filters as $key => $value) {
            $endpoint .= '&' . $key . '=eq.' . urlencode($value);
        }

        return $this->request('GET', $endpoint, null, $useServiceRole);
    }

    /**
     * Insert data into a table
     */
    public function insert($table, $data, $useServiceRole = false) {
        return $this->request('POST', $table, $data, $useServiceRole);
    }

    /**
     * Update data in a table
     */
    public function update($table, $data, $filters = [], $useServiceRole = false) {
        $endpoint = $table;
        
        if (!empty($filters)) {
            $endpoint .= '?';
            $filterParts = [];
            foreach ($filters as $key => $value) {
                $filterParts[] = $key . '=eq.' . urlencode($value);
            }
            $endpoint .= implode('&', $filterParts);
        }

        return $this->request('PATCH', $endpoint, $data, $useServiceRole);
    }

    /**
     * Delete data from a table
     */
    public function delete($table, $filters = [], $useServiceRole = false) {
        $endpoint = $table;
        
        if (!empty($filters)) {
            $endpoint .= '?';
            $filterParts = [];
            foreach ($filters as $key => $value) {
                $filterParts[] = $key . '=eq.' . urlencode($value);
            }
            $endpoint .= implode('&', $filterParts);
        }

        return $this->request('DELETE', $endpoint, null, $useServiceRole);
    }

    /**
     * Execute raw SQL query (requires service role)
     */
    public function rpc($functionName, $params = [], $useServiceRole = true) {
        return $this->request('POST', 'rpc/' . $functionName, $params, $useServiceRole);
    }

    /**
     * Get project information
     */
    public function getProjectInfo() {
        return [
            'url' => $this->url,
            'project_id' => $this->projectId,
            'connected' => !empty($this->url) && !empty($this->anonKey)
        ];
    }

    /**
     * Test connection to Supabase
     */
    public function testConnection() {
        try {
            // Try to access the health endpoint
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->url . '/rest/v1/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['apikey: ' . $this->anonKey],
                CURLOPT_TIMEOUT => 10
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            return $httpCode === 200;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Initialize database schema (if needed)
     */
    public function initializeSchema() {
        // This would typically run SQL migrations
        // For now, just test the connection
        return $this->testConnection();
    }
}

// Auto-instantiate if loaded via Core framework
if (isset($_CORE)) {
    $_CORE->Supabase = new Supabase();
}
?>
