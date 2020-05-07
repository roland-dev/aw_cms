<?php

namespace Matrix\Models;

class ArticleReply extends BaseModel
{
    //
    const STATUS_NEW     = 10;
    const STATUS_APPROVE = 20;
    const STATUS_DENIED  = 30;

    const PLACED_TOP = 1;
    const UN_PLACED_TOP = 0;

    protected $fillable = ['open_id', 'session_id', 'type', 'article_id', 'article_title', 'article_author_user_id', 'content', 'ref_id', 'ref_content', 'ref_open_id', 'status', 'examine_user_id', 'examine_at', 'created_at', 'updated_at', 'is_all_visible', 'placed_status', 'placed_at', 'forward_to_twitter'];
}
