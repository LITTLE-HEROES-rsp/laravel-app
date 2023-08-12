<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Traits\Csv;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use Csv;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 記事一覧の取得（公開済のものだけ）
        $articles = Article::where('confirmed', true)->paginate(15);
        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        // 閲覧に制限はつけません
        return view('admin.articles.show', compact('article'));
    }

    /**
     * CSVダウンロード
     *
     * @return \Illuminate\Http\Response
     */
    public function download()
    {
        // 記事一覧の取得（公開済のものだけ）
        $articles = Article::where('confirmed', true)->get();

        // 出力する情報
        $csvHeaders = [
            'id', 'title', 'created_at', 'updated_at', 'user_id', 'confirmed', 'deleted_at'
        ];

        return $this->downloadCsv($articles, $csvHeaders);
    }
}
