<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\LogSesionModel;
use Config\Services;

class Auth extends BaseController
{
    /**
     * Muestra el formulario de login (y registro, en el modal).
     */
    public function login(): string
    {
        // Si ya hay una sesión activa no tiene sentido mostrar el login de nuevo
        if (session()->get('logueado')) {
            return redirect()->to('/');
        }

        return view('login');
    }

    /**
     * Procesa el envío del formulario de login.
     */
    public function procesarLogin()
    {
        $reglas = [
            'correo'      => 'required|valid_email',
            'contrasenia' => 'required|min_length[8]',
        ];

        $validation = Services::validation();
        $validation->setRules($reglas);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->to('/login')
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->where('correo', $this->request->getPost('correo'))->first();

        if (! $usuario || ! password_verify($this->request->getPost('contrasenia'), $usuario['password_hash'])) {
            return redirect()->to('/login')
                ->withInput()
                ->with('errors', ['login' => 'Correo o contraseña incorrectos.']);
        }

        session()->set([
            'id_usuario' => $usuario['id'],
            'nickname'   => $usuario['nickname'],
            'logueado'   => true,
        ]);

        (new LogSesionModel())->registrar((int) $usuario['id'], 'Login');

        return redirect()->to('/');
    }

    /**
     * Procesa el envío del formulario de registro (modal en login.php).
     */
    public function registro()
    {
        $reglas = [
            'usuario'     => 'required|min_length[3]|max_length[100]',
            'correo'      => 'required|valid_email|is_unique[usuarios.correo]',
            'contrasenia' => 'required|min_length[8]',
        ];

        $validation = Services::validation();
        $validation->setRules($reglas);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->to('/login')
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $usuarioModel = new UsuarioModel();
        $usuarioModel->insert([
            'nickname'      => $this->request->getPost('usuario'),
            'correo'        => $this->request->getPost('correo'),
            'password_hash' => password_hash($this->request->getPost('contrasenia'), PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/login')
            ->with('exito', 'Cuenta creada correctamente, ya puedes iniciar sesión.');
    }

    /**
     * Cierra la sesión activa y deja registro en el log.
     */
    public function logout()
    {
        $idUsuario = session()->get('id_usuario');

        if ($idUsuario) {
            (new LogSesionModel())->registrar((int) $idUsuario, 'Logout');
        }

        session()->destroy();

        return redirect()->to('/login');
    }
}