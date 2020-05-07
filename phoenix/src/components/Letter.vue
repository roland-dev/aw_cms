<template>
    <div> 
      <div id="toolbar" class="toolbar"></div>
      <div :id="id" class="text" style="text-align:left"></div>
    </div>
</template>

<script>
    import E from 'wangeditor'
    import Env from '@/http/env'
    // import axios from 'axios'
    export default {
      name: 'Message',
      props: ['editorId', 'content'],
      data () {
        return {
          id: this.editorId,
          editorContent: this.content,
          editor: {}
        }
      },
      methods: {
        getContent: function (content) {
          this.editor.txt.html(content)
          this.editorContent = content
        },
        setContent: function () {
          if (this.editorContent === '<p><br></p>') {
            this.editorContent = ''
          }
          return this.editorContent
        },
        clear: function () {
          this.editor.txt.clear()
          this.editorContent = ''
        }
      },
      mounted () {
        this.editor = new E('#toolbar', `#${this.id}`)
        this.editor.customConfig.menus = ['emoticon', 'image']
        this.editor.customConfig.zIndex = 1000
        this.editor.customConfig.onchange = (html) => {
          html = html.replace(/<a href[^>]*>/gi, '')
          html = html.replace(/<\/a>/gi, '')
          html = html.replace(/https:\/\/mmbiz\.qpic\.cn/gi, 'http://res.zhongyingtougu.com')           // 兼容显示微信内图片更换为cdn地址(cdb处已处理)
          html = html.replace(/<phelvetica[^>]*>/gi, '')
          html = html.replace(/tp=webp/gi, '')
          html = html.replace(/wxfrom=5/gi, '')
          html = html.replace(/wx_lazy=1/gi, '')
          html = html.replace(/wx_co=1/gi, '')
          html = html.replace(/crossorigin="anonymous"/gi, '')
          this.editorContent = html
          this.setContent()
        }
        this.editor.customConfig.uploadImgServer = `${Env.baseURL}/resource/image`
        this.editor.customConfig.debug = true
        this.editor.customConfig.uploadImgMaxSize = 3 * 1024 * 1024
        this.editor.customConfig.uploadImgMaxLength = 5
        this.editor.customConfig.showLinkImg = false
        this.editor.customConfig.pasteFilterStyle = true
        this.editor.customConfig.pasteTextHandle = function (content) {
          if (content.indexOf('<!--[if gte mso') > -1) {
            alert('请使用【crtl+shift+v】的方式进行复制粘贴')
            return ''
          } else if (content.length > 60000) {   // 数据库存储影响
            alert('内容超出限制了，请排除html代码影响，建议先复制到记事本，再复制到富文本')
            return ''
          } else {
            return content
          }
        }
        this.editor.customConfig.withCredentials = true
        this.editor.customConfig.uploadFileName = 'image'
        this.editor.customConfig.uploadImgParams = {
          'type': 'wangeditor'
        }
        // 配置表情
        this.editor.customConfig.emotions = [
          {
            // tab 的标题
            title: '默认',
            // type -> 'emoji' / 'image'
            type: 'image',
            // content -> 数组
            content: [
              {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/ac/smilea_thumb.gif',
                alt: '[呵呵]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/0b/tootha_thumb.gif',
                alt: '[嘻嘻]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/6a/laugh.gif',
                alt: '[哈哈]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/14/tza_thumb.gif',
                alt: '[可爱]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/af/kl_thumb.gif',
                alt: '[可怜]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/a0/kbsa_thumb.gif',
                alt: '[挖鼻屎]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/f4/cj_thumb.gif',
                alt: '[吃惊]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/6e/shamea_thumb.gif',
                alt: '[害羞]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/c3/zy_thumb.gif',
                alt: '[挤眼]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/29/bz_thumb.gif',
                alt: '[闭嘴]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/71/bs2_thumb.gif',
                alt: '[鄙视]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/6d/lovea_thumb.gif',
                alt: '[爱你]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/9d/sada_thumb.gif',
                alt: '[泪]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/19/heia_thumb.gif',
                alt: '[偷笑]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/8f/qq_thumb.gif',
                alt: '[亲亲]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/b6/sb_thumb.gif',
                alt: '[生病]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/58/mb_thumb.gif',
                alt: '[太开心]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/17/ldln_thumb.gif',
                alt: '[懒得理你]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/98/yhh_thumb.gif',
                alt: '[右哼哼]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/6d/zhh_thumb.gif',
                alt: '[左哼哼]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/a6/x_thumb.gif',
                alt: '[嘘]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/af/cry.gif',
                alt: '[衰]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/73/wq_thumb.gif',
                alt: '[委屈]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/9e/t_thumb.gif',
                alt: '[吐]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/f3/k_thumb.gif',
                alt: '[打哈欠]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/27/bba_thumb.gif',
                alt: '[抱抱]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/7c/angrya_thumb.gif',
                alt: '[怒]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/5c/yw_thumb.gif',
                alt: '[疑问]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/a5/cza_thumb.gif',
                alt: '[馋嘴]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/70/88_thumb.gif',
                alt: '[拜拜]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/e9/sk_thumb.gif',
                alt: '[思考]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/24/sweata_thumb.gif',
                alt: '[汗]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/7f/sleepya_thumb.gif',
                alt: '[困]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/6b/sleepa_thumb.gif',
                alt: '[睡觉]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/90/money_thumb.gif',
                alt: '[钱]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/0c/sw_thumb.gif',
                alt: '[失望]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/40/cool_thumb.gif',
                alt: '[酷]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/8c/hsa_thumb.gif',
                alt: '[花心]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/49/hatea_thumb.gif',
                alt: '[哼]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/36/gza_thumb.gif',
                alt: '[鼓掌]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/d9/dizzya_thumb.gif',
                alt: '[晕]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/1a/bs_thumb.gif',
                alt: '[悲伤]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/62/crazya_thumb.gif',
                alt: '[抓狂]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/91/h_thumb.gif',
                alt: '[黑线]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/6d/yx_thumb.gif',
                alt: '[阴险]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/89/nm_thumb.gif',
                alt: '[怒骂]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/40/hearta_thumb.gif',
                alt: '[心]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/ea/unheart.gif',
                alt: '[伤心]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/58/pig.gif',
                alt: '[猪头]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/d6/ok_thumb.gif',
                alt: '[ok]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/d9/ye_thumb.gif',
                alt: '[耶]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/d8/good_thumb.gif',
                alt: '[good]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/c7/no_thumb.gif',
                alt: '[不要]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/d0/z2_thumb.gif',
                alt: '[赞]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/40/come_thumb.gif',
                alt: '[来]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/d8/sad_thumb.gif',
                alt: '[弱]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/91/lazu_thumb.gif',
                alt: '[蜡烛]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/6a/cake.gif',
                alt: '[蛋糕]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/d3/clock_thumb.gif',
                alt: '[钟]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/1b/m_thumb.gif',
                alt: '[话筒]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/7a/shenshou_thumb.gif',
                alt: '[草泥马]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/60/horse2_thumb.gif',
                alt: '[神马]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/bc/fuyun_thumb.gif',
                alt: '[浮云]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/c9/geili_thumb.gif',
                alt: '[给力]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/f2/wg_thumb.gif',
                alt: '[围观]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/70/vw_thumb.gif',
                alt: '[威武]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/6e/panda_thumb.gif',
                alt: '[熊猫]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/81/rabbit_thumb.gif',
                alt: '[兔子]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/bc/otm_thumb.gif',
                alt: '[奥特曼]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/15/j_thumb.gif',
                alt: '[囧]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/89/hufen_thumb.gif',
                alt: '[互粉]'
              }, {
                src: 'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/c4/liwu_thumb.gif',
                alt: '[礼物]'
              }
            ]
          }
        ]
        this.editor.create()
        // console.log(this.editor)
      }
    }
</script>

<style scoped>
.toolbar {
  border: 1px solid #ccc;
}
.text {
  border: 1px solid #ccc;
  height: 140px;
  margin-bottom: 10px;
}
.w-e-text{
  overflow: hidden;
}
</style>
