<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $popularMenus = Menu::with('category')
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        return view('home', [
            'popularMenus' => $popularMenus,
        ]);
    }
}
