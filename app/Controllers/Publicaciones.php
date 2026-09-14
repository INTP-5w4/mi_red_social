<?php

namespace App\Controllers;

use App\Models\PublicacionModel;
use App\Models\LikeModel;
use App\Models\LogLikeModel;
use App\Models\GuardadoModel;
use App\Models\ComentarioModel;
use Config\Services;

class Publicaciones extends BaseController
{
    /**
     * Crea una nueva publicación (imagen + descripción opcional).
     */
    public function crear()
    {
        $reglas = [
            'img'         => 'uploaded[img]|is_image[img]|max_size[img,4096]',
            'descripcion' => 'permit_empty|max_length[2200]',
        ];

        $validation = Services::validation();
        $validation->setRules($reglas);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->to('/')
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        $archivo       = $this->request->getFile('img');
        $nombreArchivo = $archivo->getRandomName();
        $archivo->move(ROOTPATH . 'public/uploads/publicaciones', $nombreArchivo);

        $publicacionModel = new PublicacionModel();
        $publicacionModel->insert([
            'id_usuario'  => session()->get('id_usuario'),
            'img'         => $nombreArchivo,
            'descripcion' => $this->request->getPost('descripcion'),
        ]);

        return redirect()->to('/');
    }

    /**
     * Da o quita el like de una publicación (toggle) y deja registro en el log.
     */
    public function like(int $idPublicacion)
    {
        $idUsuario    = (int) session()->get('id_usuario');
        $likeModel    = new LikeModel();
        $logLikeModel = new LogLikeModel();

        $existente = $likeModel
            ->where('id_usuario', $idUsuario)
            ->where('id_publicacion', $idPublicacion)
            ->first();

        if ($existente) {
            $likeModel->delete($existente['id']);
            $logLikeModel->registrar($idUsuario, $idPublicacion, 'Unlike');
        } else {
            $likeModel->insert([
                'id_usuario'     => $idUsuario,
                'id_publicacion' => $idPublicacion,
            ]);
            $logLikeModel->registrar($idUsuario, $idPublicacion, 'Like');
        }

        return redirect()->to('/');
    }

    /**
     * Guarda o quita una publicación de guardados (toggle).
     */
    public function guardar(int $idPublicacion)
    {
        $idUsuario     = (int) session()->get('id_usuario');
        $guardadoModel = new GuardadoModel();

        $existente = $guardadoModel
            ->where('id_usuario', $idUsuario)
            ->where('id_publicacion', $idPublicacion)
            ->first();

        if ($existente) {
            $guardadoModel->delete($existente['id']);
        } else {
            $guardadoModel->insert([
                'id_usuario'     => $idUsuario,
                'id_publicacion' => $idPublicacion,
            ]);
        }

        return redirect()->to('/');
    }

    /**
     * Agrega un comentario a una publicación.
     */
    public function comentar(int $idPublicacion)
    {
        $reglas = [
            'descripcion' => 'required|min_length[1]|max_length[500]',
        ];

        $validation = Services::validation();
        $validation->setRules($reglas);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->to('/')
                ->with('errors', $validation->getErrors());
        }

        $comentarioModel = new ComentarioModel();
        $comentarioModel->insert([
            'id_usuario'     => session()->get('id_usuario'),
            'id_publicacion' => $idPublicacion,
            'descripcion'    => $this->request->getPost('descripcion'),
        ]);

        return redirect()->to('/');
    }
}