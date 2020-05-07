<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\TeacherManager;
use Exception;
use Log;
use DB;

class SyncFriendToUc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uc:syncfriend {business : Business line (default | hk)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Friends relationship to UC.';

    protected $ucenter;
    protected $userManager;
    protected $teacherManager;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TeacherManager $teacherManager, UserManager $userManager, UcManager $ucenter)
    {
        $this->ucenter = $ucenter;
        $this->userManager = $userManager;
        $this->teacherManager = $teacherManager;

        parent::__construct();
    }

    protected function batchFriend($followStack, $teacherEnterpriseUserIdMap, $action)
    {
        DB::beginTransaction();
        try {
            $syncStack = [];
            $ucFailFriendList = [];
            $followStack->each(function ($item, $key) use (&$syncStack, $teacherEnterpriseUserIdMap, $action) { // follow
                $item->sync_to_uc = 1;
                $item->save();
                $syncStack[] = [
                    'openId' => $item->open_id,
                    'targetQyUserId' => (string)array_get($teacherEnterpriseUserIdMap, $item->user_id),
                ];

                if (count($syncStack) >= 100) {
                    switch ($action) {
                        case 'follow':
                            $ucFailFriendList = $this->ucenter->batchFriend($syncStack);
                            break;
                        case 'unfollow':
                            $this->ucenter->batchRemoveFriend($syncStack);
                            break;
                    }
                    if ('follow' === $action && !empty($ucFailFriendList)) {
                        $followStack->each(function ($item, $key) use ($ucFailFriendList, $teacherEnterpriseUserIdMap) {
                            foreach ($ucFailFriendList as $ucFailFriend) {
                                if ($item->open_id == $ucFailFriend['openId'] && (string)array_get($teacherEnterpriseUserIdMap, $item->user_id) == $ucFailFriend['targetQyUserId']) {
                                    $item->sync_to_uc = 0;
                                    $item->save();
                                }
                            }
                        });
                    }
                    DB::commit();
                    $syncStack = [];
                    $ucFailFriendList = [];
                    DB::beginTransaction();
                }
            });

            if (!empty($syncStack)) {
                switch ($action) {
                    case 'follow':
                        $ucFailFriendList = $this->ucenter->batchFriend($syncStack);
                        break;
                    case 'unfollow':
                        $this->ucenter->batchRemoveFriend($syncStack);
                        break;
                }

                if ('follow' === $action && !empty($ucFailFriendList)) {
                    $followStack->each(function ($item, $key) use ($ucFailFriendList, $teacherEnterpriseUserIdMap) {
                        foreach ($ucFailFriendList as $ucFailFriend) {
                            if ($item->open_id == $ucFailFriend['openId'] && (string)array_get($teacherEnterpriseUserIdMap, $item->user_id) == $ucFailFriend['targetQyUserId']) {
                                $item->sync_to_uc = 0;
                                $item->save();
                            }
                        }
                    });
                }
                DB::commit();
            }
        } catch (Exception $e) {
            Log::error("Teacher follow sync to uc error: ", [$e, $syncStack]);
            DB::rollBack();
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $business = $this->argument('business');
        $teacherFollowList = $this->teacherManager->getUnsyncUcFollowList($business, 1000);

        $ucFollowStack = $teacherFollowList->filter(function ($item, $key) {
            return !empty($item->active);
        });

        $ucUnfollowStack = $teacherFollowList->filter(function ($item, $key) {
            return empty($item->active);
        });

        $teacherUserIdList = $teacherFollowList->pluck('user_id')->toArray();
        $teacherEnterpriseUserIdList = $this->userManager->getUcListByUserIdList($teacherUserIdList);
        $teacherEnterpriseUserIdMap = array_column($teacherEnterpriseUserIdList, 'enterprise_userid', 'user_id');

        $this->batchFriend($ucFollowStack, $teacherEnterpriseUserIdMap, 'follow');
        $this->batchFriend($ucUnfollowStack, $teacherEnterpriseUserIdMap, 'unfollow');
    }
}
