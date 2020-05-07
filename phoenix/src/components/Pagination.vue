<template>
  <div id="pagination">
    <!-- 分页 -->
      <div class="block" v-if="totalNum > pageSize">
        <el-pagination
          @current-change="handleCurrentChange"
          :page-size="pageSize"
          :current-page="curPage"
          layout="prev, pager, next"
          :total="totalNum">
        </el-pagination>
      </div>
  </div>
</template>

<script>
export default {
  name: 'Pagination',
  props: [
    'size',         // 列表每页展示条数
    'page',         // 列表当前页码
    'total'         // 列表总条数
  ],
  data () {
    return {
      pageSize: 10,    // 每页展示条数
      curPage: 1,      // 当前分页
      totalNum: 0,     // 总数
      totalPage: 0     // 分页数
    }
  },
  mounted () {
    this.$nextTick(() => {
      this.initPage()
    })
  },
  methods: {
    // 初始化分页数据
    initPage () {
      if (this.size) {
        this.pageSize = Number(this.size)
      }
      if (this.total) {
        var total = Number(this.total)
        this.totalNum = total
        this.totalPage = Math.ceil(total / this.pageSize)
      }
      if (this.page) {
        this.curPage = Number(this.page)
      }
    },

    // 修改分页
    handleCurrentChange (page) {
      this.$emit('setPage', page)
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
