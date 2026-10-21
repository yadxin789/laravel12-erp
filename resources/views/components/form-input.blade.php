<div class="mb-4">
    @if($label ?? false)
    <label class="block text-gray-700 mb-2">{{ $label }}</label>
    @endif
    <input 
        type="{{ $type ?? 'text' }}" 
        name="{{ $name }}" 
        class="w-full px-4 py-2 border rounded {{ $errors->has($name) ? 'border-red-500' : '' }}"
        value="{{ old($name, $value ?? '') }}"
        placeholder="{{ $placeholder ?? '' }}"
        {{ ($required ?? false) ? 'required' : '' }}
        {{ ($readonly ?? false) ? 'readonly' : '' }}
        {{ $attributes }}
    >
    @error($name)
    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
