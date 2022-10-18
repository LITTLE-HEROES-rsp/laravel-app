<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles =  Article::where('confirmed', true)->get();
        return view('articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('articles.create');
    }

    /**
     * 登録確認画面の表示
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createConfirm(Request $request)
    {
        // バリデータの作成
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:5'],
            'content' => 'required|string|max:5000',
        ], customAttributes:[
            'content' => '記事内容'
        ]);
        // バリデーション
        // 失敗したら422エラーを返す
        $validator->validate();
        // 確認済みのデータ
        // バリデーションに書いてあるパラメータのみを取得
        $data = $validator->safe();

        // ユーザー入力値を代入（Articleの「$fillable」に定義された値のみ代入される）
        $article = Article::make($data->all());
        // 送信ユーザーを取得
        $user = $request->user();
        // 記事の作成者を$userに紐付ける
        $article->user()->associate($user);
        // 保存
        $article->save();

        return view('articles.create_confirm', compact('article'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param \App\models\Article $article
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Article $article)
    {
        $user = $request->user();

        if ($article->user_id !== $user->id) {
            // ダッシュボードに遷移
            return redirect()->route('dashboard');
        }

        // 公開確認完了
        $article->confirmed = true;
        // 保存
        $article->save();

        // ダッシュボードに遷移
        return redirect()->route('dashboard');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        if (Auth::id() !== $article->user_id && (!$article->confirmed || $article->trashed())) {
            abort(404);
        }
        return view('articles.show', compact('article'));
    }

    /**
     * 自分が所有する記事一覧
     *
     * @return \Illuminate\Http\Response
     */
    public function myPage()
    {
        $articles = Article::where('user_id', Auth::id())->get();
        $title = 'マイページ';
        return view('articles.index', compact('articles', 'title'));
    }

    /**
     * 自分が所有する記事のゴミ箱
     *
     * @return \Illuminate\Http\Response
     */
    public function garbage()
    {
        $articles = Article::where('user_id', Auth::id())->onlyTrashed()->get();
        $title = 'ゴミ箱';
        return view('articles.index', compact('articles', 'title'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function edit(Article $article)
    {
        if ($article->user_id !== Auth::id()) {
            return redirect()->route('dashboard');
        }
        return view('articles.edit', compact('article'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        if ($article->user_id !== Auth::id()) {
            return redirect()->route('dashboard');
        }

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'content' => 'required|string|max:5000',
        ], customAttributes:[
            'content' => '記事内容'
        ]);
        $data = $validator->validate();

        $article->fill($data);
        $article->save();

        return redirect()->route('dashboard');
    }

    /**
     * 削除確認
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroyConfirm(Article $article)
    {
        // 本人確認
        if ($article->user_id !== Auth::id()) {
            return redirect()->route('dashboard');
        }

        // 削除確認画面を表示
        return view('articles.destroy_confirm', compact('article'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        // 本人確認
        if ($article->user_id !== Auth::id()) {
            return redirect()->route('dashboard');
        }

        // 削除
        $article->delete();

        // マイページに遷移
        return redirect()->route('articles.index.mypage');
    }
    /**
     * 削除済みデータの復元
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function restore(Article $article)
    {
        // 本人確認
        if ($article->user_id !== Auth::id()) {
            return redirect()->route('dashboard');
        }

        // 復元
        $article->restore();

        // マイページに遷移
        return redirect()->route('article.index.mypage');
    }

    /**
     * 完全削除確認
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function forceDeleteConfirm(Article $article)
    {
        // 本人確認
        if ($article->user_id !== Auth::id()) {
            return redirect()->route('dashboard');
        }

        // 削除確認画面に遷移
        return view('articles.force_delete_confirm', compact('article'));
    }

    /**
     * 完全削除
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function forceDelete(Article $article)
    {
        // 本人確認
        if ($article->user_id !== Auth::id()) {
            return redirect()->route('dashboard');
        }

        // 削除
        $article->forceDelete();

        // マイページに遷移
        return redirect()->route('articles.index.mypage');
    }
}
