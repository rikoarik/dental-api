<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    use ApiResponser;

    public function index()
    {
        return $this->success(News::latest()->get(), 'Data berita berhasil dimuat');
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
        $news = News::create($data);

        if ($request->hasFile('image')) {
            $news->addMediaFromRequest('image')->toMediaCollection('cover_image');
        }

        return $this->success($news, 'Berita berhasil dibuat', 201);
    }

    public function show($id)
    {
        $news = News::findOrFail($id);
        return $this->success($news, 'Detail berita');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
            'is_published' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $news = News::findOrFail($id);
        
        $data = $request->only(['title', 'content', 'is_published']);
        if ($request->has('title') && $request->title !== $news->title) {
            $data['slug'] = Str::slug($request->title) . '-' . time();
        }
        
        $news->update($data);

        if ($request->hasFile('image')) {
            $news->clearMediaCollection('cover_image');
            $news->addMediaFromRequest('image')->toMediaCollection('cover_image');
        }

        return $this->success($news, 'Berita berhasil diperbarui');
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->clearMediaCollection('cover_image');
        $news->delete();

        return $this->success(null, 'Berita berhasil dihapus');
    }
}
