<?php

namespace App\Http\Controllers\Eden;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

abstract class EdenController extends Controller
{
    protected function page(string $view, ?string $title = null, ?string $scripts = null, array $data = []): Response
    {
        $contentView = view('eden.' . $view, $data);
        return response()->view('eden.layout', [
            'title' => $title,
            'content' => $contentView->render(),
            'scripts' => $scripts ? view('eden.' . $scripts)->render() : '',
        ]);
    }
}
