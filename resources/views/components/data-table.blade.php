<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">{{ $title }}</h2>
        @if($createRoute ?? false)
        <a href="{{ $createRoute }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建</a>
        @endif
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                {{ $header }}
            </tr>
        </thead>
        <tbody>
            {{ $body }}
        </tbody>
    </table>
    @if(isset($pagination))
    <div class="mt-4">
        {{ $pagination }}
    </div>
    @endif
</div>
