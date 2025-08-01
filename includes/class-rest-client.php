<?php
/**
 * RestClient - Classe base para todas as APIs
 * 
 * @package Cupompromo
 * @version 1.0.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

class Cupompromo_Rest_Client {
    protected $uri = null;
    private $headers = [];
    private $timeout = 30;
    private $body_type = 'json';
    private $debug = false;
    public $status_code;
    
    /**
     * Configurar URI base da API
     */
    public function set_uri(string $uri) {
        $this->uri = $uri;
    }
    
    /**
     * Configurar headers da requisição
     */
    public function set_headers(array $headers) {
        $this->headers = $headers;
    }
    
    /**
     * Configurar timeout da requisição
     */
    public function set_timeout(int $timeout) {
        $this->timeout = $timeout;
    }
    
    /**
     * Configurar tipo de body (json ou query)
     */
    public function set_body_type(string $type) {
        $this->body_type = $type;
    }
    
    /**
     * Habilitar/desabilitar debug
     */
    public function set_debug(bool $debug) {
        $this->debug = $debug;
    }
    
    /**
     * Processar resposta HTTP
     */
    protected function response($response): ?string {
        if (is_wp_error($response)) {
            if ($this->debug) {
                error_log('Cupompromo API Error: ' . $response->get_error_message());
            }
            return null;
        }
        
        $response_code = (int) wp_remote_retrieve_response_code($response);
        $this->status_code = $response_code;
        
        if ($response_code < 200 || $response_code >= 300) {
            if ($this->debug) {
                error_log('Cupompromo API HTTP Error: ' . $response_code . ' - ' . wp_remote_retrieve_body($response));
            }
            return null;
        }
        
        return wp_remote_retrieve_body($response);
    }
    
    /**
     * Fazer requisição GET
     */
    public function get(string $path, array $query = []): ?array {
        $url = $this->uri . $path;
        
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }
        
        if ($this->debug) {
            error_log('Cupompromo API GET: ' . $url);
        }
        
        $response = wp_remote_request($url, [
            'method' => 'GET',
            'timeout' => $this->timeout,
            'headers' => $this->headers,
        ]);
        
        $body = $this->response($response);
        return $body ? json_decode($body, true) : null;
    }
    
    /**
     * Fazer requisição POST
     */
    public function post(string $path, array $body = []): ?array {
        $url = $this->uri . $path;
        
        $request_body = $this->body_type === 'json' 
            ? json_encode($body) 
            : http_build_query($body);
        
        $headers = $this->headers;
        if ($this->body_type === 'json') {
            $headers['Content-Type'] = 'application/json';
        }
        
        if ($this->debug) {
            error_log('Cupompromo API POST: ' . $url);
            error_log('Cupompromo API Body: ' . $request_body);
        }
        
        $response = wp_remote_request($url, [
            'method' => 'POST',
            'timeout' => $this->timeout,
            'headers' => $headers,
            'body' => $request_body,
        ]);
        
        $body = $this->response($response);
        return $body ? json_decode($body, true) : null;
    }
    
    /**
     * Fazer requisição PUT
     */
    public function put(string $path, array $body = []): ?array {
        $url = $this->uri . $path;
        
        $request_body = $this->body_type === 'json' 
            ? json_encode($body) 
            : http_build_query($body);
        
        $headers = $this->headers;
        if ($this->body_type === 'json') {
            $headers['Content-Type'] = 'application/json';
        }
        
        if ($this->debug) {
            error_log('Cupompromo API PUT: ' . $url);
        }
        
        $response = wp_remote_request($url, [
            'method' => 'PUT',
            'timeout' => $this->timeout,
            'headers' => $headers,
            'body' => $request_body,
        ]);
        
        $body = $this->response($response);
        return $body ? json_decode($body, true) : null;
    }
    
    /**
     * Fazer requisição DELETE
     */
    public function delete(string $path, array $query = []): ?array {
        $url = $this->uri . $path;
        
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }
        
        if ($this->debug) {
            error_log('Cupompromo API DELETE: ' . $url);
        }
        
        $response = wp_remote_request($url, [
            'method' => 'DELETE',
            'timeout' => $this->timeout,
            'headers' => $this->headers,
        ]);
        
        $body = $this->response($response);
        return $body ? json_decode($body, true) : null;
    }
    
    /**
     * Fazer requisição GET com resposta XML
     */
    public function get_xml(string $path, array $query = []): ?array {
        $url = $this->uri . $path;
        
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }
        
        if ($this->debug) {
            error_log('Cupompromo API GET XML: ' . $url);
        }
        
        $response = wp_remote_request($url, [
            'method' => 'GET',
            'timeout' => $this->timeout,
            'headers' => $this->headers,
        ]);
        
        if (is_wp_error($response)) {
            return null;
        }
        
        $response_code = (int) wp_remote_retrieve_response_code($response);
        $this->status_code = $response_code;
        
        if ($response_code < 200 || $response_code >= 300) {
            return null;
        }
        
        $body = wp_remote_retrieve_body($response);
        
        if (!$body) {
            return null;
        }
        
        // Converter XML para array
        $xml = simplexml_load_string($body);
        if ($xml === false) {
            return null;
        }
        
        return json_decode(json_encode($xml), true);
    }
    
    /**
     * Obter status code da última requisição
     */
    public function get_status_code(): int {
        return $this->status_code;
    }
    
    /**
     * Verificar se a última requisição foi bem-sucedida
     */
    public function is_success(): bool {
        return $this->status_code >= 200 && $this->status_code < 300;
    }
    
    /**
     * Obter headers da resposta
     */
    public function get_response_headers($response): array {
        if (is_wp_error($response)) {
            return [];
        }
        
        return wp_remote_retrieve_headers($response);
    }
    
    /**
     * Fazer requisição com retry automático
     */
    public function request_with_retry(string $method, string $path, array $data = [], int $max_retries = 3): ?array {
        $attempts = 0;
        
        while ($attempts < $max_retries) {
            $attempts++;
            
            try {
                switch (strtoupper($method)) {
                    case 'GET':
                        $result = $this->get($path, $data);
                        break;
                    case 'POST':
                        $result = $this->post($path, $data);
                        break;
                    case 'PUT':
                        $result = $this->put($path, $data);
                        break;
                    case 'DELETE':
                        $result = $this->delete($path, $data);
                        break;
                    default:
                        return null;
                }
                
                if ($result !== null) {
                    return $result;
                }
                
                // Aguardar antes de tentar novamente (exponential backoff)
                if ($attempts < $max_retries) {
                    $delay = pow(2, $attempts) * 1000000; // microsegundos
                    usleep($delay);
                }
                
            } catch (Exception $e) {
                if ($this->debug) {
                    error_log('Cupompromo API Retry Error: ' . $e->getMessage());
                }
                
                if ($attempts >= $max_retries) {
                    throw $e;
                }
            }
        }
        
        return null;
    }
} 