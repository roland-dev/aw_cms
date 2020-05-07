<?php

namespace Matrix\Models;

class SystemNotice extends BaseModel
{
    //
    const TARGET_MANAGE = 1;
    const TARGET_CLIENT = 2;

    const NOTICE_LIMIT = 20;

    const NOTICE_CONTENT_TWITTER_REQUEST = '%s的关注申请已提交，请耐心等待审核。';
    const NOTICE_CONTENT_TWITTER_PROCESS_APPROVED = '恭喜您，%s的关注申请已通过。';
    const NOTICE_CONTENT_TWITTER_PROCESS_DENIED = '嘤嘤嘤，%s的关注申请被拒绝。';
    const NOTICE_CONTENT_TWITTER_REVIEW_DENIED = '嘤嘤嘤，%s的服务体验已经到期停止。';

    const NOTICE_CONTENT_PM_REQUEST = '%s的私信聊天申请已提交，请耐心等待审核。';
    const NOTICE_CONTENT_PM_PROCESS_APPROVED = '恭喜您，%s的私信聊天申请已通过。';
    const NOTICE_CONTENT_PM_PROCESS_DENIED = '嘤嘤嘤，%s的私信聊天申请被拒绝。';

    protected $fillable = ['title', 'content', 'target', 'user_id', 'open_id', 'read'];

    public function noticeCustomer(string $title = '', string $content = '', string $openId = '')
    {
        $systemNotice = self::create([
            'title' => $title,
            'content' => $content,
            'target' => self::TARGET_CLIENT,
            'user_id' => 0,
            'open_id' => $openId,
            'read' => 0,
        ]);

        return $systemNotice->toArray();
    }

    public function noticeManager(string $title = '', string $content = '', int $userId = 0)
    {
        $systemNotice = self::create([
            'title' => $title,
            'content' => $content,
            'target' => TARGET_MANAGE,
            'user_id' => $userId,
            'open_id' => '',
            'read' => 0,
        ]);

        return $systemNotice->toArray();
    }

    public function getSystemNoticeList(int $target, $personKey)
    {
        $condition = [];
        $condition['target'] = $target;
        switch ($target) {
            case self::TARGET_MANAGE:
                $condition['user_id'] = $personKey;
                break;
            case self::TARGET_CLIENT:
                $condition['open_id'] = $personKey;
                break;
            default:
                $condition = [];
        }

        if (empty($condition)) {
            $ret = [];
            return $ret;
        }

        $systemNoticeList = self::where($condition)->orderBy('created_at', 'desc')->take(self::NOTICE_LIMIT)->get();

        return empty($systemNoticeList) ? [] : $systemNoticeList->toArray();
    }

    public function readSystemNotice(int $target, $personKey, int $systemNoticeId)
    {
        $systemNotice = self::findOrFail($systemNoticeId);
        if ($systemNotice->target != $target) {
            abort(403);
        }
        if ($target == self::TARGET_MANAGE) {
            if ($systemNotice->user_id != $personKey) {
                abort(403);
            }
        } elseif ($target == self::TARGET_CLIENT) {
            if ($systemNotice->open_id != $personKey) {
                abort(403);
            }
        } else {
            abort(403);
        }
        $systemNotice->read = 1;
        $systemNotice->save();
    }
}
