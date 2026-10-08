<?php

declare(strict_types=1);

namespace App\Servicios;

use Illuminate\Support\Facades\Cache;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use RuntimeException;

class ContenidoMarkdown
{
    /**
     * @var array<string, string>
     */
    protected array $variables;

    protected MarkdownConverter $converter;

    public function __construct()
    {
        $projectDomain = config('proyecto.dominio');

        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'external_link' => [
                'internal_hosts' => [(string) $projectDomain],
                'open_in_new_window' => true,
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new ExternalLinkExtension());

        $this->converter = new MarkdownConverter($environment);


        $this->variables = [
            '{PROJECT_NAME}' => (string) config('proyecto.nombre'),
            '{PROJECT_DOMAIN}' => (string) $projectDomain,
            '{PROJECT_CONTACT}' => (string) config('proyecto.contacto'),
            '{PRIMARY_CHANNEL}' => (string) config('proyecto.canales.primario'),
            '{ALLOWED_CHANNELS}' => implode(', ', config('proyecto.canales.permitidos')),
            '{CHANNEL_KEY_DEFAULT}' => (string) config('proyecto.canales.clave_defecto'),
            '{LORA_REGION}' => (string) config('proyecto.lora.region'),
            '{LORA_BANDWIDTH}' => (string) config('proyecto.lora.bandwidth'),
            '{LORA_BANDWIDTH_KHZ}' => (string) config('proyecto.lora.bandwidth_khz'),
            '{LORA_SPREAD_FACTOR}' => (string) config('proyecto.lora.spread_factor'),
            '{LORA_CODING_RATE}' => (string) config('proyecto.lora.coding_rate'),
            '{LORA_FREQUENCY_SLOT}' => (string) config('proyecto.lora.frequency_slot'),
            '{LORA_FREQUENCY_MHZ}' => (string) config('proyecto.lora.frequency_mhz'),
            '{LORA_HOP_LIMIT}' => (string) config('proyecto.lora.hop_limit'),
            '{INGESTA_LORA_PREAMBULO}' => (string) config('proyecto.lora.preambulo'),
            '{MQTT_HOST}' => (string) config('proyecto.mqtt.host_publico'),
            '{MQTT_PORT}' => (string) config('proyecto.mqtt.puerto_plano'),
            '{MQTT_TLS_PORT}' => (string) config('proyecto.mqtt.puerto_tls'),
            '{MQTT_TOPIC_ROOT}' => (string) config('proyecto.mqtt.topic_root'),
            '{MQTT_GATEWAY_USER}' => (string) config('proyecto.mqtt.gateway_user'),
            '{MQTT_GATEWAY_PASSWORD}' => (string) config('proyecto.mqtt.gateway_password'),
            '{MESHVIEW_URL}' => 'https://' . env('MESHVIEW_DOMAIN', 'meshview.' . $projectDomain),
            '{POTATOMESH_URL}' => 'https://potato.' . $projectDomain,
            '{TELEGRAM_BOT_USERNAME}' => (string) config('proyecto.bots.telegram_username'),
            '{DISCORD_INVITE_URL}' => (string) config('proyecto.bots.discord_invite_url'),
            '{BOT_RIESGOS_DEFECTO}' => (string) config('proyecto.bots.riesgos_defecto'),
            '{BOT_TIPOS_DEFECTO}' => (string) config('proyecto.bots.tipos_defecto'),
            '{FW_URL_CLIENTE_WEB}' => (string) config('proyecto.firmware.cliente_web'),
            '{FW_URL_IOS}' => (string) config('proyecto.firmware.ios'),
            '{FW_URL_ANDROID}' => (string) config('proyecto.firmware.android'),
            '{FW_URL_DESCARGAS}' => (string) config('proyecto.firmware.descargas'),
            '{FW_URL_VERSIONES}' => (string) config('proyecto.firmware.versiones'),
            '{HOSTING_PROVIDER}' => (string) config('proyecto.legal.proveedor_hosting'),
            '{HOSTING_LOCATION}' => (string) config('proyecto.legal.ubicacion_hosting'),
            '{AUTOR_NOMBRE}' => (string) config('autoria.nombre'),
            '{AUTOR_NICK}' => (string) config('autoria.nick'),
            '{AUTOR_EMAIL}' => (string) config('autoria.email'),
            '{AUTOR_WEB}' => (string) (config('autoria.webs.0.url') ?? 'https://raupulus.dev'),
        ];
    }

