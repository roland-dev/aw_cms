<?php
namespace Matrix\Services;

use Matrix\Contracts\ContentGuardContract;
use Matrix\Models\ContentGuard;
use Exception;
use Log;

class ContentGuardService extends BaseService implements ContentGuardContract
{
    const URI_AD_ACCESS = '/api/v2/propaganda/ad/{adId}';
    const URI_FORUM_ACCESS = '/api/v2/propaganda/forum/{forumId}';
    const URI_COURSE_ACCESS = '/api/v2/coursesystem/{courseSystemCode}/course/{courseCode}';

    const URI_ARTICLE_ACCESS = '/api/v2/article/{article_id}';

    protected $contentGuard;
    protected $blankRow;

    public function __construct(ContentGuard $contentGuard)
    {
        $this->contentGuard = $contentGuard;
        $this->blankRow = [
            'service_code' => '',
            'uri' => '',
            'param1' => null,
            'param2' => null,
            'param3' => null,
        ];
    }

    public function getCourseAccessCodeList()
    {
        $courseContentGuardList = $this->contentGuard->getContentGuardList(self::URI_COURSE_ACCESS);

        return array_column($courseContentGuardList, 'service_code', 'param2');
    }

    public function checkCourseAccess(array $grantedCodeList, string $uri, array $parameter)
    {
    }

    public function getOnesAdAccessIdList(array $serviceKeyList)
    {
        try {
            $contentGuardList = $this->contentGuard->getContentGuardByUriAndCode(self::URI_AD_ACCESS, $serviceKeyList);
            $adAccessIdList = array_column($contentGuardList, 'param1');

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'ad_access_id_list' => $adAccessIdList,
                ],
            ];
        } catch (Exception $e) {
            $ret = [ 'code' => SYS_STATUS_ERROR_UNKNOW ];
        }

        return $ret;
    }

    public function getOnesAccessIdList(string $uri, array $serviceKeyList, string $key = 'param1')
    {
        $contentGuardList = $this->contentGuard->getContentGuardByUriAndCode($uri, $serviceKeyList);
        $accessIdList = array_column($contentGuardList, $key);
        return $accessIdList;
    }

    public function getOneForumAccessIdList(array $serviceKeyList)
    {
        try {
            $contentGuardList = $this->contentGuard->getContentGuardByUriAndCode(self::URI_FORUM_ACCESS, $serviceKeyList);
            $forumAccessIdList = array_column($contentGuardList, 'param1');

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'forum_access_id_list' => $forumAccessIdList,
                ],
            ];
        } catch (Exception $e) {
            $ret = [ 'code' => SYS_STATUS_ERROR_UNKNOW ];
        }

        return $ret;
    }

    public function getOneCourseAccessCodeTree(array $serviceKeyList)
    {
        try {
            $courseAccessCodeTree = [];
            $contentGuardList = $this->contentGuard->getContentGuardByUriAndCode(self::URI_COURSE_ACCESS, $serviceKeyList);
            foreach ($contentGuardList as $contentGuard) {
                if (!array_key_exists($contentGuard['param1'], $courseAccessCodeTree)) {
                    $courseAccessCodeTree[$contentGuard['param1']] = [];
                }
                $courseAccessCodeTree[$contentGuard['param1']][] = $contentGuard['param2'];
            }
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'course_access_code_tree' => $courseAccessCodeTree,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [ 'code' => SYS_STATUS_ERROR_UNKNOW ];
        }

        return $ret;
    }

    public function grant(array $newData)
    { // SYS_STATUS_CONTENT_GUARD_EXISTS
        $newContentGuard = $this->contentGuard->createContentGuard($newData);
        if (empty($newContentGuard)) {
            $ret = [ 'code' => SYS_STATUS_ERROR_UNKNOW ];
        } else {
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'content_guard' => $newContentGuard,
                ],
            ];
        }

        return $ret;
    }

    public function revoke(array $condition)
    { // SYS_STATUS_CONTENT_GUARD_NOT_EXISTS
        $removeContentGuard = $this->contentGuard->updateContentGuard($condition, $this->blankRow);

        return $removeContentGuard;
    }

    public function update(array $condition, array $newData)
    {
        $updateContentGuard = $this->contentGuard->updateContentGuard($condition, $newData);
        return $updateContentGuard;
    }

    public function getOneAccessCode(string $param1, string $param2, string $uri)
    {
        $contentGuard = $this->contentGuard->getContentGuardInfo($param1, $param2, $uri);
        return $contentGuard;
    }

    public function getOneArticleAccessIdList(array $serviceKeyList)
    {
        try {
            $contentGuardList = $this->contentGuard->getContentGuardByUriAndCode(self::URI_ARTICLE_ACCESS, $serviceKeyList);
            $articleAccessIdList = array_column($contentGuardList, 'param1');

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'article_access_id_list' => $articleAccessIdList,
                ],
            ];
        } catch (Exception $e) {
            $ret = [ 'code' => SYS_STATUS_ERROR_UNKNOW ];
        }

        return $ret;
    }
}

