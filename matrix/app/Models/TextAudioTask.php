<?php

namespace Matrix\Models;

class TextAudioTask extends BaseModel
{
    //
    protected $fillable = ['title', 'content', 'status', 'user_id'];

    public function createTask(array $taskData)
    {
        $task = self::create($taskData);
        return $task->toArray();
    }
}
