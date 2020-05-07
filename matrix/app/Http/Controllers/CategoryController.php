<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\CategoryManager;

use Matrix\Exceptions\MatrixException;
use Exception;
use Log;
use Matrix\Contracts\BossManager;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    //
    private $request;
    private $categoryManager;
    private $bossManager;

    public function __construct(Request $request, CategoryManager $categoryManager, BossManager $bossManager)
    {
        $this->request = $request;
        $this->categoryManager = $categoryManager;
        $this->bossManager = $bossManager;
    }

    public function getMyCategoryList()
    {
        try {
            $categoryListRes = $this->categoryManager->getMyCategoryList();
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'category_list' => array_get($categoryListRes, 'data.category_list'),
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
            ];
        }

        return $ret;
    }

    public function getCategoryList()
    {
        try {
            $categoryListRes = $this->categoryManager->getCategoryList();
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'category_list' => array_get($categoryListRes, 'data.category_list'),
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
            ];
        }

        return $ret;
    }

    public function getSubCategoryListByCategoryCode(string $categoryCode)
    {
        try {
            $subCategoryListRes = $this->categoryManager->getSubCategoryListByCategoryCode($categoryCode);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'sub_category_list' => array_get($subCategoryListRes, 'data.sub_category_list'),
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
            ];
        }

        return $ret;
    }

    public function getCategoryTeacherList(string $categoryCode)
    {
        try {
            $teacherListRes = $this->categoryManager->getTeacherListByCategoryCode($categoryCode);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'teacher_list' => array_get($teacherListRes, 'data.teacher_list'),
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
            ];
        }

        return $ret;
    }

    public function getCategoryListByGroupCode(string $categoryGroupCode)
    {
        $categoryListData = $this->categoryManager->getCategoryListByGroupCode($categoryGroupCode);
        $categoryList = array_get($categoryListData, 'data.category_list');

        $type = $this->request->input('type');
        if ('my' == $type) {
            $myCategoryListData = $this->categoryManager->getMyCategoryList();
            $myCategoryList = array_get($myCategoryListData, 'data.category_list');
            $myCategoryCodeList = array_column($myCategoryList, 'code');
            $mergeCategoryList = [];
            foreach ($categoryList as $category) {
                if (in_array($category['code'], $myCategoryCodeList)) {
                    $mergeCategoryList[] = $category;
                }
            }
        } else {
            $mergeCategoryList = $categoryList;
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_list' => $mergeCategoryList,
            ],
        ];

        return $ret;
    }

    public function getServiceList()
    {
        $serviceRes = $this->bossManager->getServices('');
        $this->checkServiceResult($serviceRes, 'BossService');
        $serviceList = array_get($serviceRes, 'data');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => $serviceList,
        ];
        return $ret;
    }


    /**
    *获取推送到企业微信的栏目分类
    *
    *@return array
    */
    public function getCategoryOfPushQywx()
    {
        try{
            $categoryListData = $this->categoryManager->getCategoryListByGroupCode('article_group_push_qywx');
            $categoryList = array_get($categoryListData, 'data.category_list');
            $categoryCodeList = array_column($categoryList, 'code');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $categoryCodeList,
            ];
        }catch(Exception $e){
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function getCategoryInfoByCategoryId(int $categoryId)
    {
        $categoryInfoRes = $this->categoryManager->getCategoryInfoByCategoryId($categoryId);
        try {
            $this->checkServiceResult($categoryInfoRes, 'CategoryService');
            $categoryInfo = array_get($categoryInfoRes, 'data.category_info');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'category_info' => $categoryInfo,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => array_get($categoryInfoRes, 'code', SYS_STATUS_ERROR_UNKNOW),
            ];
        }

        return $ret;
    }

    public function search()
    {
        $reqData = $this->request->validate([
            'name' => 'nullable|string|max:32',
            'service_key' => 'nullable|string|max:32'
        ]);

        $categoryListRes = $this->categoryManager->searchCategoryList($reqData);
        $this->checkServiceResult($categoryListRes, 'CategoryService');
        $categoryList = array_get($categoryListRes, 'data.category_list');

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_list' => $categoryList,
            ],
        ];
        
        return $ret;
    }

    public function getCategoryListOfPaging () {
        $credentials = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
            'name' => 'nullable|string',
            'service_key' => 'nullable|string'
        ]);

        try {
            $pageNo = array_get($credentials, 'page_no', 1);
            $pageSize = array_get($credentials, 'page_size', 10);

            $categoryList = $this->categoryManager->getCategoryListOfPaging($pageNo, $pageSize, $credentials);
            $categoryCnt = $this->categoryManager->getCategoryCnt($credentials);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'category_list' => $categoryList,
                    'category_cnt' => $categoryCnt,
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
                'code' =>SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误',
            ];
        }

        return $ret;
    }

    public function checkCategoryCodeUnique(string $categoryCode)
    {
        $repData = $this->categoryManager->checkCategoryCodeUnique($categoryCode);
        $this->checkServiceResult($repData, 'CategoryService');
        $oneCategoryInfo = array_get($repData, 'data.category_code_check_res');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'check_res' => $oneCategoryInfo,
            ],
        ];
        return $ret;
    }

    public function create()
    {
        $reqData = $this->request->validate([
            'name' => 'required|string',
            'code' => 'required|string',
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'cover_url' => 'nullable|string',
            'ad_image_url' => 'nullable|string',
            'service_key' => 'required|string',
        ]);

        $categoryInfoRes = $this->categoryManager->createCategory($reqData);

        try {
            $this->checkServiceResult($categoryInfoRes, 'CategoryService');
            $categoryInfo = array_get($categoryInfoRes, 'data.category_info');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'category_info' => $categoryInfo,
                ],                
            ];
        } catch (Exception $e) {
            $ret = [
                'code' => array_get($categoryInfoRes, 'code', SYS_STATUS_ERROR_UNKNOW),
            ];
        }

        return $ret;
    }

    public function update($categoryId)
    {
        $reqData = $this->request->validate([
            'name' => 'required|string',
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'cover_url' => 'nullable|string',
            'ad_image_url' => 'nullable|string',
            'service_key' => 'required|string',
            'primary_teacher_id' => 'nullable|integer',
        ]);

        $categoryInfoRes = $this->categoryManager->updateCategory($categoryId, $reqData);

        try {
            $this->checkServiceResult($categoryInfoRes, 'CategoryService');
            $categoryInfo = array_get($categoryInfoRes, 'data.category_info');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'category_info' => $categoryInfo,
                ],
            ];
        } catch (Exception $e) {
            $ret = [
                'code' => array_get($categoryInfoRes, 'code', SYS_STATUS_ERROR_UNKNOW),
            ];
        }

        return $ret;
    }

    public function getTeacherList($categoryCode)
    {
        $teacherListRes = $this->categoryManager->getTeacherList($categoryCode);
        try {
            $this->checkServiceResult($teacherListRes, 'CategoryService');
            $teacherList = array_get($teacherListRes, 'data.teacher_list');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'teacher_list' => $teacherList,
                ],
            ];
        } catch (Exception $e) {
            $ret = [
                'code' => array_get($teacherListRes, 'code', SYS_STATUS_ERROR_UNKNOW),
            ];
        }

        return $ret;
    }

    public function uploadCoverImage()
    {
        if (!$this->request->hasFile('image')) {
            abort(400);
        }
        $path = $this->request->image->store('public/category_cover');

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'path' => config('app.cdn.base_url').Storage::url($path),
            ],
        ];

        return $ret;
    }

    public function uploadAdCoverImage()
    {
        if (!$this->request->hasFile('image')) {
            abort(400);
        }
        $path = $this->request->image->store('public/category_ad_cover');

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'path' => config('app.cdn.base_url').Storage::url($path),
            ],
        ];

        return $ret;
    }
}
