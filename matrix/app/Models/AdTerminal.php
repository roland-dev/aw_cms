<?php

namespace Matrix\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;


class AdTerminal extends BaseModel
{
    use SoftDeletes;
    protected $datas = ['deleted_at'];
    protected $fillable = [
        'ad_id',
        'terminal_code'
    ];

    public function createAdTerminal(array $adTerminal)
    {

        try {
            $adTerminalNum = self::where($adTerminal)->count();
            if ($adTerminalNum === 0) {
                $adTerminalObj = self::create($adTerminal);
            } else {
                return [];
            }
        } catch (Exception $e) {
            $e->getMessage();
            $adTerminalObj = NULL;
        }
        
        return empty($adTerminalObj) ? [] : $adTerminalObj->toArray();
    }

    public function delAdTerminal(int $adId)
    {
        $result = false;

        try {
            $result = self::where('ad_id', $adId)->delete();
        } catch (Exception $e) {
            $e->getMessage();
        }

        return $result;
    }

    public function getAdTerminals(int $adId)
    {
        $adTerminalsObj = self::where('ad_id', $adId)->pluck('terminal_code')->toArray();
        return $adTerminalsObj;
    }

    public function getAdIdsOfTerminalCode(string $terminalCode)
    {
        $adIds = self::where('terminal_code', $terminalCode)->pluck('ad_id')->toArray();
        return $adIds;
    }
}