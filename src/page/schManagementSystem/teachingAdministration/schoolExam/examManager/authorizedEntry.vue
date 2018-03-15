<template>
  <div class="authorizedEntry">
    <el-row>
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <span class="breadcrumb"><router-link :to="{name:'importGrades',params:{examinationid:selectParam.examinationid}}" tag="span">成绩录入</router-link>
        <span class="breadcrumb_active">授权他们录入</span></span>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        @sort-change="sort"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          prop="title"
          label="序号" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="type"
          label="科目" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="publisher"
          label="授权地址" sortable="custom">
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="copy">复制链接</span><span class="gap">|</span><span class="reAuthorize">重新生成授权</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="testOperation_btn">
      <el-button type="primary" @click="saveMsg">批量生成</el-button>
    </el-row>
    <el-row class="pageAlerts">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="currentPage"
        :page-size="pageALl"
        layout="prev, pager, next, jumper"
        :total="1000">
      </el-pagination>
    </el-row>
  </div>
</template>
<script>
  export default{
    data(){
      return {
        tableData: [{
          id: 1,
          title: 1,
          type: '通知',
          publisher: '超级管理员',
          publishPart: 'hjshf',
          date: '2016-05-03 16:16:55',
          vState: '已查阅',
          reportData: false,
          joinTest:false
        }, {
          id: 2,
          title: 2,
          type: '通知',
          publisher: 'wy',
          publishPart: 'hjshf',
          date: '2016-05-03 16:16:55',
          vState: '未查阅',
          reportData: false,
          joinTest:false
        }, {
          id: 3,
          title: 3,
          type: '通知',
          publisher: '超级管理员',
          publishPart: 'hjshf',
          date: '2016-05-03 16:16:55',
          vState: '已查阅',
          reportData: false,
          joinTest:false
        }],
        currentPage: 1,
        pageALl: 100,
        selectParam:{
          examinationid:''
        },
        loading:false
      }
    },
    created:function () {
      this.selectParam.examinationid=this.$route.params.examinationid;
    },
    methods: {
      handleCurrentChange(val) {
        this.currentPage = val;
      },
      returnFlowchart(){
        this.$router.push('/examManagerHome');
      },
      sort(column){
      },
      saveMsg(){
      }
    }
  }
</script>
<style>
  .authorizedEntry .alertsList{
    margin-top:2rem;
  }
  .authorizedEntry .copy,.authorizedEntry .reAuthorize{
    cursor: pointer;
  }
  .authorizedEntry .copy{
    color: #13b5b1;
  }
  .authorizedEntry .reAuthorize{
    color: #4da1ff;
  }
  .authorizedEntry .gap{
    margin:0 .6rem;
  }
</style>
