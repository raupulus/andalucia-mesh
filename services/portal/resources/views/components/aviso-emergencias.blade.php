<section aria-labelledby="aviso-emergencias-titulo" class="banner-emergencias" style="display: flex; gap: 1rem; align-items: flex-start;">
    <div style="font-size: 1.5rem; line-height: 1;" aria-hidden="true">⚠️</div>
    <div>
        <h4 id="aviso-emergencias-titulo" style="font-size: 1rem; font-weight: 700; color: inherit; margin-bottom: 0.25rem;">
            {{ __('portal.emergency.title') }}
        </h4>
        <p style="margin-bottom: 0; font-size: 0.9rem; line-height: 1.45;">
            {!! str_replace(
                [':name', '**NO es un servicio de emergencias**', '**112**', '**It is NOT an emergency service**', '**NÃO é um serviço de emergência**'],
                [config('proyecto.nombre'), '<strong>' . (app()->getLocale() === 'en' ? 'It is NOT an emergency service' : (app()->getLocale() === 'pt' ? 'NÃO é um serviço de emergência' : 'NO es un servicio de emergencias')) . '</strong>', '<strong>112</strong>', '<strong>It is NOT an emergency service</strong>', '<strong>NÃO é um serviço de emergência</strong>'],
                __('portal.emergency.body', ['name' => config('proyecto.nombre')])
            ) !!}
        </p>
    </div>
</section>
