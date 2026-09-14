<?php

namespace App\Controllers;

use App\Models\PublicacionModel;
use App\Models\LikeModel;
use App\Models\GuardadoModel;
use App\Models\ComentarioModel;

class Home extends BaseController
{
    public function index(): string
    {
        $publicacionModel = new PublicacionModel();
        $likeModel        = new LikeModel();
        $guardadoModel    = new GuardadoModel();
        $comentarioModel  = new ComentarioModel();

        $idUsuarioActivo = session()->get('id_usuario');

        // Trae todas las publicaciones existentes, de más reciente a más antigua
        $publicaciones = $publicacionModel
            ->select('publicaciones.*, usuarios.nickname')
            ->join('usuarios', 'usuarios.id = publicaciones.id_usuario')
            ->orderBy('publicaciones.created_at', 'DESC')
            ->findAll();

        // Enriquecemos cada publicación con sus likes, comentarios y el
        // estado (like/guardado) del usuario que está viendo el feed
        foreach ($publicaciones as &$publicacion) {
            $publicacion['total_likes'] = $likeModel
                ->where('id_publicacion', $publicacion['id'])
                ->countAllResults();

            $publicacion['ya_dio_like'] = $idUsuarioActivo
                ? $likeModel->yaDioLike((int) $idUsuarioActivo, (int) $publicacion['id'])
                : false;

            $publicacion['ya_guardado'] = $idUsuarioActivo
                ? $guardadoModel->yaGuardado((int) $idUsuarioActivo, (int) $publicacion['id'])
                : false;

            $publicacion['comentarios'] = $comentarioModel
                ->select('comentarios.*, usuarios.nickname')
                ->join('usuarios', 'usuarios.id = comentarios.id_usuario')
                ->where('id_publicacion', $publicacion['id'])
                ->orderBy('comentarios.created_at', 'ASC')
                ->findAll();
        }
        unset($publicacion);

        return view('main_page', ['publicaciones' => $publicaciones]);
    }
}