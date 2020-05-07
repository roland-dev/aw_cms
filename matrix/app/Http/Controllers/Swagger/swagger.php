<?php

/**
 * @SWG\Swagger(
 *  schemes={"http"},
 *  host="api.cms.daohehui.com",
 *  @SWG\Info(
 *    title="CMS API",
 *    version="1.0.0",
 *    description="CMS系统对接接口"
 *  ),
 *  @SWG\Tag(
 *    name="Kit",
 *    description="达人锦囊"
 *  )
 * )
 */

/**
 * @SWG\Definition(
 *  definition="ApiResponseVo<Kit>",
 *  type="object",
 *  description="接口返回数据统一结构",
 *  required={"code", "msg"},
 *  @SWG\Property(
 *    property="code",
 *    type="integer",
 *    description="返回状态码"
 *  ),
 *  @SWG\Property(
 *    property="data",
 *    type="object",
 *    @SWG\Property(
 *      property="kits",
 *      description="锦囊列表",
 *      type="array",
 *      @SWG\Items(ref="#/definitions/Kit")
 *    )
 *  ),
 *  @SWG\Property(
 *    property="msg",
 *    type="string",
 *    description="返回状态码描述"
 *  )
 * )
 */

/**
 * @SWG\Definition(
 *    definition="Kit",
 *    type="object",
 *    description="锦囊",
 *    required={"code", "name", "cover_url", "description", "is_bought", "service_key", "category_key", "kit_detail_url", "reports"},
 *    @SWG\Property(
 *      property="code",
 *      type="string",
 *      description="锦囊Code"
 *    ),
 *    @SWG\Property(
 *      property="name",
 *      type="string",
 *      description="锦囊名称"
 *    ),
 *    @SWG\Property(
 *      property="cover_url",
 *      type="string",
 *      description="锦囊封面图片"
 *    ),
 *    @SWG\Property(
 *      property="description",
 *      type="string",
 *      description="锦囊介绍"
 *    ),
 *    @SWG\Property(
 *      property="is_bought",
 *      type="integer",
 *      description="是否购买 1 已购买 0 未购买"
 *    ),
 *    @SWG\Property(
 *      property="service_key",
 *      type="string",
 *      description="服务Code"
 *    ),
 *    @SWG\Property(
 *      property="category_key",
 *      type="string",
 *      description="栏目Key"
 *    ),
 *    @SWG\Property(
 *      property="kit_detail_url",
 *      type="string",
 *      description="锦囊详情url"
 *    ),
 *    @SWG\Property(
 *      property="reports",
 *      type="array",
 *      description="包含锦囊报告列表",
 *      @SWG\Items(ref="#/definitions/KitReport")
 *    )
 * )
 */

/**
 * @SWG\Definition(
 *    definition="KitReport",
 *    type="object",
 *    description="锦囊报告",
 *    required={"detail_id", "category_key", "report_id", "title", "kit_code", "start_at", "end_at", "cover_url", "summary", "url"},
 *    @SwG\Property(
 *      property="detail_id",
 *      type="string",
 *      description="记录ID"
 *    ),
 *    @SWG\Property(
 *      property="category_key",
 *      type="string",
 *      description="归属栏目"
 *    ),
 *    @SWG\Property(
 *      property="report_id",
 *      type="string",
 *      description="报告ID"
 *    ),
 *    @SWG\Property(
 *      property="title",
 *      type="string",
 *      description="标题"
 *    ),
 *    @SWG\Property(
 *      property="kit_code",
 *      type="string",
 *      description="锦囊Code"
 *    ),
 *    @SWG\Property(
 *      property="start_at",
 *      type="string",
 *      description="报告有效开始时间"
 *    ),
 *    @SWG\Property(
 *      property="end_at",
 *      type="string",
 *      description="报告有效结束时间"
 *    ),
 *    @SWG\Property(
 *      property="cover_url",
 *      type="string",
 *      description="海报缩略图"
 *    ),
 *    @SWG\Property(
 *      property="summary",
 *      type="string",
 *      description="摘要"
 *    ),
 *    @SWG\Property(
 *      property="url",
 *      type="string",
 *      description="详情url"
 *    )
 * )
 */