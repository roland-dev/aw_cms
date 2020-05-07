<?php

namespace Matrix\Models;

class ArticleRead extends BaseModel
{
    //
    public function record(array $record)
    {
        $articleRead = self::updateOrCreate($record, [
            'open_id' => array_get($record, 'open_id'),
            'article_id' => array_get($record, 'article_id'),
            'type' => array_get($record, 'type'),
        ]);
        return $articleRead->toArray();
    }
}
