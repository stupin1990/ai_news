<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

abstract class Controller
{
    protected function react(string $page, array $props = []): View
    {
        return view('layouts.app', [
            'page' => $page,
            'props' => $props,
        ]);
    }
}
