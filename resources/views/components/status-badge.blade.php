@php
    $class = match ($status) {
        'draft' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
        'submitted' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
        'approved' => 'bg-success-subtle text-success-emphasis border-success-subtle',
        'released' => 'bg-info-subtle text-info-emphasis border-info-subtle',
        'ready' => 'bg-info-subtle text-info-emphasis border-info-subtle',
        'in_progress' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
        'paused' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
        'completed' => 'bg-success-subtle text-success-emphasis border-success-subtle',
        'closed' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
        'cancelled' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
        'confirmed' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
        'partially_delivered', 'partially_received' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
        'fully_delivered', 'fully_received' => 'bg-success-subtle text-success-emphasis border-success-subtle',
        'material_shortage' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
        'won' => 'bg-success-subtle text-success-emphasis border-success-subtle',
        'lost' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
        'expired' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
        'change_pending' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
        'rework' => 'bg-info-subtle text-info-emphasis border-info-subtle',
        default => 'bg-light text-secondary border',
    };
@endphp

<span class="badge border {{ $class }}">{{ $status }}</span>
