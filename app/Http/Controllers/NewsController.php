<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        return $this->react('NewsPage', [
            'appName' => config('app.name', 'Ai News'),
            'userName' => (string) $request->user()?->name,
            'routes' => [
                'logout' => route('logout'),
            ],
        ]);
    }
}
