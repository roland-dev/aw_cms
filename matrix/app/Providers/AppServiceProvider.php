<?php

namespace Matrix\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind('Matrix\Contracts\UcManager',
            'Matrix\Services\UcService');
        $this->app->bind('Matrix\Contracts\UserManager',
            'Matrix\Services\UserService');
        $this->app->bind('Matrix\Contracts\PermissionManager',
            'Matrix\Services\PermissionService');
        $this->app->bind('Matrix\Contracts\VideoManager',
            'Matrix\Services\VideoService');
        $this->app->bind('Matrix\Contracts\LogManager',
            'Matrix\Services\LogService');
        $this->app->bind('Matrix\Contracts\ImageManager',
            'Matrix\Services\ImageService');
        $this->app->bind('Matrix\Contracts\CourseSystemManager',
            'Matrix\Services\CourseSystemService');
        $this->app->bind('Matrix\Contracts\CourseManager',
            'Matrix\Services\CourseService');
        $this->app->bind('Matrix\Contracts\BossManager',
            'Matrix\Services\BossService');
        $this->app->bind('Matrix\Contracts\CourseVideoManager',
            'Matrix\Services\CourseVideoService');
        $this->app->bind('Matrix\Contracts\CategoryManager',
            'Matrix\Services\CategoryService');
        $this->app->bind('Matrix\Contracts\ContentGuardContract',
            'Matrix\Services\ContentGuardService');
        $this->app->bind('Matrix\Contracts\BossManager',
            'Matrix\Services\BossService');
        $this->app->bind('Matrix\Contracts\ArticleManager',
            'Matrix\Services\ArticleService');
        $this->app->bind('Matrix\Contracts\CustomerManager',
            'Matrix\Services\CustomerService');
        $this->app->bind('Matrix\Contracts\TwitterManager',
            'Matrix\Services\TwitterService');
        $this->app->bind('Matrix\Contracts\SystemNoticeManager',
            'Matrix\Services\SystemNoticeService');
        $this->app->bind('Matrix\Contracts\SessionManager',
            'Matrix\Services\SessionService');
        $this->app->bind('Matrix\Contracts\TeacherManager',
            'Matrix\Services\TeacherService');
        $this->app->bind('Matrix\Contracts\AdManager',
            'Matrix\Services\AdService');
        $this->app->bind('Matrix\Contracts\ForumManager',
            'Matrix\Services\ForumService');
        $this->app->bind('Matrix\Contracts\FeedManager',
            'Matrix\Services\FeedService');
        $this->app->bind('Matrix\Contracts\OpenApiContract',
            'Matrix\Services\OpenApiService');
        $this->app->bind('Matrix\Contracts\UserGroupManager',
            'Matrix\Services\UserGroupService');
        $this->app->bind('Matrix\Contracts\SubCategoryManager',
            'Matrix\Services\SubCategoryService');
        $this->app->bind('Matrix\Contracts\CategoryGroupManager',
            'Matrix\Services\CategoryGroupService');
        $this->app->bind('Matrix\Contracts\MoveQrContract',
            'Matrix\Services\MoveQrService');
        $this->app->bind('Matrix\Contracts\InteractionContract',
            'Matrix\Services\InteractionService');
        $this->app->bind('Matrix\Contracts\TalkshowContract',
            'Matrix\Services\TalkshowService');
        $this->app->bind('Matrix\Contracts\OperateLogContract',
            'Matrix\Services\OperateLogService');
        $this->app->bind('Matrix\Contracts\StockReportManager',
            'Matrix\Services\StockReportService');
        $this->app->bind('Matrix\Contracts\KitManager',
            'Matrix\Services\KitService');
        $this->app->bind('Matrix\Contracts\KitReportManager',
            'Matrix\Services\KitReportService');
        $this->app->bind('Matrix\Contracts\DynamicAdManager',
            'Matrix\Services\DynamicAdService');
        
    }
}
