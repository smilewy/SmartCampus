<template>
  <div class="examManagement">
    <el-row type="flex" justify="center">
      <el-col :span="24">
        <h3>考试管理</h3>
      </el-col>
    </el-row>
    <el-row class="em-top" type="flex" align="middle">
      <el-col :span="12">
        <el-col :span="10">
          <el-row class="em-top-grade" type="flex" align="middle">
            <el-col :span="5" class="em-top-text">年级：</el-col>
            <el-col :span="12">
              <el-select v-model="gradevalue" class="el-select" @change="changeGrade">
                <el-option
                  v-for="item in gradeoptions"
                  :key="item.gradeid"
                  :value="item.gradeid"
                  :label="item.name">
                </el-option>
              </el-select>
            </el-col>
          </el-row>
        </el-col>
        <el-col :span="10">
          <el-row class="em-top-exam" type="flex" align="middle">
            <el-col :span="5" class="em-top-text">考试：</el-col>
            <el-col :span="19">
              <el-select v-model="examvalue" class="el-select2" @change="chooseExam">
                <el-option
                  v-for="(item,index) in examoptions"
                  :key="item.index"
                  :value="item.examinationid"
                  :label="item.examination">
                </el-option>
              </el-select>
            </el-col>
          </el-row>
        </el-col>
      </el-col>
      <el-col :span="9" :offset="3" class="steps">
        <span class="step unable_edit">不可编辑</span>
        <span class="step completed">已完成</span>
        <span class="step main_process">主要流程</span>
        <span class="step sec_process">次要流程</span>
        <span class="step un_activated ">未激活</span>
      </el-col>
    </el-row>
    <el-row class="stepLists">
      <el-col :span="24" class="subClassDivision_items">
        <el-col :span="4">
          <el-row class="subClassDivision_item">
            <div class="item" style="background: #d2d2d2">{{examName || '无'}}</div>
          </el-row>
          <div class="line level">
            <i class="el-icon-arrow-right"></i>
          </div>
          <div class="line vertical reverse long">
            <i class="el-icon-arrow-up"></i>
          </div>
        </el-col>
        <el-col :span="4" :offset="1" :class="examInfo.number?'complete':'main'">
          <el-row class="subClassDivision_item">
            <div class="item" v-if="gradeoptions.length" @click="linkTo({name:'testNumberSetting',params:{gradeid:gradevalue,examinationid:examvalue}})">考号设置</div>
            <div class="item" v-else>考号设置</div>
          </el-row>
          <div class="line level">
            <i class="el-icon-arrow-right"></i>
          </div>
          <div class="line level right-down">
            <i class="el-icon-arrow-right"></i>
          </div>
        </el-col>
        <el-col :span="4" :offset="1">
          <el-row class="subClassDivision_item">
            <div class="item" style="background: #d2d2d2">学生考试</div>
          </el-row>
          <div class="line level">
            <i class="el-icon-arrow-right"></i>
          </div>
        </el-col>
        <el-col :span="4" :offset="1" :class="examInfo.import?'complete':'main'">
          <el-row class="subClassDivision_item">
            <div  v-if="examInfo.number&&gradeoptions.length" class="item" @click="linkTo({name:'importGrades',params:{examinationid:examvalue}})">导入成绩</div>
            <div class="item" v-else>导入成绩</div>
          </el-row>
          <div class="line level">
            <i class="el-icon-arrow-right"></i>
          </div>
          <div class="line level right-down">
            <i class="el-icon-arrow-right"></i>
          </div>
        </el-col>
        <el-col :span="4" :offset="1" :class="examInfo.release?'complete':'main'">
          <el-row class="subClassDivision_item">
            <div v-if="examInfo.import&&gradeoptions.length" class="item" @click="linkTo({name:'releaseResults',params:{gradeid:gradevalue,examinationid:examvalue}})">发布成绩</div>
            <div class="item" v-else>发布成绩</div>
          </el-row>
        </el-col>
      </el-col>
      <el-col :span="24" style="margin-top: 4rem;" class="subClassDivision_items">
        <el-col :span="4" :offset="5">
          <div class="line level before">
            <i class="el-icon-arrow-right"></i>
          </div>
          <el-row class="subClassDivision_item">
            <div class="item" v-if="gradeoptions.length" @click="linkTo({name:'confirmStatus',params:{examinationid:examvalue}})">各班参考学生确认</div>
            <div class="item" v-else>各班参考学生确认</div>
          </el-row>
          <div class="line vertical">
            <i class="el-icon-arrow-up"></i>
          </div>
        </el-col>
        <el-col :span="4" :offset="1">
          <el-row>
            <div class="line vertical main_process">
              <i class="el-icon-arrow-up"></i>
            </div>
          </el-row>
          <el-row class="subClassDivision_item">
            <div class="item"  v-if="examInfo.number&&gradeoptions.length" @click="linkTo({name:'testReportPrinting',params:{gradeid:gradevalue,examinationid:examvalue}})">考务报表打印</div>
            <div class="item" v-else>考务报表打印</div>
          </el-row>
        </el-col>
        <el-col :span="4" :offset="1">
          <el-row>
            <div class="line vertical main_process">
              <i class="el-icon-arrow-up"></i>
            </div>
          </el-row>
          <el-row class="subClassDivision_item">
            <div class="item" v-if="gradeoptions.length" @click="linkTo({name:'studentTestAlone',params:{gradeid:gradevalue,examinationid:examvalue}})">学生单独参考</div>
            <div class="item" v-else>学生单独参考</div>
          </el-row>
        </el-col>
        <el-col :span="4" :offset="1">
          <el-row>
            <div class="line vertical main_process">
              <i class="el-icon-arrow-up"></i>
            </div>
          </el-row>
          <el-row class="subClassDivision_item">
            <div class="item"  v-if="examInfo.import&&gradeoptions.length" @click="linkTo({name:'testScribing',params:{examinationid:examvalue}})">考试划线</div>
            <div class="item" v-else>考试划线</div>
          </el-row>
        </el-col>
      </el-col>
      <el-col :span="24" style="margin-top: 4rem;" class="subClassDivision_items">
        <el-col :span="4" :offset="5">
          <el-row class="subClassDivision_item">
            <div class="item" v-if="gradeoptions.length" @click="linkTo({name:'examinationDistribution',params:{examinationid:examvalue}})">考场分配</div>
            <div class="item" v-else>考场分配</div>
          </el-row>
          <div class="line vertical reverse">
            <i class="el-icon-arrow-up"></i>
          </div>
          <div class="line level main_process">
            <i class="el-icon-arrow-right"></i>
          </div>
        </el-col>
        <el-col :span="4" :offset="1">
          <el-row>
            <div class="line vertical main_process">
              <i class="el-icon-arrow-up"></i>
            </div>
          </el-row>
          <el-row class="subClassDivision_item">
            <div class="item" v-if="gradeoptions.length" @click="linkTo({name:'candidatesArrangement',params:{examinationid:examvalue}})">考生安排</div>
            <div class="item" v-else>考生安排</div>
          </el-row>
        </el-col>
      </el-col>
      <el-col :span="24" style="margin-top: 4rem;" class="subClassDivision_items">
        <el-col :span="4">
          <el-row class="subClassDivision_item">
            <div class="item" v-if="gradeoptions.length" @click="linkTo({name:'testTime',params:{examinationid:examvalue}})">各科目参考时间</div>
            <div class="item" v-else>各科目参考时间</div>
          </el-row>
          <div class="line level main_process">
            <i class="el-icon-arrow-right"></i>
          </div>
        </el-col>
        <el-col :span="4" :offset="1">
          <el-row class="subClassDivision_item">
            <div class="item" v-if="gradeoptions.length" @click="linkTo({name:'invigilatorTask',params:{examinationid:examvalue}})">监考任务</div>
            <div class="item" v-else>监考任务</div>
          </el-row>
          <div class="line vertical reverse">
            <i class="el-icon-arrow-up"></i>
          </div>
          <div class="line level main_process">
            <i class="el-icon-arrow-right"></i>
          </div>
        </el-col>
        <el-col :span="4" :offset="1">
          <el-row class="subClassDivision_item">
            <div v-if="gradeoptions.length" @click="linkTo({name:'proctorArrangement',params:{examinationid:examvalue}})" class="item">监考安排</div>
            <div class="item" v-else>监考安排</div>
          </el-row>
          <div class="line circle">
            <i class="el-icon-arrow-up"></i>
          </div>
        </el-col>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import req from '../../../../../assets/js/common'

  export default {
    data() {
      return {
        gradeoptions: [],
        gradevalue: '',
        examoptions: [],
        examInfo: {},
        examvalue: '',
        examName: '',
      }
    },
    created() {
      let param = {};
      let self = this;
      if(sessionStorage.getItem('selectParam')){
        let selectParam = JSON.parse(sessionStorage.getItem('selectParam'));
        sessionStorage.removeItem('selectParam');
        self.gradeoptions = selectParam.gradeoptions;
        self.gradevalue = selectParam.gradevalue;
        self.examoptions = selectParam.examoptions;
        self.examvalue = selectParam.examvalue;
        self.examName = selectParam.examName;
        self.examInfo = selectParam.examInfo;
        req.ajaxSend('/school/Examination/exmanagement/type/interface/typename/exselect', 'post', param, (res) => {
          if(res.length){
            self.examInfo = res.filter(val=>val.gradeid===self.gradevalue)[0].ex.filter(val=>val.examinationid===self.examvalue)[0];
          }
        })
      }else{
        req.ajaxSend('/school/Examination/exmanagement/type/interface/typename/exselect', 'post', param, (res) => {
          if(res.length){
            self.gradeoptions = res;
            self.gradevalue = res[0].gradeid;
            self.examoptions = res[0].ex;
            self.examvalue = res[0].ex[0].examinationid;
            self.examName = res[0].ex[0].examination;
            self.examInfo = res[0].ex[0];
          }
        })
      }
    },
    methods: {
      linkTo(data){
        sessionStorage.setItem('selectParam',JSON.stringify({
          gradeoptions:this.gradeoptions,
          gradevalue:this.gradevalue,
          examoptions : this.examoptions,
          examvalue:this.examvalue,
          examName: this.examName,
          examInfo : this.examInfo
        }));
        this.$router.push(data);
      },
      changeGrade() {
        for (let obj of this.gradeoptions) {
          if (obj.gradeid === this.gradevalue) {
            this.examoptions = obj.ex;
            this.examvalue = obj.ex[0].examinationid;
            this.examName = obj.ex[0].examination;
            this.examInfo = obj.ex[0];
          }
        }
      },
      chooseExam(id){
        this.examName = this.examoptions.filter(val=>val.examinationid===id)[0].examination;
        this.examInfo = this.examoptions.filter(val=>val.examinationid===id)[0];
      }
    },
  }
