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
     * Páginas desde las que se puede quitar algo => ruta a la que se regresa.
     * Se usa como lista blanca del campo "contexto" que manda el formulario.
     */
    private const CONTEXTOS = [
        'likes'     => '/logs/likes',
        'guardados' => '/logs/guardados',
        'acciones'  => '/logs/acciones',
    ];

    /**
     * Historial de likes/unlikes del usuario en sesión. Paginado de 10 en 10.
     * Solo la fila "tope" de la pila (el último like que sigue activo) muestra el botón Quitar.
     */
    public function likes(): string
    {
        $idUsuario = (int) session()->get('id_usuario');
        $topes     = $this->calcularTopes($idUsuario);
        $idTope    = $topes['like'] !== null ? (int) $topes['like']['id'] : null;

        $logLikeModel = new LogLikeModel();

        $registros = $logLikeModel
            ->select('log_likes.*, publicaciones.img, publicaciones.descripcion')
            ->join('publicaciones', 'publicaciones.id = log_likes.id_publicacion')
            ->where('log_likes.id_usuario', $idUsuario)
            ->orderBy('log_likes.fecha', 'DESC')
            ->paginate(10, 'likes');

        foreach ($registros as &$registro) {
            $registro['es_tope'] = $idTope !== null && (int) $registro['id'] === $idTope;
        }
        unset($registro);

        return view('Likes', [
            'registros' => $registros,
            'pager'     => $logLikeModel->pager,
        ]);
    }

    /**
     * Historial de guardados/quitados del usuario en sesión. Paginado de 10 en 10.
     * Solo la fila "tope" de la pila (el último guardado que sigue activo) muestra el botón Quitar.
     */
    public function guardados(): string
    {
        $idUsuario = (int) session()->get('id_usuario');
        $topes     = $this->calcularTopes($idUsuario);
        $idTope    = $topes['guardado'] !== null ? (int) $topes['guardado']['id'] : null;

        $logGuardadoModel = new LogGuardadoModel();

        $registros = $logGuardadoModel
            ->select('log_guardados.*, publicaciones.img, publicaciones.descripcion')
            ->join('publicaciones', 'publicaciones.id = log_guardados.id_publicacion')
            ->where('log_guardados.id_usuario', $idUsuario)
            ->orderBy('log_guardados.fecha', 'DESC')
            ->paginate(10, 'guardados');

        foreach ($registros as &$registro) {
            $registro['es_tope'] = $idTope !== null && (int) $registro['id'] === $idTope;
        }
        unset($registro);

        return view('Guardados', [
            'registros' => $registros,
            'pager'     => $logGuardadoModel->pager,
        ]);
    }

    /**
     * Historial combinado (likes + guardados + comentarios) del usuario en sesión,
     * ordenado cronológicamente. Paginado de 20 en 20.
     *
     * Al venir de tablas distintas no se puede usar el paginate() nativo
     * de un solo modelo, así que aquí traemos los historiales completos
     * del usuario, los unimos, ordenamos por fecha y paginamos a mano.
     *
     * Solo la fila "tope" general (la última acción activa entre likes, guardados
     * y comentarios) muestra el botón Quitar.
     */
    public function acciones(): string
    {
        $idUsuario = (int) session()->get('id_usuario');
        $porPagina = 20;
        $topes     = $this->calcularTopes($idUsuario);
        $tope      = $topes['general'];

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

        foreach ($registrosPagina as &$registro) {
            $registro['publicacion'] = $publicaciones[$registro['id_publicacion']] ?? null;

            // Los ids se repiten entre tablas, por eso se compara origen + id
            $registro['es_tope'] = $tope !== null
                && $registro['origen'] === $tope['origen']
                && (int) $registro['id'] === $tope['id'];
        }
        unset($registro);

        return view('Acciones', [
            'registros'    => $registrosPagina,
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas,
        ]);
    }

    /**
     * Quita (pop) la última inserción activa de una pila.
     *
     * $tipo: like | guardado | comentario
     * $id:   id de la publicación (like/guardado) o id del comentario.
     * El formulario manda además "contexto" (likes | guardados | acciones)
     * para saber contra qué pila se valida.
     */
    public function quitar(string $tipo, int $id)
    {
        $idUsuario = (int) session()->get('id_usuario');
        $contexto  = (string) $this->request->getPost('contexto');

        if (! isset(self::CONTEXTOS[$contexto])) {
            return redirect()->to('/logs/acciones')
                ->with('errors', ['pila' => 'Origen no válido.']);
        }

        $vuelta = self::CONTEXTOS[$contexto];

        // La validación real está aquí: ocultar el botón en la vista no basta,
        // porque alguien podría mandar el POST a mano con otro id.
        $topes = $this->calcularTopes($idUsuario);

        if (! $this->esTope($contexto, $tipo, $id, $topes)) {
            return redirect()->to($vuelta)
                ->with('errors', ['pila' => 'Solo se puede quitar la última acción registrada.']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        if ($tipo === 'like') {
            $likeModel = new LikeModel();
            $existente = $likeModel
                ->where('id_usuario', $idUsuario)
                ->where('id_publicacion', $id)
                ->first();

            if ($existente) {
                $likeModel->delete($existente['id']);
                (new LogLikeModel())->registrar($idUsuario, $id, 'Unlike');
            }
        } elseif ($tipo === 'guardado') {
            $guardadoModel = new GuardadoModel();
            $existente     = $guardadoModel
                ->where('id_usuario', $idUsuario)
                ->where('id_publicacion', $id)
                ->first();

            if ($existente) {
                $guardadoModel->delete($existente['id']);
                (new LogGuardadoModel())->registrar($idUsuario, $id, 'Quitar');
            }
        } elseif ($tipo === 'comentario') {
            $comentarioModel = new ComentarioModel();
            $comentario      = $comentarioModel->find($id);

            // Solo el dueño del comentario puede eliminarlo
            if ($comentario && (int) $comentario['id_usuario'] === $idUsuario) {
                $comentarioModel->delete($id);
            }
        }

        $db->transComplete();

        return redirect()->to($vuelta)->with('exito', 'Se quitó la última acción.');
    }

    /**
     * Indica si (tipo, id) es el tope de la pila del contexto indicado.
     */
    private function esTope(string $contexto, string $tipo, int $id, array $topes): bool
    {
        if ($contexto === 'acciones') {
            $general = $topes['general'];

            return $general !== null
                && $general['origen'] === $tipo
                && $general['referencia'] === $id;
        }

        if ($contexto === 'likes' && $tipo === 'like') {
            return $topes['like'] !== null
                && (int) $topes['like']['id_publicacion'] === $id;
        }

        if ($contexto === 'guardados' && $tipo === 'guardado') {
            return $topes['guardado'] !== null
                && (int) $topes['guardado']['id_publicacion'] === $id;
        }

        return false;
    }

    private function calcularTopes(int $idUsuario): array
    {
        $idsLikesActivos = array_map('intval', array_column(
            (new LikeModel())->where('id_usuario', $idUsuario)->findAll(),
            'id_publicacion'
        ));

        $idsGuardadosActivos = array_map('intval', array_column(
            (new GuardadoModel())->where('id_usuario', $idUsuario)->findAll(),
            'id_publicacion'
        ));

        $topeLike = null;
        if (! empty($idsLikesActivos)) {
            $topeLike = (new LogLikeModel())
                ->where('id_usuario', $idUsuario)
                ->where('tipo_accion', 'Like')
                ->whereIn('id_publicacion', $idsLikesActivos)
                ->orderBy('id', 'DESC')
                ->first();
        }

        $topeGuardado = null;
        if (! empty($idsGuardadosActivos)) {
            $topeGuardado = (new LogGuardadoModel())
                ->where('id_usuario', $idUsuario)
                ->where('tipo_accion', 'Guardar')
                ->whereIn('id_publicacion', $idsGuardadosActivos)
                ->orderBy('id', 'DESC')
                ->first();
        }

        $topeComentario = (new ComentarioModel())
            ->where('id_usuario', $idUsuario)
            ->orderBy('id', 'DESC')
            ->first();

        $candidatos = [];

        if ($topeLike !== null) {
            $candidatos[] = [
                'origen'     => 'like',
                'id'         => (int) $topeLike['id'],
                'referencia' => (int) $topeLike['id_publicacion'],
                'fecha'      => $topeLike['fecha'],
            ];
        }

        if ($topeGuardado !== null) {
            $candidatos[] = [
                'origen'     => 'guardado',
                'id'         => (int) $topeGuardado['id'],
                'referencia' => (int) $topeGuardado['id_publicacion'],
                'fecha'      => $topeGuardado['fecha'],
            ];
        }

        if ($topeComentario !== null) {
            $candidatos[] = [
                'origen'     => 'comentario',
                'id'         => (int) $topeComentario['id'],
                'referencia' => (int) $topeComentario['id'],
                'fecha'      => $topeComentario['created_at'],
            ];
        }

        usort($candidatos, static fn ($a, $b) => strtotime($b['fecha']) <=> strtotime($a['fecha']));

        return [
            'like'       => $topeLike,
            'guardado'   => $topeGuardado,
            'comentario' => $topeComentario,
            'general'    => $candidatos[0] ?? null,
        ];
    }
}