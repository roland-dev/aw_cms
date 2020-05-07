<?php

namespace Matrix\Http\Controllers\Client;

use Exception;
use Illuminate\Http\Request;
use Log;
use Matrix\Contracts\ArticleManager;
use Matrix\Contracts\CustomerManager;
use Matrix\Contracts\InteractionContract;
use Matrix\Contracts\TeacherManager;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\UserManager;
use Matrix\Exceptions\MatrixException;
use Matrix\Exceptions\PermissionException;

class ContentReplyController extends Controller
{
  const RECEIVE_TYPE = 'receive';
  const SEND_TYPE = 'send';
  const REPLY_LIKE_TYPE = 'article_reply';
  const USER_GROUP_CODE_SUPERMAN_TAG = 'teacher_superman_tag';

  protected $request;
  protected $ucenter;
  protected $interaction;
  protected $customer;
  protected $article;
  protected $user;
  protected $teacher;

  public function __construct(Request $request, 
                              UcManager $ucenter, 
                              InteractionContract $interaction, 
                              CustomerManager $customer,
                              ArticleManager $article,
                              UserManager $user,
                              TeacherManager $teacher)
  {
    $this->request = $request;
    $this->ucenter = $ucenter;
    $this->interaction = $interaction;
    $this->customer = $customer;
    $this->article = $article;
    $this->user = $user;
    $this->teacher = $teacher;
  }
  public function getReplyList()
  {
    $credentials = $this->request->validate([
      'type' => 'required|string',
      'index' => 'nullable|integer',
      'page_size' => 'nullable|integer',
    ]);

    try {
      $type = array_get($credentials, 'type');

      $sessionId = $this->request->header('X-SessionId');
      if (empty($sessionId)) {
        $sessionId = $this->request->cookie('X-SessionId');
      }

      if (empty($sessionId)) {
        throw new PermissionException('尚未登录', SYS_STATUS_PERMISSION_ERROR);
      }

      $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);
      $openId = (string)array_get($ucUserInfo, 'data.user.openId');

      if(empty($openId)) {
        throw new MatrixException('X-SessionId 登录态获取失败', SYS_STATUS_ERROR_UNKNOW);
      }

      // 获取 用户对应的 teahcer_id 列表
      $qyUserId = (string)array_get($ucUserInfo, 'data.user.qyUserId');
      $userInfo = $this->user->getUserByEnterpriseUserId($qyUserId);
      $userId = (int)array_get($userInfo, 'data.id');

      $index = array_get($credentials, 'index', 0);
      $pageSize = (int)array_get($credentials, 'page_size', 20);

      if ($type == self::SEND_TYPE) {
        $replyList = $this->interaction->getReplyListBySend($openId, $index, $pageSize);
      } else if ($type = self::RECEIVE_TYPE) {
        // 如果为 作者老师需要特殊处理 -- 增加 客户 评论
        $replyList = $this->interaction->getReplyListByReceive($openId, $index, $pageSize, $userId);
      } else {
        throw new MatrixException('传入参数 type 格式有问题', SYS_STATUS_ERROR_UNKNOW);
      }

      $customerOpenIdList = $replyList->pluck('open_id');
      $customerRefOpenIdList = $replyList->filter(function ($value, $key) {
        return !empty($value);
      })->pluck('ref_open_id');
      $customerAllOpenIdList = collect( [$customerOpenIdList->unique()->all(), $customerRefOpenIdList->unique()->all()] )->collapse()->unique()->all();
      
      if (!empty($replyList->toArray())) {

        // 获取对应的点赞状态
        $replyIdList = array_column($replyList->toArray(), 'id');
        $replyLikeList = $this->interaction->getLikeRecordList($replyIdList, self::REPLY_LIKE_TYPE, $openId);
        $replyIdOfLike = array_column($replyLikeList, 'article_id');

        //获取对应评论的点赞总数
        $replyLikeSumList = $this->interaction->getLikeSumList($replyIdList, self::REPLY_LIKE_TYPE);
        $likeSumOfReplyId = array_column($replyLikeSumList, NULL, 'article_id');

        $customerList = $this->customer->getCustomerList($customerAllOpenIdList);
        $customerMap = array_column($customerList, NULL, 'open_id');

        $supermanUserList = $this->user->getUserListByGroupCode(self::USER_GROUP_CODE_SUPERMAN_TAG);
        $supermanUserIdList = array_column($supermanUserList, 'id');
        $supermanUcList = $this->user->getUcListByUserIdList($supermanUserIdList);
        $supermanQyUseridList = (array)array_column($supermanUcList, 'enterprise_userid');

        foreach ($replyList as &$reply) {
          $customer = array_get($customerMap, $reply['open_id']);
          if (!empty($customer)) {
            $reply['nickname'] = $customer['name'];
            if (!empty($openId) && $openId == $customer['open_id']) {
              $reply['nickname'] .= '(我)';
            }
            $reply['icon_url'] = $customer['icon_url'];

            if (!empty($supermanQyUseridList) && in_array($customer['qy_userid'], $supermanQyUseridList)) {
              $reply['teacher_qy_userId'] = $customer['qy_userid'];
              $reply['is_teacher'] = 1;
            } else {
              $reply['is_teacher'] = 0;
            }
          }

          if (!empty($userId) && $reply['article_author_user_id'] === $userId) {
            $reply['is_auth'] = 1;
          } else {
            $reply['is_auth'] = 0;
          }

          // 本人是否点赞
          if (in_array($reply['id'], $replyIdOfLike)) {
            $reply['is_like'] = 1;
          } else {
            $reply['is_like'] = 0;
          }

          // 点赞总数
          if (empty($likeSumOfReplyId[$reply['id']])) {
            $reply['like_sum'] = 0;
          } else {
            $likeSum = (int)array_get($likeSumOfReplyId[$reply['id']], 'like_sum');
            $reply['like_sum'] = $likeSum;
          }

          if (!empty($reply['ref_open_id'])) {
            $refCustomer = array_get($customerMap, $reply['ref_open_id']);
            $reply['ref_nickname'] = $refCustomer['name'];
            if (!empty($openId) && $openId == $refCustomer['open_id']) {
              $reply['ref_nickname'] .= '(我)';
            }
            $reply['ref_icon_url'] = $refCustomer['icon_url'];

            if (!empty($supermanQyUseridList) && in_array($refCustomer['qy_userid'], $supermanQyUseridList)) {
              $reply['ref_is_teacher'] = 1;
              $reply['ref_teacher_qy_userid'] = $refCustomer['qy_userid'];
            } else {
              $reply['ref_is_teacher'] = 0;
            }
          }

          $reply['send_time_text'] = '';
          if (!empty($reply['created_at'])) {
            $sendTimeStamp = strtotime($reply['created_at']);
            $nowTime = time();
            $diffTime = $nowTime - $sendTimeStamp;
            if ($diffTime < 60) { // 一分钟内
              $reply['send_time_text'] = '刚刚';
            } elseif ($diffTime <= 3600) { // 一小时内
                $reply['send_time_text'] = intval($diffTime / 60).'分钟前';
            } elseif ($diffTime <= 86400) { // 一天内
                $reply['send_time_text'] = intval($diffTime / 3600).'小时前';
            } else { // 大于一天
                $oneYearAgoTime = strtotime('-1 year');
                if ($sendTimeStamp >= $oneYearAgoTime) { // 一年之内
                    $reply['send_time_text'] = date('m月d日 H:i', $sendTimeStamp);
                } else { // 一年以上
                    $reply['send_time_text'] = date('Y年m月d日 H:i', $sendTimeStamp);
                }
            }
            unset($reply['session_id']);
          }
        }
      }

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'reply_list' => $replyList,
        ],
      ];
    } catch (MatrixException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '发生了一个不可预料的错误',
      ];
    }

    return $ret;
  }

  public function examineReply()
  {
    $credentials = $this->request->validate([
      'reply_id' => 'required|integer', // 评论编号
      'operate' => 'required|integer', // 审批意见
    ]);

    try {
      $replyId = (int)array_get($credentials, 'reply_id');
      $operate = array_get($credentials, 'operate');

      $sessionId = $this->request->header('X-SessionId');
      if (empty($sessionId)) {
        $sessionId = $this->request->cookie('X-SessionId');
      }

      if (empty($sessionId)) {
        throw new PermissionException('尚未登录', SYS_STATUS_PERMISSION_ERROR);
      }

      $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);

      $openId = (string)array_get($ucUserInfo, 'data.user.openId');

      if(empty($openId)) {
        throw new MatrixException('X-SessionId 登录态获取失败', SYS_STATUS_ERROR_UNKNOW);
      }

      $qyUserId = (string)array_get($ucUserInfo, 'data.user.qyUserId');
      $userInfo = $this->user->getUserByEnterpriseUserId($qyUserId);
      $userId = (int)array_get($userInfo, 'data.id');

      if (empty($userId)) {
        throw new PermissionException('当前用户无此操作权限', SYS_STATUS_PERMISSION_ERROR);
      }

      $reply = $this->interaction->getReplyInfo($replyId);

      if ($reply['article_author_user_id'] !== $userId) {
        throw new PermissionException('当前用户无此操作权限', SYS_STATUS_PERMISSION_ERROR);
      }

      if ($reply['forward_to_twitter'] === 1) {
        $ret = [
          'code' => INTERACTION_REPLY_FORWARD_TO_TWITTER,
          'msg' => '该老师已将评论内容转发至解盘，不可变更状态'
        ];
        return $ret;
      }

      $reply = $this->interaction->examineReplyOfClient($replyId, $operate, $userId);

      $customerOpenIdList = [];
      $customerOpenIdList[] = $reply['open_id'];
      if (!empty($reply['ref_open_id'])) {
        $customerOpenIdList[] = $reply['ref_open_id'];
      }

      // 获取对应的点赞状态
      $voteData = $this->interaction->getLikeRecord($reply['id'], self::REPLY_LIKE_TYPE, $openId);
      $reply['is_like'] = (int)array_get($voteData, 'data.like');

      // 获取对应评论的点赞总数
      $replyCntData = $this->interaction->getLikeSum($reply['id'], self::REPLY_LIKE_TYPE);
      $reply['like_sum'] = (int) array_get($replyCntData, 'data.statisticInfo.like_sum');


      $customerList = $this->customer->getCustomerList($customerOpenIdList);
      $customerMap = array_column($customerList, NULL, 'open_id');

      $supermanUserList = $this->user->getUserListByGroupCode(self::USER_GROUP_CODE_SUPERMAN_TAG);
      $supermanUserIdList = array_column($supermanUserList, 'id');
      $supermanUcList = $this->user->getUcListByUserIdList($supermanUserIdList);
      $supermanQyUseridList = array_column($supermanUcList, 'enterprise_userid');

      $customer = array_get($customerMap, $reply['open_id']);
      if (!empty($customer)) {
        $reply['nickname'] = $customer['name'];
        if (!empty($openId) && $openId == $customer['open_id']) {
          $reply['nickname'] .= '(我)';
        }
        $reply['icon_url'] = $customer['icon_url'];

        if (!empty($supermanQyUseridList) && in_array($customer['qy_userid'], $supermanQyUseridList)) {
          $reply['is_teacher'] = 1;
          $reply['teacher_qy_userid'] = $customer['qy_userid'];
        } else {
          $reply['is_teacher'] = 0;
        }

        if (!empty($userId) && $reply['article_author_user_id'] === $userId) {
          $reply['is_auth'] = 1;
        } else {
          $reply['is_auth'] = 0;
        }

        // 点赞总数

        if (!empty($reply['ref_open_id'])) {
          $refCustomer = array_get($customerMap, $reply['ref_open_id']);
          $reply['ref_nickname'] = $refCustomer['name'];
          if (!empty($openId) && $openId == $refCustomer['open_id']) {
            $reply['ref_nickname'] .= '(我)';
          }
          $reply['ref_icon_url'] = $refCustomer['icon_url'];

          if (!empty($supermanQyUseridList) && in_array($refCustomer['qy_userid'], $supermanQyUseridList)) {
            $reply['ref_is_teacher'] = 1;
            $reply['ref_teacher_qy_userid'] = $refCustomer['qy_userid'];
          } else {
            $reply['ref_is_teacher'] = 0;
          }
        }

        $reply['send_time_text'] = '';
        if (!empty($reply['created_at'])) {
          $sendTimeStamp = strtotime($reply['created_at']);
          $nowTime = time();
          $diffTime = $nowTime - $sendTimeStamp;
          if ($diffTime < 60) {
            $reply['send_time_text'] = '刚刚';
          } elseif ($diffTime <= 3600) {
            $reply['send_time_text'] = intval($diffTime / 60).'分钟前';
          } elseif ($diffTime <= 86400) {
            $reply['send_time_text'] = intval($diffTime / 3600).'小时前';
          } else {
            $oneYearAgoTime = strtotime('-1 year');
            if ($sendTimeStamp >= $oneYearAgoTime) {
              $reply['send_time_text'] = date('m月d日 H:i', $sendTimeStamp);
            } else {
              $reply['send_time_text'] = date('Y年m月d日 H:i', $sendTimeStamp);
            }
          }
          unset($reply['session_id']);
        }
      }


      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'reply' => $reply,
        ],
      ];

    } catch (MatrixException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '发生了一个不可预料的错误'
      ];
    }

    return $ret;
  }
}