<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Ingest\Diagnostico;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class DiagnosticoController extends Controller
{
    public function __construct(
        protected Diagnostico $diagnostico
    ) {}

    /**
     * Buscador inicial de la herramienta "Revisa tu nodo".
     */
    public function index(Request $request): View|RedirectResponse
    {
        $busqueda = trim((string) $request->query('buscar', ''));
        $resultados = [];

        if (mb_strlen($busqueda) >= 2) {
            // Si el usuario introduce directamente un ID !xxxxxxxx o xxxxxxxx, redirigir directo
            if (preg_match('/^!?([0-9A-Fa-f]{8})$/', $busqueda, $m)) {
                $lang = (string) $request->query('lang', '');
                $langQuery = in_array($lang, ['es', 'en', 'pt'], true) && $lang !== 'es' ? '?lang=' . $lang : '';

                return redirect('/revisa-tu-nodo/!' . strtolower($m[1]) . $langQuery);
            }

            try {
                $res = $this->diagnostico->buscar($busqueda, 20);
                $resultados = (array) ($res->datos['items'] ?? []);
            } catch (Throwable) {
                $resultados = [];
            }
        }

        return view('revisa-nodo.index', [
            'busqueda' => $busqueda,
            'resultados' => $resultados,
        ]);
    }

    /**
     * Informe completo de salud y auditoría de un nodo específico.
     */
    public function show(string $id): View
    {
        $idNormalizado = Diagnostico::normalizarId($id);

        $informe = null;
        try {
            $res = $this->diagnostico->obtenerDiagnostico($idNormalizado);
            $informe = $res->datos;
        } catch (Throwable) {
            $informe = null;
        }

        if (!$informe) {
            return view('revisa-nodo.show', [
                'idBuscado' => $idNormalizado,
                'noEncontrado' => true,
                'informe' => null,
            ]);
        }

        return view('revisa-nodo.show', [
            'idBuscado' => $idNormalizado,
            'noEncontrado' => false,
            'informe' => $informe,
        ]);
    }
}
