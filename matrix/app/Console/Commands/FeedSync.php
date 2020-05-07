<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\TwitterManager;
use Matrix\Contracts\ArticleManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\FeedManager;

class FeedSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feed:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync content to feed table when some resource.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function fitClientUrl(string $url)
    {
        if (empty($url)) {
            return '';
        }
        if (strpos($url, '//') === 0) {
            $url = "http:$url";
        }

        return $url;
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(CategoryManager $categoryManager, TwitterManager $twitterManager, ArticleManager $articleManager, UserManager $userManager, FeedManager $feedManager)
    {
        //
        $categoryListData = $categoryManager->getCategoryListByGroupCode('twitter_group_a');
        $categoryList = array_get($categoryListData, 'data.category_list');
        $categoryCodeList = array_column($categoryList, 'code');
        $categoryList = array_column($categoryList, NULL, 'code');

        $twitterList = $twitterManager->getUnfeedTwitterList($categoryCodeList);

        $teacherIdList = array_column($twitterList, 'teacher_id');
        $teacherListData = $categoryManager->getTeacherListByIdList($teacherIdList);
        $teacherList = array_get($teacherListData, 'data.teacher_list');

        $userIdList = array_column($teacherList, 'user_id');
        $ucList = $userManager->getUcListByUserIdList($userIdList);
        $ucList = array_column($ucList, 'enterprise_userid', 'user_id');

        $teacherList = array_column($teacherList, NULL, 'id');

        $syncTwitterIdList = [];
        $twitterFeedList = [];
        foreach ($twitterList as $twitter) {
            if ($twitter['teacher_id'] == 0)
            {
                continue;
            }
            $twitterFeed = [
                'feed_owner' => $teacherList[$twitter['teacher_id']]['name'], // teacher name
                'feed_type'  => 11, // twitter=11, article=12
                'category_key' => (string)array_get($twitter, 'category_code'), // category_code
                'msg_type' => 'message', // twitter = message, article = news
                'owner_id' => $ucList[$teacherList[$twitter['teacher_id']]['user_id']], // teacher.qy_userid
                'source_id' => (string)array_get($twitter, 'id'),
                'title' => '',
                'summary' => (string)array_get($twitter, 'content'),
                'access_level' => $categoryList[array_get($twitter, 'category_code')]['service_key'],
                'push_status' => 5,
                'add_time' => (string)array_get($twitter, 'created_at'),
            ];

            $imageUrlArr = (array)array_get($twitter, 'image_url');
            if (empty($imageUrlArr)) {
                $twitterFeed['thumb_cdn_url'] = '';
                $twitterFeed['origin_image_url'] = '';
            } else {
                $twitterFeed['thumb_cdn_url'] = $this->fitClientUrl((string)$imageUrlArr[0]);
                $twitterFeed['origin_image_url'] = $this->fitClientUrl((string)$imageUrlArr[0]);
            }

            if (!empty($twitter['ref_id'])) {
                $twitterFeed['refer'] = [
                    'ref_type' => $twitter['ref_type'],
                    'ref_id' => $twitter['ref_id'],
                    'ref_thumb' => $this->fitClientUrl($twitter['ref_thumb']),
                    'ref_title' => $twitter['ref_title'],
                    'ref_summary' => $twitter['ref_summary'],
                    'ref_category_code' => $twitter['ref_category_code'],
                ];
            }
            $syncTwitterIdList[] = $twitter['id'];
            $twitterFeedList[] = $twitterFeed;
        }
        $twitterManager->setTwitterFeed($syncTwitterIdList);
        $feedManager->syncInFeed($twitterFeedList);

        $categoryListDataOfPushQywx = $categoryManager->getCategoryListByGroupCode('article_group_push_qywx');
        $categoryListOfPushQywx = array_get($categoryListDataOfPushQywx, 'data.category_list');
        $categoryCodeListOfPushQywx = array_column($categoryListOfPushQywx, 'code');


        $categoryListData = $categoryManager->getCategoryListByGroupCode('article_group_a');
        $categoryList = array_get($categoryListData, 'data.category_list');
        $categoryCodeList = array_column($categoryList, 'code');
        $categoryList = array_column($categoryList, NULL, 'code');

        $articleList = $articleManager->getUnfeedArticleList($categoryCodeList);

        $teacherIdList = array_column($articleList, 'teacher_id');
        $teacherListData = $categoryManager->getTeacherListByIdList($teacherIdList);
        $teacherList = array_get($teacherListData, 'data.teacher_list');

        $userIdList = array_column($teacherList, 'user_id');
        $ucList = $userManager->getUcListByUserIdList($userIdList);
        $ucList = array_column($ucList, 'enterprise_userid', 'user_id');

        $teacherList = array_column($teacherList, NULL, 'id');

        $syncArticleIdList = [];
        $articleFeedList = [];
        $isPushQywx = 0;
        foreach ($articleList as $article) {
            if ($article['teacher_id'] == 0 || $article['show'] == 0)
            {
                continue;
            }

            if(in_array(array_get($article, 'category_code'), $categoryCodeListOfPushQywx) && 1 === (int)array_get($article, 'is_push_qywx')){//避免出现如果 不再推送分组里面，但是发送文章时也勾选了推送qywx，这种情况
                $isPushQywx = 1;
            }

            $articleFeed = [
                'feed_owner' => $teacherList[$article['teacher_id']]['name'], // teacher name
                'feed_type'  => 12, // twitter=11, article=12
                'category_key' => array_get($article, 'category_code'), // category_code
                'msg_type' => 'news', // twitter = message, article = news
                'owner_id' => $ucList[$teacherList[$article['teacher_id']]['user_id']], // teacher.qy_userid
                'source_id' => array_get($article, 'id'),
                'title' => array_get($article, 'title'),
                'summary' => array_get($article, 'summary'),
                'thumb_cdn_url' => $this->fitClientUrl((string)array_get($article, 'cover_url')),
                'origin_image_url' => $this->fitClientUrl((string)array_get($article, 'cover_url')),
                'access_level' => $categoryList[array_get($article, 'category_code')]['service_key'],
                'source_url' => sprintf('/api/v2/client/article/%s', array_get($article, 'id')),
                'push_status' => 0,
                'add_time' => array_get($article, 'published_at'),
                'qywx_status' => $isPushQywx,
            ];
            $syncArticleIdList[] = $article['id'];
            $articleFeedList[] = $articleFeed;
        }

        $articleManager->setArticleFeed($syncArticleIdList);
        $feedManager->syncInFeed($articleFeedList);
    }
}