    /**
     * Carga y procesa un documento Markdown devolviendo título, descripción y HTML.
     *
     * @param string $nombreFichero Nombre relativo en resources/contenido/ (ej. '02-project.md')
     * @return array{titulo: string, descripcion: string, html: string, h1: string}
     */
    public function render(string $nombreFichero): array
    {
        $path = resource_path('contenido/' . $nombreFichero);
        if (!file_exists($path)) {
            throw new RuntimeException("El archivo de contenido {$nombreFichero} no existe.");
        }

        $mtime = filemtime($path);
        $cacheKey = "markdown_render_v3:{$nombreFichero}:{$mtime}";

        /** @var array{titulo: string, descripcion: string, html: string, h1: string} */
        return Cache::rememberForever($cacheKey, function () use ($path, $nombreFichero): array {
            $rawContent = (string) file_get_contents($path);

            // 1. Extraer metadatos de SEO si existen
            $titulo = (string) config('proyecto.nombre');
            $descripcion = (string) config('proyecto.seo.descripcion_defecto');

            if (preg_match('/## SEO\s+- \*\*Título:\*\*\s*`?([^`\n]+)`?/u', $rawContent, $mTit)) {
                $titulo = trim($mTit[1]);
            }
            if (preg_match('/## SEO.*?-\s+\*\*Descripción:\*\*\s*`?([^`\n]+)`?/us', $rawContent, $mDesc)) {
                $descripcion = trim($mDesc[1]);
            }

            // 2. Extraer cuerpo del texto (tras "## Borrador del texto" o el archivo completo)
            $body = $rawContent;
            if (preg_match('/## Borrador del texto\s+(.+)$/us', $rawContent, $mBody)) {
                $body = $mBody[1];
            }

            // Descartar metadatos técnicos, tablas de configuración y notas internas al final del borrador
            $partes = preg_split('/\n##\s+(?:Datos dinámicos|Supuestos aplicados|Criterios de aceptación)/u', $body);
            $body = $partes[0] ?? $body;
            $body = preg_replace('/\n---\s*\n>\s*Creado:.*$/us', '', $body) ?? $body;


            // 3. Extraer H1 si está anotado como "**H1:** Título" y descartar meta-instrucciones previas
            $h1 = $titulo;
            if (preg_match('/\*\*H1:\*\*\s*([^\n]+)\n+(.+)$/us', $body, $mH1)) {
                $h1 = trim($mH1[1]);
                $body = $mH1[2];
            } elseif (preg_match('/\*\*H1:\*\*\s*([^\n]+)/u', $body, $mH1)) {
                $h1 = trim($mH1[1]);
                $body = preg_replace('/\*\*H1:\*\*\s*[^\n]+\n+/u', '', $body) ?? $body;
            }

            // Limpiar posibles notas internas que hayan quedado al inicio
            $body = preg_replace('/^Cada\s+`?###`?.*?\n+/um', '', $body) ?? $body;
            $body = preg_replace('/^>\s+\*\*Borrador orientativo\.\*\*.*?\n+/um', '', $body) ?? $body;

            // 4. Reemplazar variables
            $titulo = strtr($titulo, $this->variables);
            $descripcion = strtr($descripcion, $this->variables);
            $h1 = strtr($h1, $this->variables);
            $body = strtr($body, $this->variables);

            // 5. Detectar variables no sustituidas {VARIABLE}
            if (preg_match('/\{[A-Z0-9_]+\}/', $body, $mErr)) {
                throw new RuntimeException("Variable desconocida sin sustituir '{$mErr[0]}' en {$nombreFichero}");
            }

            // 6. Normalizar los encabezados para preservar la jerarquía H1 (título de página) -> H2 -> H3
            $body = preg_replace('/^####\s+/m', '### ', $body) ?? $body;
            $body = preg_replace('/^###\s+/m', '## ', $body) ?? $body;

            // 7. Convertir Markdown a HTML
            $html = (string) $this->converter->convert($body);

            return [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'h1' => $h1,
                'html' => $html,
            ];
        });
    }
}
