<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\AlertThreshold;
use App\Services\AlertThresholdService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

/**
 * Página administrativa de calibración y centralización de umbrales de alertas y diagnósticos.
 * Permite al operador configurar parámetros independientes para los 3 grupos de roles:
 * 1. Infraestructura troncal (router, router_late, repeater)
 * 2. Clientes fijos y estándar (client, client_hidden, client_base)
 * 3. Dispositivos personales y mudos (client_mute, tracker, etc.)
 */
class GestionUmbralesAlertas extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.gestion-umbrales-alertas';

    /**
     * Pestaña activa actual en la interfaz ('router', 'client', 'client_mute').
     */
    public string $activeTab = 'router';

    /**
     * Matriz de umbrales editables en memoria Livewire.
     *
     * @var array<string, array<string, float|int|string>>
     */
    public array $umbrales = [];

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.coordinated_routers.nav_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.alert_thresholds.nav_label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin.alert_thresholds.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.alert_thresholds.subheading');
    }

    public function mount(AlertThresholdService $service): void
    {
        $this->umbrales = $service->getAllThresholds();
    }

    /**
     * Cambia la pestaña activa.
     */
    public function setActiveTab(string $tab): void
    {
        if (in_array($tab, AlertThreshold::GROUPS, true)) {
            $this->activeTab = $tab;
        }
    }

    /**
     * Guarda los umbrales configurados tras ejecutar validación cruzada y notifica a detector-alertas.
     */
    public function save(AlertThresholdService $service): void
    {
        $this->validateThresholds();

        $resultado = $service->saveThresholds($this->umbrales);

        if ($resultado['detector_notified']) {
            Notification::make()
                ->title(__('admin.alert_thresholds.saved_success'))
                ->body($resultado['message'])
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('admin.alert_thresholds.saved_local'))
                ->body($resultado['message'])
                ->warning()
                ->send();
        }
    }

    /**
     * Restablece los umbrales a los valores de fábrica predefinidos.
     */
    public function resetDefaults(AlertThresholdService $service): void
    {
        $resultado = $service->resetToDefaults();
        $this->umbrales = $service->getAllThresholds();

        Notification::make()
            ->title(__('admin.alert_thresholds.reset_success'))
            ->body($resultado['message'])
            ->success()
            ->send();
    }

    /**
     * Realiza validaciones lógicas cruzadas en los umbrales ingresados.
     */
    protected function validateThresholds(): void
    {
        foreach (AlertThreshold::GROUPS as $group) {
            $params = $this->umbrales[$group] ?? [];

            // 1. Valores numéricos no pueden ser negativos
            foreach ($params as $paramKey => $val) {
                if (is_numeric($val) && (float) $val < 0) {
                    throw ValidationException::withMessages([
                        "umbrales.{$group}.{$paramKey}" => __('admin.alert_thresholds.validation_positive'),
                    ]);
                }
            }

            // 2. Saltos LoRa: rango [1, 7] y orden hop_rec <= hop_warn <= hop_crit
            $hopRec = (float) ($params['hop_limit_recommended'] ?? 0);
            $hopWarn = (float) ($params['hop_limit_warning'] ?? 0);
            $hopCrit = (float) ($params['hop_limit_critical'] ?? 0);

            if ($hopRec < 1 || $hopRec > 7) {
                throw ValidationException::withMessages([
                    "umbrales.{$group}.hop_limit_recommended" => __('admin.alert_thresholds.validation_hops_range'),
                ]);
            }
            if ($hopWarn < 1 || $hopWarn > 7) {
                throw ValidationException::withMessages([
                    "umbrales.{$group}.hop_limit_warning" => __('admin.alert_thresholds.validation_hops_range'),
                ]);
            }
            if ($hopCrit < 1 || $hopCrit > 7) {
                throw ValidationException::withMessages([
                    "umbrales.{$group}.hop_limit_critical" => __('admin.alert_thresholds.validation_hops_range'),
                ]);
            }

            if ($hopRec > $hopWarn) {
                throw ValidationException::withMessages([
                    "umbrales.{$group}.hop_limit_recommended" => __('admin.alert_thresholds.validation_hops_rec_order'),
                ]);
            }
            if ($hopWarn > $hopCrit) {
                throw ValidationException::withMessages([
                    "umbrales.{$group}.hop_limit_warning" => __('admin.alert_thresholds.validation_hops_order'),
                ]);
            }

            // 3. Batería: rango [0, 100] y orden bat_crit <= bat_warn <= bat_res
            $batWarn = (float) ($params['battery_warning_pct'] ?? 0);
            $batCrit = (float) ($params['battery_critical_pct'] ?? 0);
            $batRes = (float) ($params['battery_resolution_pct'] ?? 0);
            $sunset = (float) ($params['sunset_battery_min_pct'] ?? 0);

            foreach ([
                'battery_warning_pct' => $batWarn,
                'battery_critical_pct' => $batCrit,
                'battery_resolution_pct' => $batRes,
                'sunset_battery_min_pct' => $sunset,
            ] as $batKey => $batVal) {
                if ($batVal < 0 || $batVal > 100) {
                    throw ValidationException::withMessages([
                        "umbrales.{$group}.{$batKey}" => __('admin.alert_thresholds.validation_percent_range'),
                    ]);
                }
            }

            if ($batWarn < $batCrit) {
                throw ValidationException::withMessages([
                    "umbrales.{$group}.battery_warning_pct" => __('admin.alert_thresholds.validation_battery_order'),
                ]);
            }

            if ($batRes < $batWarn) {
                throw ValidationException::withMessages([
                    "umbrales.{$group}.battery_resolution_pct" => __('admin.alert_thresholds.validation_battery_res'),
                ]);
            }

            // 4. Silencios: aviso <= crítico si crítico está activo
            $silWarn = (float) ($params['silence_warning_h'] ?? 0);
            $silCrit = (float) ($params['silence_critical_h'] ?? 0);
            if ($silCrit > 0 && $silWarn > $silCrit) {
                throw ValidationException::withMessages([
                    "umbrales.{$group}.silence_warning_h" => __('admin.alert_thresholds.validation_silence_order'),
                ]);
            }

            // 5. Reinicios: 5m <= 10m
            $reb5m = (float) ($params['reboot_loop_count_5m'] ?? 0);
            $reb10m = (float) ($params['reboot_loop_count_10m'] ?? 0);
            if ($reb5m > $reb10m) {
                throw ValidationException::withMessages([
                    "umbrales.{$group}.reboot_loop_count_5m" => __('admin.alert_thresholds.validation_reboots_order'),
                ]);
            }

            // 6. NodeInfo: mínimo <= recomendado
            $nodeMin = (float) ($params['nodeinfo_interval_min_s'] ?? 0);
            $nodeRec = (float) ($params['nodeinfo_interval_recommended_s'] ?? 0);
            if ($nodeMin > $nodeRec) {
                throw ValidationException::withMessages([
                    "umbrales.{$group}.nodeinfo_interval_min_s" => __('admin.alert_thresholds.validation_nodeinfo_order'),
                ]);
            }

            // 7. Telemetría: mínimo <= recomendado
            $telemMin = (float) ($params['telemetry_interval_min_s'] ?? 0);
            $telemRec = (float) ($params['telemetry_interval_recommended_s'] ?? 0);
            if ($telemMin > $telemRec) {
                throw ValidationException::withMessages([
                    "umbrales.{$group}.telemetry_interval_min_s" => __('admin.alert_thresholds.validation_telemetry_order'),
                ]);
            }
        }
    }

    /**
     * Acciones de cabecera en la página.
     *
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetDefaultsAction')
                ->label(__('admin.alert_thresholds.btn_reset'))
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('admin.alert_thresholds.reset_modal_heading'))
                ->modalDescription(__('admin.alert_thresholds.reset_modal_desc'))
                ->modalSubmitActionLabel(__('admin.alert_thresholds.btn_reset'))
                ->action(fn (AlertThresholdService $service) => $this->resetDefaults($service)),
        ];
    }
}