</script>
<style lang="less">
  .examManagement {
    .em-top {
      height: 8rem;
    }
    .em-top .step:before {
      position: absolute;
      display: block;
      content: '';
      width: .6rem;
      height: .6rem;
      top: 50%;
      -webkit-transform: translateY(-50%);
      -moz-transform: translateY(-50%);
      -ms-transform: translateY(-50%);
      -o-transform: translateY(-50%);
      transform: translateY(-50%);
      left: -1.5rem;
      border-radius: 100%;
    }
    .steps {
      text-align: right;
    }
    .step {
      margin-left: 3rem;
    }
    .step {
      position: relative;
      font-size: .875rem;
    }
    .step.unable_edit:before{
      background: #d2d2d2;
      color: #fff;
      text-align: center;
    }
    .step.completed:before{
      background: #13b5b1;
      color: #fff;
      text-align: center;
    }
    .step.main_process:before{
      background: #4da1ff;
      color: #fff;
      text-align: center;
    }
    .step.sec_process:before{
      background: #89bcf5;
      color: #fff;
      text-align: center;
    }
    .step.un_activated:before{
      border: 2px solid #d2d2d2;
    }
    .stepLists {
      width: 100%;
      margin:0 auto 10rem auto;
      overflow: hidden;
    }
    .subClassDivision_items > div {
      position: relative;
    }
    .subClassDivision_items .item {
      display: inline-block;
      position: relative;
      z-index: 2;
      width: 12.5rem;
      padding: .75rem 0;
      border-radius: 1.5rem;
      font-size: .875rem;
      text-align: center;
      background: #89BCF5;
      color: #fff;
      cursor: pointer;

    }
    .subClassDivision_items .line {
      border-color: #89BCF5;
    }
    .subClassDivision_items i{
      color: #89BCF5;
    }
    .subClassDivision_items .complete{
      .line{
        border-color: #13b5b1;
      }
      .item{
        background: #13b5b1;
      }
      i{
        color:#13b5b1;
      }
    }
    .subClassDivision_items .main{
      .line{
        border-color: #4da1ff;
      }
      .item{
        background: #4da1ff;
      }
      i{
        color:#4da1ff;
      }
    }
    .line {
      position: relative;
      z-index: 2;
      border-width: 2px;
    }
    .circle{
      position: absolute;
      left: -4rem;
      top: -15rem;
      z-index: 1;
      width: 20rem;
      height: 20rem;
      border-radius: 10rem;
      border-right:2px solid;
      &:after{
        content: ' ';
        display: block;
        position: absolute;
        background: white;
        left: 0;
        top: 0;
        height: 100%;
        width: 90%;
      }
      i{
        position: absolute;
        z-index: 2;
        left: 86%;
        top: 16%;
        transform: rotate(-45deg);
      }
    }
    .level {
      position: absolute;
      width: 2.5rem;
      display: inline-block;
      border-bottom: 2px solid;
      top: 50%;
      left: 90%;
    }
    .level.right-down {
      width: 3rem;
      top: 170%;
      transform: rotate(30deg);
    }
    .level.before {
      left: -36%;
    }
    .level i {
      position: absolute;
      bottom: -.5rem;
      left: 2.6rem;
    }
    .vertical {
      position: absolute;
      height: 2.5rem;
      display: inline-block;
      border-left: .125rem solid;
      left: 42%;
      top: -3rem;
    }
    .vertical.reverse {
      transform: rotate(180deg);
    }
    .vertical.long {
      height:13rem;
      top: 3.5rem;
    }
    .vertical i {
      top: -0.7rem;
      left: -0.52rem;
      position: absolute;
    }
  }
</style>
