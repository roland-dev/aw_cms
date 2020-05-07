<?php

namespace Matrix\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends BaseModel
{
    //
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'category_code', 'sub_category_code', 'title', 'summary', 'description', 'content', 'audio_url', 'teacher_id', 'modify_user_id', 'show', 'read', 'cover_url', 'published_at', 'ad_guide', 'is_push_qywx'
    ];

    public function getArticleList($cond, $index = -1, $pageSize = -1)
    {
        $model = self::orderBy('created_at', 'desc');
        foreach ($cond as $k => $v) {
            if (in_array($k, ['category_code', 'sub_category_code', 'show'])) {
                $model = $model->where($k, '=', $v);
            }
            if ($k == 'title') {
                $model = $model->where($k, 'like', "%$v%");
            }
            if ($k == 'teacher_id') {
                $model = $model->whereIn($k, $v);
            }
            if ($k == 'published_at'){//用于文章推送定时任务,获取早于当前时间的文件记录进行查看
                $model = $model->where($k, '<', "$v");
            }
        }

        if ($index != -1 && $index != 0) {
            $model = $model->where('id', '<', $index);
        }

        if ($pageSize != -1) {
            $model = $model->take($pageSize);
        }

        $articleList = $model->orderBy('id', 'desc')->get();
        return empty($articleList) ? [] : $articleList->toArray();
    }

    public function updateShowStatus($articleId)
    {
        $article = self::findOrFail($articleId);
        $article->show = empty($article->show) ? 1 : 0;
        //$article->published_at = date('Y-m-d H:i:s');
        $article->save();

        return $article->toArray();
    }

    public function updatePushQywxStatus($articleId)
    {
        $article = self::findOrFail($articleId);
        $article->is_push_qywx = empty($article->is_push_qywx) ? 1 : 0;
        //$article->published_at = date('Y-m-d H:i:s');
        $article->save();

        return $article->toArray();
    }

    public function updateArticle($articleId, $articleData)
    {
        $article = self::findOrFail($articleId);
        foreach ($articleData as $k => $v) {
            $article->{$k} = $v;
        }
        $article->save();

        return $article->toArray();
    }

    public function trashArticle($articleId)
    {
        $article = self::findOrFail($articleId);
        $article->delete();
    }

    public function getArticleInfo($articleId)
    {
        $article = self::findOrFail($articleId);
        return $article->toArray();
    }

    public function getActiveArticleListBySubCategoryCode(string $code) {
        $articleList = self::where('sub_category_code', $code)->where('show', 1)->orderBy('published_at', 'desc')->get();
        return empty($articleList) ? [] : $articleList->toArray();
    }

    public function read($articleId)
    {
        self::where('id', $articleId)->increment('read');
        $article = self::where('id', $articleId)->take(1)->firstOrFail();
        return $article->toArray();
    }

    public function getUnfeedArticleList(array $categoryCodeList)
    {
        $articleList = self::whereIn('category_code', $categoryCodeList)->where('feed', 0)->where('published_at', '<', date('Y-m-d H:i:s'))->get();
        //self::whereIn('category_code', $categoryCodeList)->where('feed', 0)->where('qywx_push_at', '<', date('Y-m-d H:i:s'))->toSql();

        return empty($articleList) ? [] : $articleList->toArray();
    }

    public function setArticleFeed(array $articleIdList)
    {
        self::whereIn('id', $articleIdList)->update([
            'feed' => 1,
        ]);
    }
}
