<template>
  <div class="releaseResults">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>发布成绩</h3>
    </el-row>
    <el-row class="examManager_row switch" type="flex" align="middle">
      <span>向学生和家长发布排名：</span>
      <el-switch
        v-model="isPublic"
        active-color="#13b5b1"
        inactive-color="#ff4949"
        active-text="是"
        inactive-text="否">
      </el-switch>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          prop="branch"
          label="科类">
        </el-table-column>
        <el-table-column
          prop="sunbject"
          label="科目">
        </el-table-column>
        <el-table-column
          prop="all"
          label="考试人数（人）">
        </el-table-column>
        <el-table-column
          prop="input"
          label="已录入成绩（人）">
        </el-table-column>
        <el-table-column
          prop="uninput"
          label="未录入成绩（人）">
        </el-table-column>
        <el-table-column
          prop="ratio"
          label="完成度（%）">
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="testOperation_btn">
      <el-button type="primary" v-if="!isPublish" @click="releaseResults(1)">发布成绩</el-button>
      <el-button type="primary" v-if="isPublish" @click="releaseResults(0)">取消发布</el-button>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        isPublic: true,
        isPublish: true,
        tableData: [],
        selectParam: {
          examinationid: ''
        },
        publishParam: {
          examinationid: '',
          release: '',
          ranking: ''
        },
        loading: false
      }
    },
    created: function () {
      var self = this;
      self.selectParam.examinationid = self.$route.params.examinationid;
      self.publishParam.examinationid = self.selectParam.examinationid;
      self.loading = true;
      req.ajaxSend('/school/Examination/exmanagement/type/release/typename/find', 'post', self.selectParam, function (res) {
        self.tableData = res.data;
        self.isPublish = res.state.release == '1';
        self.isPublic = res.state.ranking == '1';
        self.loading = false;
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/examManagerHome');
      },
      releaseResults(type){
        var self = this, msg = '', msg1 = '';
        self.publishParam.ranking = self.isPublic ? 1 : 0;
        self.publishParam.release = type;
        msg = self.isPublish ? '取消成功！' : '发布成功！';
        msg1 = self.isPublish ? '取消失败！' : '发布失败！';
        req.ajaxSend('/school/Examination/exmanagement/type/release/typename/do', 'post', self.publishParam, function (res) {
          if (res.return) {
            self.vmMsgSuccess(msg);
            self.isPublish = !self.isPublish;
          } else {
            self.vmMsgError(msg1);
          }
        })
      }
    }
  }
</script>
<style>
  .releaseResults .examManager_row .el-button--primary {
    background-color: #13b5b1;
    border-color: #13b5b1;
    height: 36px;
    padding: 0 15px;
  }
</style>
