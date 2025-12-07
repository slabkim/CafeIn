<?php


namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuImage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function __invoke(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        $categories = Category::with(['menus' => function ($query) {
            $query->where('is_active', true)->orderBy('name');
        }])->orderBy('name')->get();

        $results = collect();
        if ($q !== '' || $categoryId || $minPrice !== null || $maxPrice !== null) {
            $lq = mb_strtolower($q);
            $resultsQuery = Menu::with('category')
                ->where('is_active', true)
                ->when($q !== '', function ($qb) use ($lq) {
                    $qb->where(function ($w) use ($lq) {
                        $w->whereRaw('LOWER(name) LIKE ?', ["%{$lq}%"])
                          ->orWhereRaw('LOWER(description) LIKE ?', ["%{$lq}%"]);
                    });
                })
                ->when($categoryId, function ($qb) use ($categoryId) {
                    $qb->where('category_id', $categoryId);
                })
                ->when($minPrice !== null && $minPrice !== '', function ($qb) use ($minPrice) {
                    $qb->where('price', '>=', (float) $minPrice);
                })
                ->when($maxPrice !== null && $maxPrice !== '', function ($qb) use ($maxPrice) {
                    $qb->where('price', '<=', (float) $maxPrice);
                })
                ->orderBy('name')
                ->limit(48);

            $results = $resultsQuery->get();
        }

        // If client requests JSON (live search), return lightweight payload
        if ($request->wantsJson() || $request->boolean('ajax')) {
            $payload = $results->map(function (Menu $menu) {
                return [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'description' => $menu->description,
                    'price' => (float) $menu->price,
                    'stock' => (int) $menu->stock,
                    'category' => $menu->category?->name,
                    'image_url' => $menu->image ? Storage::url($menu->image) : null,
                ];
            });
            return response()->json([
                'results' => $payload,
                'count' => $payload->count(),
                'q' => $q,
            ]);
        }

        return view('menus', [
            'categories' => $categories,
            'searchTerm' => $q,
            'results' => $results,
            'selectedCategory' => $categoryId,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ]);
    }

    public function adminIndex(Request $request): View
    {
        $categories = Category::orderBy('name')->get();

        $query = Menu::with('category');
        $q = $request->string('q')->toString();
        $categoryId = $request->input('category');

        if ($q) {
            $lq = mb_strtolower($q);
            $query->where(function ($qb) use ($lq) {
                $qb->whereRaw('LOWER(name) LIKE ?', ["%{$lq}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$lq}%"]);
            });
        }
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $menus = $query->orderBy('name')->paginate(12)->withQueryString();

        return view('admin.menus.index', [
            'menus' => $menus,
            'categories' => $categories,
            'filter' => [
                'q' => $q,
                'category' => $categoryId,
            ],
        ]);
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.menus.create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $disk = Storage::disk(config('filesystems.default'));

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('menus', config('filesystems.default'));
            $validated['image'] = $path;
        }

        Menu::create($validated);

        return redirect()->route('menus')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu): View
    {
        $categories = Category::orderBy('name')->get();
        $menu->load('images');

        return view('admin.menus.edit', [
            'menu' => $menu,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $disk = Storage::disk(config('filesystems.default'));

        // Handle cover image replace/remove
        if ($request->boolean('remove_image') && $menu->image && $disk->exists($menu->image)) {
            $disk->delete($menu->image);
            $validated['image'] = null;
        }
        if ($request->hasFile('image')) {
            if ($menu->image && $disk->exists($menu->image)) {
                $disk->delete($menu->image);
            }
            $validated['image'] = $request->file('image')->store('menus', config('filesystems.default'));
        }

        $menu->update($validated);

        // Handle additional gallery images
        if ($request->hasFile('images')) {
            foreach ((array) $request->file('images') as $file) {
                if (! $file) continue;
                $path = $file->store('menus/gallery', config('filesystems.default'));
                MenuImage::create([
                    'menu_id' => $menu->id,
                    'path' => $path,
                ]);
            }
        }

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        // Prevent delete if referenced in order_items due to FK restrictions
        if ($menu->orderItems()->exists()) {
            return back()->with('error', 'Menu tidak dapat dihapus karena sudah digunakan pada order. Nonaktifkan saja menu ini.');
        }

        $disk = Storage::disk(config('filesystems.default'));

        // Delete cover image
        if ($menu->image && $disk->exists($menu->image)) {
            $disk->delete($menu->image);
        }
        // Delete gallery images
        foreach ($menu->images as $img) {
            if ($img->path && $disk->exists($img->path)) {
                $disk->delete($img->path);
            }
        }
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu berhasil dihapus.');
    }

    public function deleteImage(Menu $menu, MenuImage $image): RedirectResponse
    {
        if ($image->menu_id !== $menu->id) {
            abort(404);
        }
        if ($image->path && Storage::disk(config('filesystems.default'))->exists($image->path)) {
            Storage::disk(config('filesystems.default'))->delete($image->path);
        }
        $image->delete();

        return back()->with('success', 'Gambar berhasil dihapus.');
    }

    public function toggleActive(Menu $menu): RedirectResponse
    {
        $menu->update(['is_active' => ! $menu->is_active]);
        return back()->with('success', 'Status menu diperbarui.');
    }
}
