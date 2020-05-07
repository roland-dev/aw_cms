<?php

namespace Matrix\Models;

class Log extends BaseModel
{

   protected $fillable = [
       'source_key',
       'user_id',
       'original_data',
       'operate',
       'created_at'
   ];

   public $timestamps = false;

   public function show()
   {
       $logList = self::all();
       return empty($logList) ? [] : $logList->toArray();
   }

   public function createLog(string $sourceKey, int $userId, string $originalData, string $operate)
   {
       $logData = [
           'source_key' => $sourceKey,
           'user_id' => $userId,
           'original_data' => $originalData,
           'operate' => $operate,
           'created_at' => date('Y:m:d h:m:s')
       ];
       self::create($logData);
   }

}
