<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SwaggerController extends Controller
{
    public function index(): View
    {
        return view('swagger.index');
    }

    public function spec(Request $request): Response
    {
        $yaml = file_get_contents(base_path('docs/openapi.yaml'));

        $currentServer = rtrim($request->getSchemeAndHttpHost(), '/').'/api';

        // Sisipkan server aktif (dari host Swagger saat ini) sebagai pilihan pertama
        $dynamicServer = "  -\n    url: \"{$currentServer}\"\n    description: \"Host aktif — otomatis dari URL Swagger ini\"\n";

        $yaml = preg_replace(
            '/^servers:\n/m',
            "servers:\n{$dynamicServer}",
            $yaml,
            1
        );

        return response($yaml, 200, [
            'Content-Type' => 'application/yaml',
        ]);
    }
}
