<?php

namespace App\View\Components;

use Illuminate\View\Component;

class StatusBadge extends Component
{
    public string $color;
    public string $label;

    public function __construct(string $status)
    {
        $map = [
            'pendiente'  => ['color' => 'bg-red-500/20 text-red-400 border-red-500/30',   'label' => 'Pendiente'],
            'preparando' => ['color' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30', 'label' => 'Preparando'],
            'listo'      => ['color' => 'bg-green-500/20 text-green-400 border-green-500/30',  'label' => 'Listo ✓'],
            'entregado'  => ['color' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',   'label' => 'Entregado'],
            'pagado'     => ['color' => 'bg-gray-500/20 text-gray-400 border-gray-500/30',   'label' => 'Pagado'],
        ];

        $this->color = $map[$status]['color'] ?? 'bg-gray-500/20 text-gray-400';
        $this->label = $map[$status]['label'] ?? $status;
    }

    public function render()
    {
        return view('components.status-badge');
    }
}