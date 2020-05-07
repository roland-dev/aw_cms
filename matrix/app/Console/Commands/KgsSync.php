<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;
use Matrix\Models\KgsHistory;
use Matrix\Models\KgsUser;
use Matrix\Models\KgsRoom;
use Matrix\Models\KgsVote;
use Matrix\Models\Ucenter;
use Matrix\Models\Teacher;
use Matrix\Models\Twitter;
use Matrix\Models\ArticleLike;
use Matrix\Models\LikeStatistic;
use Matrix\Models\Customer;
use Matrix\Contracts\UcManager;
use Exception;
use Log;
use DB;

class KgsSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kgs:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the kgs system fail to send data.';

    protected $uc;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UcManager $uc)
    {
        $this->uc = $uc;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        // Get room id list.
        $roomIdList = config('kgs.room_id');

        $startTimestamp = 1556640000000;
        $endTimestamp = (time() - 120) * 1000;
        $historyList = KgsHistory::whereBetween('timestamp', [$startTimestamp, $endTimestamp])
            ->where('cms_record_id', NULL)->orderBy('timestamp', 'desc')->get();

        $ownerObjectIdList = $historyList->pluck('owner');
        $ownerList = KgsUser::whereIn('_id', $ownerObjectIdList)->get()->toArray();

        $qyUserIdList = array_column($ownerList, 'userId');
        $ownerList = array_column($ownerList, NULL, '_id');

        $ucList = Ucenter::whereIn('enterprise_userid', $qyUserIdList)->get()->toArray();
        $ucMap = array_column($ucList, 'user_id', 'enterprise_userid');

        $teacherList = Teacher::whereIn('category_code', array_values($roomIdList))->get()->toArray();

        $twitterList = $historyList->map(function ($item, $key) use ($roomIdList, $ownerList, $ucMap, $teacherList) {
            $categoryCode = (string)array_get($roomIdList, (string)$item->roomId);
            if (!empty($categoryCode)) {
                $owner = array_get($ownerList, (string)$item->owner);
                if (!empty($owner)) {
                    $showTime = date('Y-m-d H:i:s', (int)$item->timestamp / 1000);
                    $userId = (int)array_get($ucMap, (string)$owner['userId']);
                    $teacherId = 0;
                    foreach ($teacherList as $teacher) {
                        if ($teacher['category_code'] == $categoryCode && $teacher['user_id'] == $userId) {
                            $teacherId = $teacher['id'];
                        }
                    }
                    if (!empty($teacherId)) {
                        $twitterData = [
                            'source_id' => (string)$item->_id,
                            'content' => $item->type == 'textMessage' ? (string)$item->content : (string)$item->tips,
                            'category_code' => $categoryCode,
                            'teacher_id' => $teacherId,
                            'created_at' => $showTime,
                            'updated_at' => $showTime,
                            'operator_user_id' => $userId,
                            'room_id' => (string)$item->roomId,
                            'image_url' => $item->type == 'textMessage' ? '' : (string)$item->content,
                        ];
                        return $twitterData;
                    }
                }
            }
        })->toArray();

        $twitterList = array_column($twitterList, NULL, 'source_id');
        $historyList->each(function ($item, $key) use ($twitterList) {
            $twitterData = array_get($twitterList, (string)$item->_id);
            if (empty($twitterData)) {
                return;
            }
            DB::beginTransaction();
            try {
                $twitter = Twitter::create($twitterData);
                $item->cms_record_id = $twitter->id;
                $item->save();
                Log::info($twitter);
                DB::commit();
            } catch (Exception $e) {
                Log::error('看高手同步失败.', [$e]);
                DB::rollBack();
            }
        });

        $voteList = KgsVote::where('cmsSync', NULL)->where('qyUserId', '<>', NULL)->whereBetween('createdAt', [$startTimestamp, $endTimestamp])->get();
        $msgIdList = $voteList->pluck('msgId')->toArray();

        $twitterList = Twitter::whereIn('source_id', $msgIdList)->whereIn('category_code', $roomIdList)->get()->toArray();
        $twitterMap = array_column($twitterList, NULL, 'source_id');

        foreach ($voteList as $vote) {
            $twitter = array_get($twitterMap, (string)$vote->msgId);
            if (empty($twitter)) {
                continue;
            }
            $qyUserId = (string)$vote->qyUserId;
            if (empty($qyUserId)) {
                continue;
            }
            $categoryCode = (string)array_get($roomIdList, (string)$vote->roomId);
            if (empty($categoryCode)) {
                continue;
            }
            $ucUserInfo = $this->uc->getUserInfoByQyUserid($qyUserId);

            $openId = (string)array_get($ucUserInfo, 'data.openId');
            if (empty($openId)) {
                continue;
            }

            $customer = Customer::where('open_id', $openId)->take(1)->first();
            if (empty($customer)) {
                $customer = Customer::create([
                    'open_id' => $openId,
                    'code' => (string)array_get($ucUserInfo, 'data.customerCode'),
                    'qy_userid' => $qyUserId,
                    'name' => (string)array_get($ucUserInfo, 'data.name'),
                    'mobile' => (string)array_get($ucUserInfo, 'data.mobile'),
                    'nickname' => (string)array_get($ucUserInfo, 'data.nickName'),
                    'icon_url' => (string)array_get($ucUserInfo, 'data.iconUrl'),
                    'created_at' => (string)date('Y-m-d H:i:s'),
                    'updated_at' => (string)date('Y-m-d H:i:s'),
                ]);
            } else {
                $customer->code = (string)array_get($ucUserInfo, 'data.customerCode');
                $customer->qy_userid = $qyUserId;
                $customer->name = (string)array_get($ucUserInfo, 'data.name');
                $customer->mobile = (string)array_get($ucUserInfo, 'data.mobile');
                $customer->nickname = (string)array_get($ucUserInfo, 'data.nickName');
                $customer->icon_url = (string)array_get($ucUserInfo, 'data.iconUrl');
                $customer->updated_at = (string)date('Y-m-d H:i:s');
                $customer->save();
            }

            $customerCode = (string)array_get($ucUserInfo, 'data.customerCode');

            $role = empty($customerCode) ? 'ROLE_STAFF' : 'ROLE_CUSTOMER';

            DB::beginTransaction();
            try {
                $articleLike = ArticleLike::where('article_id', $twitter['id'])->where('open_id', $openId)->where('type', 'twitter')->take(1)->first();
                $likeStatistic = LikeStatistic::where('type', 'twitter')->where('article_id', $twitter['id'])->take(1)->first();
                $showTime = date('Y-m-d H:i:s', (int)$vote->createdAt / 1000);
                if ($vote->score == 1 && empty($articleLike)) { // 1 = vote
                    $articleLike = ArticleLike::create([
                        'article_id' => $twitter['id'],
                        'open_id' => $openId,
                        'type' => 'twitter',
                        'udid' => '',
                        'user_type' => $role,
                        'session_id' => '',
                        'created_at' => $showTime,
                        'updated_at' => $showTime,
                    ]);
                    if (empty($likeStatistic)) {
                        $likeStatistic = LikeStatistic::create([
                            'article_id' => $twitter['id'],
                            'type' => 'twitter',
                            'like_sum' => 1,
                            'staff_like_sum' => (int)($role == 'ROLE_STAFF'),
                            'customer_like_sum' => (int)($role == 'ROLE_CUSTOMER'),
                            'created_at' => $showTime,
                            'updated_at' => $showTime,
                        ]);
                    } else {
                        $updateCond = [
                            'like_sum' => DB::raw('like_sum + 1'),
                            'updated_at' => $showTime,
                        ];
                        if ($role == 'ROLE_STAFF') {
                            $updateCond['staff_like_sum'] = DB::raw('staff_like_sum + 1');
                        } else {
                            $updateCond['customer_like_sum'] = DB::raw('customer_like_sum + 1');
                        }
                        LikeStatistic::where('type', 'twitter')->where('article_id', $twitter['id'])->update($updateCond);
                    }
                } elseif ($vote->score == -1 && !empty($articleLike)) { // -1 = unvote
                    $articleLike->delete();
                    if (!empty($likeStatistic)) {
                        $updateCond = [
                            'like_sum' => DB::raw('like_sum - 1'),
                            'updated_at' => $showTime,
                        ];
                        if ($role == 'ROLE_STAFF') {
                            $updateCond['staff_like_sum'] = DB::raw('staff_like_sum - 1');
                        } else {
                            $updateCond['customer_like_sum'] = DB::raw('customer_like_sum - 1');
                        }
                        LikeStatistic::where('type', 'twitter')->where('article_id', $twitter['id'])->update($updateCond);
                    }
                }

                $vote->cmsSync = 1;
                $vote->save();

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::error('同步看高手点赞失败.', [$e]);
            }
        }
    }
}
