<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Se ejecuta antes de llegar al controlador.
     * Si no hay una sesión activa, redirige al login.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('logueado')) {
            return redirect()->to('/login')
                ->with('errors', ['auth' => 'Debes iniciar sesión para continuar.']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere ninguna acción posterior
    }
}