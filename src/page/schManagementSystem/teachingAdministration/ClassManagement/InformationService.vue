<template>
  <div class="g-container InformationService">
    <header class="g-header">
      <div class="gh-header">信息管理</div>
      <div class="gh-section notRequire clear_fix">
        <el-form ref="studentMessge" :rules="studentMsgRules" :model="dataHeader"
                 label-width="86px">
          <el-form-item label="年级：" prop="grade">
            <el-select class="g-select" @change="gradeClick" v-model="dataHeader.grade" placeholder="请选择年级">
              <el-option v-for="(content,index) in headerButtonData.gradeloadData"
                         :label="content.znGradeName"
                         :key="content.gradeid"
                         :value="content.gradeid"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="班级：" prop="classes">
            <el-select class="g-select" v-model="dataHeader.classes" placeholder="请选择班级">
              <el-option label="全选" value="all" :disabled="!headerButtonData.classesLoadData.length>0"></el-option>
              <el-option v-for="(content,index) in headerButtonData.classesLoadData"
                         :key='index' :label="content.classname"
                         :value="content.classid"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="信息类别：" prop="messageType">
            <el-select class="g-select" v-model="dataHeader.messageType" placeholder="请选择信息类别">
              <el-option v-for="(content,index) in headerButtonData.msgTypeLoadData" :key="index" :label="content.name"
                         :value="content.ListType"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
      <div class="gh-buttonGroup clear_fix">
        <el-button type="primary" @click="searchClick('studentMessge',0)">查询</el-button>
      </div>
    </header>
    <section class="g-section">
      <div class="gs-header">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="buttonClick" data-msg="export" class="filt" title="导出">
              <img class="filt_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"/>
              <img class="filt_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"/>
            </el-button>
            <el-button data-msg="print" class="filt" title="打印预览"  @click="operationData('print')">
              <img class="filt_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"/>
              <img class="filt_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"/>
            </el-button>
            <!--<el-button @click="buttonClick" data-msg="delete" class="filt" title="删除">-->
              <!--<img class="filt_unactive"-->
                   <!--src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete.png"/>-->
              <!--<img class="filt_active"-->
                   <!--src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete_highlight.png"/>-->
            <!--</el-button>-->
          </el-button-group>
          <el-button-group class="elGroupButton_two">
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="班级/姓名"
                    @change="fuzzyClick"></el-input>
        </div>
      </div>
      <div class="gs-table alertsList">
        <el-table
            height="550"
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          class="g-fixedColumn" v-show="headerButtonData.hasBasicData" ref="studentMsgTable"
                  :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange"
                  @selection-change="handleStudentTable">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <!--<el-table-column type="selection"></el-table-column>-->
          <el-table-column label="序号" width="110" type="index" fixed></el-table-column>
          <el-table-column min-width="180" v-for="(content,index) in headerButtonData.studentBasicTr" :key="index"
                           :label="content.zh"
                           :prop="content.en" sortable></el-table-column>
          <!--<el-table-column label="操作" fixed="right" width="80">-->
            <!--<template slot-scope="scope">-->
              <!--<el-button @click="changeBasicMsg(scope.$index)" type="text" size="small">修改</el-button>-->
            <!--</template>-->
          <!--</el-table-column>-->
        </el-table>
      </div>
    </section>
    <footer v-show="headerButtonData.hasBasicData" class="g-footer">
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="currentPage"
          layout="prev, pager, next, jumper"
          :page-count="pageALl">
        </el-pagination>
      </el-row>
    </footer>
  </div>
