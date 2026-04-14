<?php
namespace App\Filters;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $origin = $request->getHeaderLine('Origin');
        $response = service('response');
        
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            if (!empty($origin)) {
                $normalizedOrigin = rtrim($origin, '/');
                $model = new \App\Models\Api\AllowedOriginModel();
                $isAllowed = $model->isAllowedOrigin($normalizedOrigin);
                
                if ($isAllowed) {
                    $response->setHeader('Access-Control-Allow-Origin', $normalizedOrigin);
                    $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
                    $response->setHeader('Access-Control-Allow-Headers', 'X-API-Key, Content-Type, Authorization, Accept, Origin');
                    $response->setHeader('Access-Control-Max-Age', '3600');
                    $response->setHeader('Access-Control-Allow-Credentials', 'true');
                }
            }
            
            return $response->setStatusCode(204);
        }
        
        if (empty($origin)) {
            return;
        }
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $origin = $request->getHeaderLine('Origin');
        
        if (empty($origin)) {
            return $response;
        }
        
        $origin = rtrim($origin, '/');
        $model = new \App\Models\Api\AllowedOriginModel();
        $isAllowed = $model->isAllowedOrigin($origin);
        
        if ($isAllowed) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->setHeader('Access-Control-Allow-Headers', 'X-API-Key, Content-Type, Authorization, Accept, Origin');
            $response->setHeader('Access-Control-Allow-Credentials', 'true');
        }
        
        return $response;
    }
} 