<template>
  <div class="scheduleStudent">
    <el-row type="flex" align="middle">
      <h3>班级课表</h3>
    </el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-button class="delete" title="导出" @click="operationTable('out')">
        <img class="delete_unactive"
             src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
             alt="">
        <img class="delete_active"
             src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
             alt="">
      </el-button>
      <el-button-group class="secBtn-group">
        <el-button class="filt" title="复制">
          <img class="filt_unactive"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
               alt="">
          <img class="filt_active"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
               alt="">
        </el-button>
        <el-button class="delete" title="打印">
          <img class="delete_unactive"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
               alt="">
          <img class="delete_active"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
               alt="">
        </el-button>
      </el-button-group>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        border
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column v-for="n in 9" :key="n" :label="weekData[n-1]">
          <template slot-scope="scope">
            <span v-if="n==1 || n==2">{{scope.row[n-1].subjectName}}</span>
            <div v-if="n!=1 && n!=2">
              <div class="notHasClass" v-if="scope.row[n-1].statu==0">不上课</div>
              <div class="hasClass" v-if="scope.row[n-1].statu!=0">
                <p>{{scope.row[n-1].subjectName}}</p>
                <p v-if="scope.row[n-1].teacherName">（{{scope.row[n-1].teacherName}}）</p>
              </div>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        weekData: ['节/周', '上课时间', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'],
        tableData: [],
        loading: false
      }
    },
    created: function () {
      this.loadData();
    },
    methods: {
      operationTable(type){
        if (this.tableData.length == 0) {
          this.vmMsgWarning('没有可以导出的数据！');
          return false;
        }
        if (type == 'out') {
          req.downloadFile('.scheduleStudent', '/school/Schedule/sudent?type=studentClassTableExport', 'post');
        }
      },
      loadData(){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Schedule/sudent?type=studentClassTable', 'get', '', function (res) {
          self.loading = false;
          if (res.statu == 1) {
            self.tableData = res.data;
          } else {
            self.vmMsgError(res.message);
          }
        })
      }
    }
  }
</script>
<style>
  .scheduleStudent {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .scheduleStudent h3 {
    font-size: 1.25rem;
  }

  .scheduleStudent .alertsBtn {
    margin: 2.5rem 0 1.25rem 0;
  }

  .scheduleStudent .el-table th, .scheduleStudent .el-table td {
    text-align: center;
  }

  .scheduleStudent .el-table td {
    padding: 1.5rem 0;
  }

  .scheduleStudent .hasClass {
    font-weight: bold;
  }

  .scheduleStudent .notHasClass {
    color: #999999;
  }
</style>
