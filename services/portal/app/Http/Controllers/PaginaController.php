<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Servicios\ContenidoMarkdown;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class PaginaController extends Controller
{
    public function __construct(
        protected ContenidoMarkdown $markdown
    ) {}

    public function proyecto(): View
    {
        $data = $this->markdown->render('02-project.md');
        return view('pagina', $data);
    }

    public function quienLoImpulsa(): View
    {
        $data = $this->markdown->render('03-who.md');
        return view('pagina', array_merge($data, [
            'mostrarBloqueAutoria' => true,
        ]));
    }

    public function comoSeGestiona(): View
    {
        $data = $this->markdown->render('04-governance.md');
        return view('pagina', $data);
    }

    public function configuraTuNodo(): View
    {
        $data = $this->markdown->render('05-node-setup.md');
        return view('configura-tu-nodo', $data);
    }

    public function conectaTuGateway(): View
    {
        $data = $this->markdown->render('06-gateway.md');
        return view('conecta-tu-gateway', $data);
    }

    public function bots(): View
    {
        $data = $this->markdown->render('07-bots.md');
        return view('pagina', $data);
    }

    public function firmware(): View
    {
        $data = $this->markdown->render('15-firmware.md');
        return view('pagina', $data);
    }

    public function apiDocs(): View
    {
        $data = $this->markdown->render('10-api.md');
        return view('pagina', $data);
    }

    public function avisoLegal(): View
    {
        $data = $this->markdown->render('11-legal-notice.md');
        return view('pagina', $data);
    }

    public function privacidad(): View
    {
        $data = $this->markdown->render('12-privacy.md');
        return view('pagina', $data);
    }

    public function cookies(): View
    {
        $data = $this->markdown->render('13-cookies.md');
        return view('pagina', $data);
    }
}
