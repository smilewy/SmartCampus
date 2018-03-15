<template>
  <div class="importGrades">
    <el-row>
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <span class="breadcrumb"><span class="breadcrumb_active">成绩录入</span>
        <!--<router-link :to="{name:'authorizedEntry',params:{examinationid:selectParam.examinationid}}" tag="span">授权他们录入</router-link>-->
      </span>
    </el-row>
    <el-row class="importGrades_process" v-for="(data,index) in tableData" :key="index">
      <el-row class="subjectName">
        <span class="exam_subTitle">{{data.branchname}}</span>
      </el-row>
      <el-row class="subjectLists">
        <div class="subject_row" v-for="(subject,i) in data.data" :key="i">
          <el-row type="flex" align="middle">
            <el-col :span="12">
              <!--<chart :options="i"></chart>-->
              <el-progress :width="100" :stroke-width="10" type="circle" :percentage="subject.ratio"></el-progress>
            </el-col>
            <el-col :span="12">
              <h6>{{subject.subject}}</h6>
              <p>总数：{{subject.all}}</p>
              <p>已录：{{subject.input}}</p>
              <p>未录：{{subject.uninput}}</p>
            </el-col>
          </el-row>
          <el-button type="primary" class="import" @click="toNext(index,i)">成绩录入</el-button>
        </div>
      </el-row>
    </el-row>
  </div>
</template>
<script>
  //  import circle from './circleChart.vue'
  import req from '@/assets/js/common'
  export default{
    /*components: {
     'chart': circle
     },*/
    data(){
      return {
        tableData: [],
        selectParam: {
          examinationid: ''
        }
      }
    },
    created: function () {
      var exid = this.$route.params.examinationid, self = this;
      self.selectParam.examinationid = exid;
      req.ajaxSend('/school/Examination/exmanagement/type/resultin/typename/resultfind', 'post', self.selectParam, function (res) {
        self.tableData = res;
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/examManagerHome');
      },
      toNext(idx,i){
          var data={
            examinationid:this.selectParam.examinationid,
            branchid:this.tableData[idx].data[i].branchid,
            subjectid:this.tableData[idx].data[i].subjectid
          };
        this.$router.push({name:'selfEntry',params:data});
      }
    }
  }
</script>
<style>
  .importGrades_process {
    margin-top: 2rem;
  }

  .importGrades .subjectName {
    margin-bottom: 2rem;
  }

  .importGrades .subjectName .exam_subTitle {
    display: inline-block;
    width: 6.25rem;
    height: 2rem;
    line-height: 2rem;
    padding: 0;
    border-radius: 0 15px 15px 0;
    -webkit-box-shadow: 0 5px 5px 0 #ddd;
    -moz-box-shadow: 0 5px 5px 0 #ddd;
    box-shadow: 0 5px 5px 0 #ddd;
    background-color: #89bcf5;
    border-color: #89bcf5;
    color: #fff;
    text-align: center;
  }

  .importGrades .subject_row {
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

  .importGrades .subjectLists > div:last-child {
    margin-right: 0;
  }

  .importGrades .subject_row h6,
  .importGrades .subject_row p {
    font-size: .875rem;
    padding-left: 16px;
    margin: 10px 0;
  }

  .importGrades .subject_row .el-button.import {
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
