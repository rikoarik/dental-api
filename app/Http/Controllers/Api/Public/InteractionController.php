<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Article;
use App\Traits\ApiResponser;

class InteractionController extends Controller
{
    use ApiResponser;

    public function likeNews($id)
    {
        $news = News::findOrFail($id);
        $news->increment('like_count');
        return $this->success(null, 'Berita berhasil disukai.');
    }

    public function likeArticle($slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        $article->increment('like_count');
        return $this->success(null, 'Artikel berhasil disukai.');
    }

    public function viewArticle($slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        $article->increment('view_count');
        return $this->success($article, 'Detail artikel.');
    }
}