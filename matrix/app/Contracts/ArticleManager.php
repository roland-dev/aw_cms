<?php
namespace Matrix\Contracts;

interface ArticleManager extends BaseInterface
{
    public function getArticleList(int $pageNo, int $pageSize, array $credentials);
    public function getArticleCnt(array $credentials);
    public function createArticle(array $articleData);
    public function updateArticle(int $articleId, array $articleData);
    public function updateArticleShow($articleId);
    public function trashArticle(int $articleId);
    public function getArticleInfo($articleId, $openId, $udid);
    public function getActiveArticleListBySubCategoryCode(string $subCategoryCode);
    public function readArticle($articleId, $openId);
    public function likeArticle($articleId, $openId, $udid);
    public function getArticleListByTeacherIdList(array $teacherIdList, int $index, int $pageSize);
    public function getUnfeedArticleList(array $categoryCodeList);
    public function setArticleFeed(array $articleIdList);
}

