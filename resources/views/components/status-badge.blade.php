<span class="px-2 py-1 rounded text-xs font-medium
    @switch($status)
        @case('draft') bg-gray-100 text-gray-800 @break
        @case('submitted') bg-blue-100 text-blue-800 @break
        @case('approved') bg-green-100 text-green-800 @break
        @case('released') bg-purple-100 text-purple-800 @break
        @case('ready') bg-teal-100 text-teal-800 @break
        @case('in_progress') bg-orange-100 text-orange-800 @break
        @case('paused') bg-yellow-100 text-yellow-800 @break
        @case('completed') bg-green-100 text-green-800 @break
        @case('closed') bg-gray-100 text-gray-800 @break
        @case('cancelled') bg-red-100 text-red-800 @break
        @case('confirmed') bg-blue-100 text-blue-800 @break
        @case('partially_delivered') bg-yellow-100 text-yellow-800 @break
        @case('fully_delivered') bg-green-100 text-green-800 @break
        @case('partially_received') bg-yellow-100 text-yellow-800 @break
        @case('fully_received') bg-green-100 text-green-800 @break
        @case('material_shortage') bg-red-100 text-red-800 @break
        @case('won') bg-green-100 text-green-800 @break
        @case('lost') bg-red-100 text-red-800 @break
        @case('expired') bg-gray-100 text-gray-800 @break
        @case('change_pending') bg-yellow-100 text-yellow-800 @break
        @case('rework') bg-purple-100 text-purple-800 @break
        @default bg-gray-100 text-gray-800
    @endswitch
">
    {{ $status }}
</span>
