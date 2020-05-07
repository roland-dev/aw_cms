
// 如果不适用cdn中的photoswipe则需要启用此路径计算逻辑
// var allScripts = document.getElementsByTagName("script");
// var PREVIEWJS_PATH = allScripts[allScripts.length - 1].getAttribute("src");
// PREVIEWJS_PATH = PREVIEWJS_PATH.substring(0, PREVIEWJS_PATH.lastIndexOf("/"));

(function() {
    var imgsSrc = [];
    var imageItems = [];
    var iOSBridge = null;
    var h5swiper = null;
    var imageSwipeOptions = {};
    const MAX_URL_PARAM_SIZE = 505000;

    function reviewImage(ele) {
        var src = getFullSrc(ele.srcElement);
        if (!src) return;

        var srcIndex = imgsSrc.indexOf(src);
        if (imgsSrc.length <= 0 || srcIndex < 0) {
            initImageList();
        }

        if ((typeof window.WeixinJSBridge != 'undefined') && !window.isDelayLoadForApp) {
            //微信浏览器图片查看器初始化
            WeixinJSBridge.invoke('imagePreview', {
                'current': src,
                'urls': imgsSrc
            });
        } else {
            var isInlineImage = (src.substring(0, 5) == "data:");
            if (isInlineImage && (src.length >= MAX_URL_PARAM_SIZE)) {
                //内嵌图片超长，则不做任何展现处理
                return;
            }

            if (window.zytg_imagePreview) {
                // alert("android detected");
                //android 图片查看器初始化
                window.zytg_imagePreview.callNativePicture(window.location.href, document.title, (isInlineImage ? [] : imgsSrc), src);
            } else if (iOSBridge) {
                //iOS 图片查看器初始化
                var data = {
                    pageUrl: window.location.href,
                    title: document.title,
                    imageUrls: isInlineImage ? [] : imgsSrc,
                    currentImageUrl: src
                };
                iOSBridge.callHandler('zytg_imagePreview', data, function responseCallback(responseData) {});
            } else {
                //其他app的webview环境，启用photoswipe组件
                ele.preventDefault();
                if (!h5swiper) {
                    //初始化photoswipe
                    // console.log(src.length, src, imgsSrc);
                    loadPhotoSwipeHTML(function(){
                        h5swiper = document.getElementById("photoSwiper");
                        showH5Swiper(srcIndex);
                    });
                } else {
                    showH5Swiper(srcIndex);
                }

            }
        }
    }

    function showH5Swiper(srcIndex) {
        if (!h5swiper) return false;
        imageSwipeOptions.index = srcIndex;

        gallery = new PhotoSwipe( h5swiper, PhotoSwipeUI_Default, imageItems, imageSwipeOptions);
        gallery.init();

        //处理pushstate冲突，图片浏览期间禁用
        window.disalbePushStateMonitor = true;
        gallery.listen('destroy', function() { 
            setTimeout(function(){
                window.disalbePushStateMonitor = false;
            }, 500);
        });
    }

    //ios图片查看器对接
    function setupWebViewJavascriptBridge(callback) {
        try {
            if (window.WVJBCallbacks) { return window.WVJBCallbacks.push(callback); }
            window.WVJBCallbacks = [callback];
            var WVJBIframe = document.createElement('iframe');
            WVJBIframe.style.display = 'none';
            WVJBIframe.src = 'https://__bridge_loaded__';
            document.documentElement.appendChild(WVJBIframe);
            setTimeout(function() { document.documentElement.removeChild(WVJBIframe) }, 0);
        } catch(err) {
            console.log("bridge", err);
        }
    }

    function initImageList() {
        var imgs = document.getElementsByTagName('img');
        imgsSrc = [];
        for (var i = 0; i < imgs.length; i++) {
            var img = imgs.item(i);

            if (img) {
                var src = getFullSrc(img);

                if (src) {
                    if (typeof window.WeixinJSBridge == 'undefined' && !window.zytg_imagePreview && !iOSBridge) {
                        imgsSrc.push(src);
                        imageItems.push({
                          src: src,
                          w: window.screen.availWidth,
                          h: Math.floor(img.height / img.width * window.screen.availWidth)
                        });
                    } else {
                        imgsSrc.push(src);
                    }
                }
            }
        }
    }

    function getFullSrc(img) {
        if (!img) return null;
        var src = img.getAttribute("src");      
        if ( !src || (img.width <= 100) || (src.indexOf("/public/") >= 0)) {
            return null;
        }

        if (src.substring(0, 2) == "//") {
            src = "https:" + src;
        }
        if (src.indexOf("://") < 0 && (src.substring(0, 10) != "data:image"))
            src = window.location.origin + '/' + src;
        return src;
    }

    if ((typeof jQuery ==  'undefined') && (typeof Zepto ==  'undefined')) {
        loadZepto(function(){
            $(document).on("click", "img", reviewImage);
        });
    } else {
        $(document).on("click", "img", reviewImage);
    }


    // if (window.isDelayLoadForApp) {
    //     setTimeout(function(){onImgLoad();}, 200);
    // } else {
    //     if (window.addEventListener) {
    //         window.addEventListener('load', onImgLoad, false);
    //     } else if (window.attachEvent) {
    //         window.attachEvent('load', onImgLoad);
    //         window.attachEvent('onload', onImgLoad);
    //     }
    // }

    if (!window.WeixinJSBridge && !window.zytg_imagePreview) {
        //todo: 需要通知ios开发，webview中需写入zytg标识，精确范围判断
        if (navigator.platform == "iPhone" || navigator.platform == "iPad") {
            setupWebViewJavascriptBridge(function(bridge) {
                iOSBridge = bridge;
            });
        }
    }

    function loadJS(url, callback) {//加载js
        loadScript = document.createElement("script");
        loadScript.type = "text/javascript";
        loadScript.src = url;
        document.body.appendChild(loadScript);

        loadScript.onload = function() {//加载结束之后执行回调
             loadScript.onload = null;
             callback && callback();
        }
    }

    function loadZepto(callback) {//加载zeptojs
        loadJS("https://cdn.bootcss.com/zepto/1.2.0/zepto.min.js", callback)
    }

    function loadPhotoSwipeHTML(callback) {
        html = "";
        html = html + ("    <!-- 缩略图放大缩小功能 -->");
        html = html + ("    <!-- Root element of PhotoSwipe. Must have class pswp. -->");
        // html = html + ("    <link rel=\'stylesheet\' href=\'" + PREVIEWJS_PATH + "/pswp/photoswipe.css\'>");
        html = html + ("    <link rel=\'stylesheet\' href=\'https://cdn.bootcss.com/photoswipe/4.1.2/photoswipe.min.css\'>");
        html = html + ("    <div id=\'photoSwiper\' class=\'pswp\' tabindex=\'-1\' role=\'dialog\' aria-hidden=\'true\'>");
        html = html + ("        <!-- Background of PhotoSwipe. ");
        html = html + ("             It\'s a separate element as animating opacity is faster than rgba(). -->");
        html = html + ("        <div class=\'pswp__bg\'></div>");
        html = html + ("        <!-- Slides wrapper with overflow:hidden. -->");
        html = html + ("        <div class=\'pswp__scroll-wrap\'>");
        html = html + ("            <!-- Container that holds slides. ");
        html = html + ("                PhotoSwipe keeps only 3 of them in the DOM to save memory.");
        html = html + ("                Don\'t modify these 3 pswp__item elements, data is added later on. -->");
        html = html + ("            <div class=\'pswp__container\'>");
        html = html + ("                <div class=\'pswp__item\'></div>");
        html = html + ("                <div class=\'pswp__item\'></div>");
        html = html + ("                <div class=\'pswp__item\'></div>");
        html = html + ("            </div>");
        html = html + ("            <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->");
        html = html + ("            <div class=\'pswp__ui pswp__ui--hidden\'>");
        html = html + ("                <div class=\'pswp__top-bar\'>");
        html = html + ("                    <!--  Controls are self-explanatory. Order can be changed. -->");
        html = html + ("                    <div class=\'pswp__counter\'></div>");
        html = html + ("                    <button class=\'pswp__button pswp__button--close\' title=\'Close (Esc)\'></button>");
        html = html + ("                    <button class=\'pswp__button pswp__button--share\' title=\'Share\'></button>");
        html = html + ("                    <button class=\'pswp__button pswp__button--fs\' title=\'Toggle fullscreen\'></button>");
        html = html + ("                    <button class=\'pswp__button pswp__button--zoom\' title=\'Zoom in/out\'></button>");
        html = html + ("                    <!-- Preloader demo http://codepen.io/dimsemenov/pen/yyBWoR -->");
        html = html + ("                    <!-- element will get class pswp__preloader--active when preloader is running -->");
        html = html + ("                    <div class=\'pswp__preloader\'>");
        html = html + ("                        <div class=\'pswp__preloader__icn\'>");
        html = html + ("                          <div class=\'pswp__preloader__cut\'>");
        html = html + ("                            <div class=\'pswp__preloader__donut\'></div>");
        html = html + ("                          </div>");
        html = html + ("                        </div>");
        html = html + ("                    </div>");
        html = html + ("                </div>");
        html = html + ("                <div class=\'pswp__share-modal pswp__share-modal--hidden pswp__single-tap\'>");
        html = html + ("                    <div class=\'pswp__share-tooltip\'></div> ");
        html = html + ("                </div>");
        html = html + ("                <button class=\'pswp__button pswp__button--arrow--left\' title=\'Previous (arrow left)\'>");
        html = html + ("                </button>");
        html = html + ("                <button class=\'pswp__button pswp__button--arrow--right\' title=\'Next (arrow right)\'>");
        html = html + ("                </button>");
        html = html + ("                <div class=\'pswp__caption\'>");
        html = html + ("                    <div class=\'pswp__caption__center\'></div>");
        html = html + ("                </div>");
        html = html + ("            </div>");
        html = html + ("        </div>");
        html = html + ("    </div>");
        var swipe = document.createElement("div");
        swipe.innerHTML = html;
        document.body.appendChild(swipe);

        // loadJS(PREVIEWJS_PATH + "/pswp/photoswipe.min.js", function(){
        //     loadJS(PREVIEWJS_PATH + "/pswp/photoswipe-ui-default.min.js", function(){
        //         callback && callback();
        //     });
        // });
        // 暂修改为从cdn获取photoswipe
        loadJS("https://cdn.bootcss.com/photoswipe/4.1.2/photoswipe.min.js", function(){
            loadJS("https://cdn.bootcss.com/photoswipe/4.1.2/photoswipe-ui-default.min.js", function(){
                callback && callback();
            });
        });


        // 放大展示图片 
        imageSwipeOptions = {
            mainClass: 'pswp--minimal--dark',
            barsSize: {top:0,bottom:0},
            captionEl: false,
            fullscreenEl: false,
            shareEl: false,
            bgOpacity: 0.85,
            tapToClose: true,
            maxSpreadZoom: 2,
            tapToToggleControls: false    
        };
    }

})();
