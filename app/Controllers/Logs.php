<?php

namespace App\Controllers;

use App\Models\LogLikeModel;
use App\Models\LogGuardadoModel;
use App\Models\LikeModel;
use App\Models\GuardadoModel;
use App\Models\ComentarioModel;
use App\Models\PublicacionModel;

class Logs extends BaseController
{
    /**
     * Historial de likes/unlikes del usuario en sesión. Paginado de 10 en 10.
     */
    public function likes(): string
    {
        $idUsuario = (int) session()->get('id_usuario');

        $logLikeModel = new LogLikeModel();

        $registros = $logLikeModel
            ->select('log_likes.*, publicaciones.img, publicaciones.descripcion')
            ->join('publicaciones', 'publicaciones.id = log_likes.id_publicacion')
            ->where('log_likes.id_usuario', $idUsuario)
            ->orderBy('log_likes.fecha', 'DESC')
            ->paginate(10, 'likes');

        // Publicaciones que el usuario tiene actualmente likeadas, para saber
        // en qué filas mostrar el botón de "Quitar".
        $idsActivos = array_map('intval', array_column(
            (new LikeModel())->where('id_usuario', $idUsuario)->findAll(),
            'id_publicacion'
        ));

        foreach ($registros as &$registro) {
            $registro['activo'] = in_array((int) $registro['id_publicacion'], $idsActivos, true);
        }
        unset($registro);

        return view('Likes', [
            'registros' => $registros,
            'pager'     => $logLikeModel->pager,
        ]);
    }

    /**
     * Historial de guardados/quitados del usuario en sesión. Paginado de 10 en 10.
     */
    public function guardados(): string
    {
        $idUsuario = (int) session()->get('id_usuario');

        $logGuardadoModel = new LogGuardadoModel();

        $registros = $logGuardadoModel
            ->select('log_guardados.*, publicaciones.img, publicaciones.descripcion')
            ->join('publicaciones', 'publicaciones.id = log_guardados.id_publicacion')
            ->where('log_guardados.id_usuario', $idUsuario)
            ->orderBy('log_guardados.fecha', 'DESC')
            ->paginate(10, 'guardados');

        // Publicaciones que el usuario tiene actualmente guardadas, para saber
        // en qué filas mostrar el botón de "Quitar".
        $idsActivos = array_map('intval', array_column(
            (new GuardadoModel())->where('id_usuario', $idUsuario)->findAll(),
            'id_publicacion'
        ));

        foreach ($registros as &$registro) {
            $registro['activo'] = in_array((int) $registro['id_publicacion'], $idsActivos, true);
        }
        unset($registro);

        return view('Guardados', [
            'registros' => $registros,
            'pager'     => $logGuardadoModel->pager,
        ]);
    }

    /**
     * Historial combinado (likes + guardados) del usuario en sesión, ordenado
     * cronológicamente. Paginado de 20 en 20.
     *
     * Al venir de dos tablas distintas no se puede usar el paginate() nativo
     * de un solo modelo, así que aquí traemos ambos historiales completos
     * del usuario, los unimos, ordenamos por fecha y paginamos a mano.
     */
    public function acciones(): string
    {
        $idUsuario = (int) session()->get('id_usuario');
        $porPagina = 20;

        $paginaActual = (int) ($this->request->getGet('pagina') ?? 1);
        if ($paginaActual < 1) {
            $paginaActual = 1;
        }

        $likes = (new LogLikeModel())
            ->where('id_usuario', $idUsuario)
            ->findAll();

        foreach ($likes as &$registro) {
            $registro['origen'] = 'like';
        }
        unset($registro);

        $guardados = (new LogGuardadoModel())
            ->where('id_usuario', $idUsuario)
            ->findAll();

        foreach ($guardados as &$registro) {
            $registro['origen'] = 'guardado';
        }
        unset($registro);

        $comentarios = (new ComentarioModel())
            ->where('id_usuario', $idUsuario)
            ->findAll();

        foreach ($comentarios as &$registro) {
            $registro['origen']      = 'comentario';
            $registro['fecha']       = $registro['created_at'];
            $registro['tipo_accion'] = 'Comentario';
        }
        unset($registro);

        // Unimos los tres historiales y ordenamos del más reciente al más antiguo
        $todos = array_merge($likes, $guardados, $comentarios);
        usort($todos, static fn ($a, $b) => strtotime($b['fecha']) <=> strtotime($a['fecha']));

        $totalRegistros = count($todos);
        $totalPaginas   = (int) max(1, ceil($totalRegistros / $porPagina));
        $paginaActual   = min($paginaActual, $totalPaginas);

        $registrosPagina = array_slice($todos, ($paginaActual - 1) * $porPagina, $porPagina);

        // Adjuntamos los datos de la publicación relacionada a cada registro
        // en un solo query, en vez de uno por fila.
        $idsPublicaciones = array_unique(array_column($registrosPagina, 'id_publicacion'));
        $publicaciones    = [];

        if (! empty($idsPublicaciones)) {
            $publicaciones = (new PublicacionModel())
                ->whereIn('id', $idsPublicaciones)
                ->findAll();
            $publicaciones = array_column($publicaciones, null, 'id');
        }

        // Estado actual de likes/guardados, para saber en qué filas mostrar
        // el botón de "Quitar".
        $likesActivos = array_map('intval', array_column(
            (new LikeModel())->where('id_usuario', $idUsuario)->findAll(),
            'id_publicacion'
        ));
        $guardadosActivos = array_map('intval', array_column(
            (new GuardadoModel())->where('id_usuario', $idUsuario)->findAll(),
            'id_publicacion'
        ));

        foreach ($registrosPagina as &$registro) {
            $registro['publicacion'] = $publicaciones[$registro['id_publicacion']] ?? null;

            if ($registro['origen'] === 'like') {
                $registro['activo'] = in_array((int) $registro['id_publicacion'], $likesActivos, true);
            } elseif ($registro['origen'] === 'guardado') {
                $registro['activo'] = in_array((int) $registro['id_publicacion'], $guardadosActivos, true);
            } else {
                // Un comentario siempre se puede quitar mientras exista
                $registro['activo'] = true;
            }
        }
        unset($registro);

        return view('Acciones', [
            'registros'    => $registrosPagina,
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas,
        ]);
    }
}