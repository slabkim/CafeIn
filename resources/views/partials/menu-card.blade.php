<div class="menu-card menu-item" data-category="{{ $slug ?? '' }}" data-menu-id="{{ $menu->id }}"
    data-menu-name="{{ $menu->name }}" data-menu-desc="{{ $menu->description ?? 'Menu favorit kami.' }}"
    data-menu-price="{{ (float) $menu->price }}"
    data-menu-image="{{ $menu->image ? Storage::url($menu->image) : '' }}" data-menu-stock="{{ $menu->stock }}"
    data-menu-category="{{ $menu->category?->name }}" data-menu-meta-prep="{{ $menu->metadata['prep_time'] ?? '' }}"
    data-menu-meta-size="{{ $menu->metadata['serving_size'] ?? '' }}"
    data-menu-meta-cal="{{ $menu->metadata['calories'] ?? '' }}">
    <div class="menu-card-image">
        @if ($menu->image)
            <img src="{{ Storage::url($menu->image) }}" alt="{{ $menu->name }}" loading="lazy">
        @else
            <img src="/placeholder.svg?height=200&width=300" alt="{{ $menu->name }}" loading="lazy">
        @endif
        @if ($menu->stock < 5 && $menu->stock > 0)
            <span class="stock-badge warning">Sisa {{ $menu->stock }}</span>
        @elseif($menu->stock < 1)
            <span class="stock-badge danger">Habis</span>
        @endif
    </div>
    <div class="menu-card-content menu-item-content"
        @if (!$isAdmin) role="button" tabindex="0" aria-label="Detail {{ $menu->name }}" @endif>
        <span class="menu-card-category">{{ $menu->category?->name ?? 'Menu' }}</span>
        <h3>{{ $menu->name }}</h3>
        <p>{{ \Illuminate\Support\Str::limit($menu->description ?? 'Menu favorit kami.', 50) }}</p>
        <div class="menu-card-footer">
            <span class="menu-card-price">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
            @if ($isAdmin)
                <div class="admin-actions">
                    <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-sm btn-outline">Edit</a>
                    <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST"
                        onsubmit="return confirm('Hapus menu ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </div>
            @else
                <button class="btn-add-quick btn-add" data-menu-id="{{ $menu->id }}"
                    {{ $menu->stock < 1 ? 'disabled' : '' }}>
                    @if ($menu->stock < 1)
                        <span>Habis</span>
                    @else
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                    @endif
                </button>
            @endif
        </div>
    </div>
</div>