</template>
<script>
  import {
    studentMessageGradeLoad, studentMessageClassLoad, studentMessageTypeLoad,
    /*页面加载数据*/
    studentMessageSearchLoad,
    studentMessageDelete,//删除数据
    studentMessageUpdate,//修改数据
    studentMessageExport,//导出数据
  } from '@/api/http'
  import req from '@/assets/js/common'
  import moment from 'moment'
  export default{
    data(){
      var regPhone = /0?(13|14|15|18)[0-9]{9}$/,
        regEmail = /\w+((-w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+/;
      var validatePhone = (rule, value, callback) => {
        if (value && !regPhone.test(value)) {
          callback(new Error('请输入正确手机号'));
        } else {
          callback();
        }
      };
      var validateEmail = (rule, value, callback) => {
        if (value && !regEmail.test(value)) {
          callback(new Error('请输入正确邮箱格式'));
        } else {
          callback();
        }
      };
      return {
        isLoading:false,
        /*form表单规则*/
        studentMsgRules: {
          grade: [{required: true, message: ''}],
          classes: [{required: true, message: ''}],
          messageType: [{required: true, message: ''}],
        },
        /*ajax data*/
        headerButtonData: {
          gradeloadData: [],//年级返回数组
          classesLoadData: [],//班级返回数组
          msgTypeLoadData: [],//信息类别返回数组
          /*下面表格信息数据*/
          hasBasicData: false,
          studentBasicMsg: [],//学生基本信息数据
          studentBasicTr: [],//学生基本信息列
        },
        /*ajax params*/
        ajaxClasses: [],
        ajaxGrade: '',
        /*form表单双向绑定数据*/
        dataHeader: {
          grade: '',
          classes: '',
          messageType: ''
        },
        fuzzyInput: '',
        mutipleChoose: [],
        /*page*/
        pageALl: 1,
        currentPage: 1,
        pageSize: 50,
        /*弹框*/
        dialogTitle: '学生补录',
        isDialog: false,
        activeForm: 'student_info',
        activeChoice: '',
        student_infoTime1: '',
        student_infoTime2: '',
        student_infoTime3: '',
        branchList: [],
        majorList: [],
        majorState: false,
        classState: false,
        serialNumberState:false,
        formClassList: [],
        updateData: {
          /*基本信息*/
          student_info: {
            logo: '',
            gradeId: '',
            classId: '',
            sex: '',
            birthday: '',
            serialNumber: '',
            height: '',
            idCard: '',
            country: '',
            nation: '',
            leagueTime: '',
            enrolTime: '',
            enrolWay: '',
            isResSchool: '',
            phone: '',
            homePath: '',
            homePostcode: '',
            nowHomePath: '',
            nowHomePostcode: '',
            dorStore: '',
            dorStorey: '',
            dorNumber: '',
            studentCard: ''
          },
          /*学籍信息*/
          school_rollinfo: {
            isAtSchool: '是',
            isLeave: '0',
            isSubor: '0',
            isTempStudy: '0',
            stream: '',
            major: '',
            nationalNumber: '',
            provinceNumber: '',
            cityNumber: '',
            admCategory: '',
            eleSchool: '',
            secSchool: '',
            midExam: ''
          },
          /*户籍信息*/
          cen_reg_info: {
            perAddress: '',
            perAddressCode: '',
            cenRegType: '',
            originCode: '',
            cenRegNature: ''
          },
          /*家长信息*/
          parents_info: {
            jzName1: '',
            relation1: '',
            jzPhone1: '',
            resAddress1: '',
            jzWorkUnit1: '',
            jzPost1: '',
            jzCardType1: '',
            jzIdCard1: '',
            jzNation1: '',
            jzName2: '',
            relation2: '',
            jzPhone2: '',
            jzWorkUnit2: '',
            jzPost2: '',
            jzCardType2: '',
            resAddress2: '',
            jzIdCard2: '',
            jzNation2: '',
            isGuardian1: '是',
            isGuardian2: '是'
          },
          /*学费信息*/
          tuition_info: {
            openBank: '',
            bankAccount: ''
          },
          /*其他信息*/
          other_info: {
            disPerType: '',
            trafficMode: '',
            distance: '',
            workerNumber: '',
            healthStatus: '',
            mailAddress: '',
            bloodType: '',
            studentSource: '',
            email: '',
            businessPage: '',
            isSingleton: '0',
            isBehChild: '0',
            isOrphan: '0',
            isMartyrChild: '0',
            isApplyFund: '0',
            isPreschool: '0',
            isTraChild: '0',
            isTakeSchoolBus: '0',
            isSubsidy: '0',
            isGovBuyDegree: '0',
            auxNumber: ''
          }
        },
        messageFormRules: {
          student_info: {
            phone: [
              {validator: validatePhone, trigger: 'blur'}
            ]
          },
          parents_info: {
            jzPhone1: [
              {validator: validatePhone, trigger: 'blur'}
            ],
            jzPhone2: [
              {validator: validatePhone, trigger: 'blur'}
            ]
          },
          other_info: {
            email: [
              {validator: validateEmail, trigger: 'blur'}
            ]
          }
        }
      }
    },
    computed: {},
    created(){
      var self = this;
      self.sendLoadAjax();
      //得到科类
      req.ajaxSend('/school/user/userGl?type=getBranch', 'get', '', function (res) {
        self.branchList = res.data;
      });
    },
    methods: {
      operationData(type){
        if(!this.headerButtonData.studentBasicMsg.length){
          this.vmMsgWarning('暂无数据');
          return;
        }
        let sAy = [], hdData = {};
        this.headerButtonData.studentBasicTr.forEach(val=>{
          hdData[val.en]=val.zh;
        });
        sAy.push(hdData);
        for (let obj of this.headerButtonData.studentBasicMsg) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.InformationService', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.searchStudentAjax({
          page: this.currentPage,
          ListType: this.activeChoice,
          class: this.ajaxClasses,
          grade: this.ajaxGrade,
          pageSize: this.pageSize
        });
      },
      handleStudentTable(section){
        this.mutipleChoose = section;
      },
      sortChange(column){
        this.searchStudentAjax({
          ListType: this.activeChoice,
          sort: column.order||'',
          sortType: column.prop||'',
          class: this.ajaxClasses,
          grade: this.ajaxGrade,
          pageSize: this.pageSize
        });
      },
      buttonClick(event){
        const e = $(event.currentTarget), targetMsg = e.data('msg');
        if (targetMsg == 'delete') {
          this.deleteAjax();
        } else if (targetMsg == 'export') {
          this.exportAjax();
        }
      },
      changeBasicMsg(index){
        /*修改信息弹框*/
        var self = this, data = {
          branchid: ''
        };
        self.isDialog = true;
        self.dialogTitle = '修改信息';
        if (self.activeForm == 'student_info') {
          self.student_infoTime1 = self.headerButtonData.studentBasicMsg[index].leagueTime ? new Date(self.headerButtonData.studentBasicMsg[index].leagueTime) : self.headerButtonData.studentBasicMsg[index].leagueTime;
          self.student_infoTime2 = self.headerButtonData.studentBasicMsg[index].enrolTime ? new Date(self.headerButtonData.studentBasicMsg[index].enrolTime) : self.headerButtonData.studentBasicMsg[index].enrolTime;
          self.student_infoTime3 = self.headerButtonData.studentBasicMsg[index].birthday ? new Date(self.headerButtonData.studentBasicMsg[index].birthday) : self.headerButtonData.studentBasicMsg[index].birthday;
        }
        $.extend(self.updateData[self.activeForm], self.headerButtonData.studentBasicMsg[index]);
        if (self.activeForm == 'school_rollinfo') {
          for (let obj of self.branchList) {
            if (obj.branch == self.updateData[self.activeForm].stream) {
              data.branchid = obj.branchid;
            }
          }
          req.ajaxSend('/school/user/userGl?type=getMajor', 'get', data, function (res) {
            self.majorList = res.data;
          })
        }
        if(self.activeForm=='student_info'){
          studentMessageClassLoad({gradeid: self.updateData[self.activeForm].gradeId}).then(data => {
            if (data) {
              self.formClassList = data;
            } else {
              self.formClassList = [];
            }
          });
        }
      },
      setMajorState(state){
        this.majorState = state;
      },
      chooseMajor(){  //弹框选择专业
        var self = this, data = {
          branchid: ''
        };
        for (let obj of self.branchList) {
          if (obj.branch == self.updateData.school_rollinfo.stream) {
            data.branchid = obj.branchid;
          }
        }
        if (self.majorState) {
          self.updateData.school_rollinfo.major = '';
        }
        req.ajaxSend('/school/user/userGl?type=getMajor', 'get', data, function (res) {
          self.majorList = res.data;
        })
      },
      setClassState(state){
        this.classState = state;
      },
      chooseFormClass(){  //弹框选择班级
        if (this.classState) {
          this.updateData.student_info.classId = '';
          this.updateData.student_info.serialNumber = '';
        }
        studentMessageClassLoad({gradeid: this.updateData.student_info.gradeId}).then(data => {
          if (data) {
            this.formClassList = data;
          } else {
            this.formClassList = [];
          }
        });
      },
      setNumberState(state){
        this.serialNumberState = state;
      },
      changeNumber(){
        if (this.serialNumberState) {
          this.updateData.student_info.serialNumber = '';
        }
      },
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
        this.searchStudentAjax({
          ListType: this.activeChoice,
          class: this.ajaxClasses,
          grade: this.ajaxGrade,
          pageSize: this.pageSize,
          value: this.fuzzyInput
        });
      },
      handlerClose(done){
        done();
      },
      /*send ajax*/
      updateAjax(){
        /*userId:this.updateData.userId,*/
        if (this.activeForm == 'student_info') {
          this.updateData[this.activeForm].leagueTime = this.student_infoTime1 ? moment(this.student_infoTime1).format('YYYY-MM-DD') : this.student_infoTime1;
          this.updateData[this.activeForm].enrolTime = this.student_infoTime2 ? moment(this.student_infoTime2).format('YYYY-MM-DD') : this.student_infoTime2;
          this.updateData[this.activeForm].birthday = this.student_infoTime3 ? moment(this.student_infoTime3).format('YYYY-MM-DD') : this.student_infoTime3;
        }
        this.$refs[this.activeForm].validate((valid) => {
          if (valid) {
            studentMessageUpdate({
              ListType: this.activeForm,
              updataData: this.updateData[this.activeForm]
            }).then(data => {
              if (data.statu) {
                this.isDialog = false;
                this.searchClick('studentMessge', 1);
                this.vmMsgSuccess('修改成功!');
              } else {
                this.vmMsgError('修改失败!');
              }
            })
          } else {
            return false;
          }
        })
      },
      deleteAjax(){
        /*this.resetClick('studentMessge');*/
        if (this.mutipleChoose.length > 0) {
          this.$confirm('是否删除学生信息（删除后不能恢复）?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            const deleteArr = [];
            this.mutipleChoose.forEach((value) => {
              deleteArr.push(value.userId);
            });
            studentMessageDelete({userId: deleteArr}).then(data => {
              if (data.statu > 0) {
                this.vmMsgSuccess('删除成功!');
                /*刷新页面数据*/
                this.searchStudentAjax({
                  ListType: this.activeChoice,
                  class: this.ajaxClasses,
                  grade: this.ajaxGrade,
                  pageSize: this.pageSize
                });
              } else {
                this.vmMsgError('删除失败!');
              }
            });
          }).catch(() => {
          });
        } else {
          this.vmMsgWarning('请选择删除数据!');
        }
      },
      /*导出数据*/
      exportAjax(){
        this.handlerParams();
        this.$refs['studentMessge'].validate((valid) => {
          if (valid) {
            req.downloadFile('.g-container', '/school/user/exportExcel?type=' + this.activeChoice + '&class=' + this.ajaxClasses + '&grade=' + this.ajaxGrade, 'post');
          } else {
            this.vmMsgWarning('请填完整相关信息!');
          }
        });
      },
      sendLoadAjax(){
        studentMessageGradeLoad().then(data => {
          this.headerButtonData.gradeloadData = data;
        });
        studentMessageTypeLoad().then(data => {
          this.headerButtonData.msgTypeLoadData = data;
        });

      },
      gradeClick(){
        /*获取当前年级的班级*/
        this.dataHeader.classes='';
        this.headerButtonData.classesLoadData = [];
        studentMessageClassLoad({gradeid: this.dataHeader.grade}).then(data => {
          if (data) {
            this.headerButtonData.classesLoadData = data;
          } else {
            this.headerButtonData.classesLoadData = [];
          }
        });
      },
      /*页面数据请求*/
      searchClick(formName, type){
        if (type == 0) {
          this.fuzzyInput = '';
          this.activeChoice = this.dataHeader.messageType;
          switch (this.dataHeader.messageType) {
            case 'jbXi':
              this.activeForm = 'student_info';
              break;
            case 'xjXi':
              this.activeForm = 'school_rollinfo';
              break;
            case 'hjXi':
              this.activeForm = 'cen_reg_info';
              break;
            case 'jzXi':
              this.activeForm = 'parents_info';
              break;
            case 'xfXi':
              this.activeForm = 'tuition_info';
              break;
            case 'qtXi':
              this.activeForm = 'other_info';
              break;
          }
        }
        /*handler params*/
        this.handlerParams();
        /*send ajax*/
        this.searchStudentAjax({
          ListType: this.activeChoice,
          class: this.ajaxClasses,
          grade: this.ajaxGrade,
          pageSize: this.pageSize
        });
      },
      /*send student ajax*/
      searchStudentAjax(params){
        this.isLoading=true;
        this.$refs['studentMessge'].validate((valid) => {
          if (valid) {
            studentMessageSearchLoad(params).then(data => {
              this.headerButtonData.studentBasicMsg = data.data;
              this.headerButtonData.studentBasicTr = data.tr;
              this.pageALl = data.maxpage;
              this.currentPage = Number(data.page);
              this.headerButtonData.hasBasicData = true;
            });
          } else {
            this.vmMsgWarning('请填完整相关信息!');
          }
          this.isLoading=false;
        });
      },
      /*params处理，传grade的name和class的name*/
      handlerParams(){
        /*得到选择班级数组*/
        this.ajaxClasses = [];
        if (this.dataHeader.classes == 'all') {
          this.headerButtonData.classesLoadData.forEach((value) => {
            this.ajaxClasses.push(value.classid);
          });
          /*将this.dataHeader.classes改成包含所有classes的数组*/
        }
        else {
          this.headerButtonData.classesLoadData.forEach((value) => {
            if (value.classid == this.dataHeader.classes) {
              this.ajaxClasses.push(value.classid);
            }
          });
        }
        /*得到当前的年级的name值*/
        this.headerButtonData.gradeloadData.forEach((value) => {
          if (value.gradeid == this.dataHeader.grade) {
            this.ajaxGrade = value.gradeid;
          }
        });
      },
      /*弹框提示*/
      alertDialog(msg, type){
        this.$alert(msg, '提示', {
          confirmButtonText: '确定',
          type: type,
          callback: action => {
          }
        });
      },
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/userManager/student/studentMessage.less';
  @import '../../../../style/userManager/student/studentManager.css';
</style>


