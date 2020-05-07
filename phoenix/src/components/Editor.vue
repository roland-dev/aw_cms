<template>
    <div>
        <div :id="id" style="text-align:left"></div>
        <!-- <button v-on:click="getContent">查看内容</button> -->
    </div>
</template>

<script>
    import E from 'wangeditor'
    import Env from '@/http/env'
    import axios from 'axios'
    export default {
      name: 'Editor',
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
        this.editor = new E(`#${this.id}`)
        this.editor.customConfig.onchange = (html) => {
          this.editorContent = html
          this.setContent()
        }
        this.editor.customConfig.colors = [
          '#880000',
          '#800080',
          '#ff0000',
          '#ff00ff',
          '#000080',
          '#0000ff',
          '#00ffff',
          '#008080',
          '#008000',
          '#808000',
          '#00ff00',
          '#ffcc00',
          '#808080',
          '#c0c0c0',
          '#000000',
          '#ffffff'
        ]
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
        this.editor.customConfig.customUploadImg = function (files, insert) {
          let formData = new FormData()
          formData.append('image', files[0])
          axios.post(`${Env.baseURL}/resource/image`, formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }).then(res => {
            if (res.data.code === 0) {
              insert(res.data.data.path)
            } else {
              alert('图片格式不正确！')
            }
          })
        }
        this.editor.customConfig.menus = [
          'bold',  // 粗体
          'italic',  // 斜体
          'underline',  // 下划线
          'strikeThrough',  // 删除线
          'head',  // 标题
          'fontSize',  // 字号
          'fontName',  // 字体
          'foreColor',  // 文字颜色
          'backColor',  // 背景颜色
          'link',  // 插入链接
          'list',  // 列表
          'justify',  // 对齐方式
          'quote',  // 引用
          'emoticon',  // 表情
          'image',  // 插入图片
          'table',  // 表格
          'video',  // 插入视频
          'code',  // 插入代码
          'undo'  // 撤销
        ]
        this.editor.create()
        // 处理视频链接
        Object.getPrototypeOf(this.editor.menus.menus.video)._insert = function _insert (val) {
          let editor = this.editor
          let s
          let playVideoUrl = 'https://cms.zhongyingtougu.com/play_video_mini.php?vid='
          if (val.indexOf('<iframe') >= 0) {
            s = `<p class="video" id="zytg_player">${val}</p>`
          } else {
            if (val.indexOf('http') >= 0) {
              // 腾讯视频判断
              if (val.indexOf('v.qq.com') >= 0) {
                if (val.indexOf('vid') >= 0) {
                  s = playVideoUrl + val.substring(val.indexOf('vid=') + 4, val.lastIndexOf('&auto'))
                } else if (val.indexOf('page/') >= 0) {
                  s = playVideoUrl + val.substring(val.indexOf('page/') + 5, val.lastIndexOf('.html'))
                } else {
                  s = val
                }
              } else {
                s = val
              }
            } else {
              alert('请输入正确的链接格式')
              return false
            }
            s = `<p class="video" id="zytg_player"><iframe src="${s}" frameborder="0" allowfullscreen="allowfullscreen"></iframe></p>`
          }
          editor.cmd.do('insertHTML', s)
        }
      }
    }
</script>

<style scoped>
.w-e-panel-tab-title li{
  list-style: none
}
</style>
