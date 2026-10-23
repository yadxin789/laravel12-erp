<div class="mb-3">
    @if($label ?? false)
        <label class="form-label" for="{{ $name }}">{{ $label }}</label>
    @endif
    <input
        type="{{ $type ?? 'text' }}"
        id="{{ $name }}"
        name="{{ $name }}"
        class="form-control {{ $errors->has($name) ? 'is-invalid' : '' }}"
        value="{{ old($name, $value ?? '') }}"
        placeholder="{{ $placeholder ?? '' }}"
        {{ ($required ?? false) ? 'required' : '' }}
        {{ ($readonly ?? false) ? 'readonly' : '' }}
        {{ $attributes }}
    >
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
