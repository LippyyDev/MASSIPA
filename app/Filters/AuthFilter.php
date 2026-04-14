<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Periksa apakah pengguna sudah login
        if (!$session->get('isLoggedIn')) {
            $response = service('response');
            $response->setStatusCode(401);
            echo view('errors/html/error_401');
            exit;
        }

        // Dapatkan segmen pertama dari URI untuk menentukan grup (admin atau user)
        $uri = $request->getUri();
        $group = $uri->getSegment(1); // 'admin' atau 'user'

        // Dapatkan role pengguna dari sesi
        $userRole = $session->get('role');

        // Periksa apakah role pengguna sesuai dengan grup rute
        if (($group === 'admin' && $userRole !== 'admin') || ($group === 'user' && $userRole !== 'user')) {
            // Tampilkan halaman error 403 forbidden
            $response = service('response');
            $response->setStatusCode(403);
            echo view('errors/html/error_403');
            exit;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada tindakan setelah request
    }
}