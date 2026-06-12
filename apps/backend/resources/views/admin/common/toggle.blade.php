<div>
    <label class="switch">
        <input type="checkbox"
            @foreach ($attributes ?? [] as $key => $value)
                {{ $key }}="{{ $value }}" 
            @endforeach
            @checked($status ?? false)
            @disabled($disabled ?? false)
        />
        <span class="slider" for="status"></span>
    </label>
</div>