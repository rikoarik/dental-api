<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    use ApiResponser;

    public function index()
    {
        return $this->success(Article::latest()->get(), 'Data artikel berhasil dimuat');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_published' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['title', 'content', 'is_published']);
        $data['slug'] = Str::slug($request->title) . '-' . time();
        $article = Article::create($data);

        if ($request->hasFile('image')) {
            $article->addMediaFromRequest('image')->toMediaCollection('cover_image');
        }

        return $this->success($article, 'Artikel berhasil dibuat', 201);
    }

    public function show($id)
    {
        $article = Article::findOrFail($id);
        return $this->success($article, 'Detail artikel');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
            'is_published' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $article = Article::findOrFail($id);
        
        $data = $request->only(['title', 'content', 'is_published']);
        if ($request->has('title') && $request->title !== $article->title) {
            $data['slug'] = Str::slug($request->title) . '-' . time();
        }
        
        $article->update($data);

        if ($request->hasFile('image')) {
            $article->clearMediaCollection('cover_image');
            $article->addMediaFromRequest('image')->toMediaCollection('cover_image');
        }

        return $this->success($article, 'Artikel berhasil diperbarui');
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->clearMediaCollection('cover_image');
        $article->delete();

        return $this->success(null, 'Artikel berhasil dihapus');
    }
}
