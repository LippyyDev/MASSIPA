<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ApiKeyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return;
        }
        
        $apiKey = trim($request->getHeaderLine('X-API-KEY'));
        
        if (empty($apiKey)) {
            $apiKey = trim($request->getGet('X-API-Key') ?? '')
                   ?: trim($request->getGet('X-API-KEY') ?? '')
                   ?: trim($request->getGet('x-api-key') ?? '')
                   ?: trim($request->getVar('X-API-Key') ?? '')
                   ?: trim($request->getVar('X-API-KEY') ?? '')
                   ?: trim($_GET['X-API-Key'] ?? $_GET['X-API-KEY'] ?? '');
        }
        
        $apiKeyModel = new \App\Models\Api\ApiKeyModel();
        if (empty($apiKey) || !$apiKeyModel->isValid($apiKey)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Unauthorized: Invalid API Key',
                    'data' => null
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
} 