<template>
  <div class="scoresManagement">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回上一步</span></el-button>
      <h3>成绩管理</h3>
    </el-row>
    <el-row class="subClassDivision_row subjectLists">
      <div class="subject_row" v-for="(subject,idx) in option" :key="subject.id">
        <el-row type="flex" align="middle">
          <el-col :span="12">
            <el-progress :width="100" :stroke-width="10" type="circle" :percentage="subject.ratio*100"></el-progress>
          </el-col>
          <el-col :span="12">
            <h6>{{subject.subject}}</h6>
            <p>总数：{{subject.totalNumber}}</p>
            <p>已录：{{subject.recordNumber}}</p>
            <p>未录：{{subject.notNumber}}</p>
          </el-col>
        </el-row>
        <el-button type="primary" class="import" @click="toNext(idx)">成绩录入</el-button>
      </div>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        option: [],
        pId: '',
        examId: ''
      }
    },
    created: function () {
      var self = this,data;
      self.pId = self.$route.params.planId;
      self.examId = self.$route.params.examId;
      data={
        planId:self.pId,
        examId:self.examId
      };
      req.ajaxSend('/school/DivideBranch/scoreManage', 'post',data,function (res) {
          self.option=res.data||[];
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.go(-1);
      },
      toNext(val){
        this.$router.push({name: 'scoresEntry', params: {planId: this.pId, examId: this.examId,subId:this.option[val].id}});
      }
    }
  }
</script>
<style>
  .scoresManagement .subject_row {
    position: relative;
    float: left;
    width: 240px;
    margin-right: 1rem;
    margin-bottom: 30px;
    border: 1px solid #d2d2d2;
    border-radius: 6px;
    padding: 20px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
  }

  .scoresManagement .subjectLists > div:last-child {
    margin-right: 0;
  }

  .scoresManagement .subject_row h6,
  .scoresManagement .subject_row p {
    font-size: .875rem;
    padding-left: 16px;
    margin: 10px 0;
  }

  .scoresManagement .subject_row .el-button.import {
    position: absolute;
    left: 50%;
    -webkit-transform: translateX(-50%);
    -moz-transform: translateX(-50%);
    -ms-transform: translateX(-50%);
    -o-transform: translateX(-50%);
    transform: translateX(-50%);
    bottom: -18px;
    border-radius: 20px;
    padding: 10px 20px;
  }
</style>
