<template>
  <div class="testNumberSetting">
    <el-row>
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <span class="breadcrumb"><span class="breadcrumb_active">考号调用</span><router-link
        :to="{name:'testNumberManagement',params:{gradeid:selectParam.gradeid,examinationid:selectParam.examinationid}}"
        tag="span">学生考号管理</router-link></span>
    </el-row>
    <el-row class="examManager_row">
      <el-form :inline="true" class="formInline">
        <el-form-item label="考号：">
          <el-select v-model="selectParam.program" placeholder="请选择" class="testNumber"
                     @change="changeData">
            <el-option
              v-for="item in testNumberTypes"
              :key="item.value"
              :label="item.label"
              :value="item.label">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="参考学生：">
          <span>{{joinTests}}人</span>
        </el-form-item>
        <el-form-item label="有效考号：">
          <span>{{testNumbers}}个</span>
        </el-form-item>
        <el-form-item v-show="selectParam.program!='本次考试'">
          <el-button type="primary" class="saveTestNumber" @click="saveTestNumber">保存为本次考试号</el-button>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button class="delete" title="导出" @click="operationTable('out')">
          <img class="delete_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
               alt="">
          <img class="delete_active"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
               alt="">
        </el-button>
        <el-button-group class="secBtn-group">
          <el-button class="filt" title="复制" @click="operationTable('copy')">
            <img class="filt_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                 alt="">
            <img class="filt_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                 alt="">
          </el-button>
          <el-button class="delete" title="打印" @click="operationTable('print')">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                 alt="">
          </el-button>
        </el-button-group>
      </el-col>
      <el-col :span="6">
        <div class="g-fuzzyInput">
          <el-input
            placeholder="请输入姓名"
            suffix-icon="el-icon-search"
            v-model="selectParam.screen"
            @change="goSearch">
          </el-input>
        </div>
      </el-col>
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
          type="index"
          width="100"
          label="序号">
        </el-table-column>
        <el-table-column
          prop="className"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="serialNumber"
          label="班级序号" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="name"
          label="姓名" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="number"
          :label="selectParam.program">
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableData.length!=0">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="selectParam.page"
        :page-size="selectParam.limit"
        layout="prev, pager, next, jumper"
        :total="totalNum">
      </el-pagination>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        testNumberTypes: [{
          value: '0',
          label: '本次考试'
        }, {
          value: '1',
          label: '省考号'
        }, {
          value: '2',
          label: '市考号'
        }, {
          value: '3',
          label: '校考号'
        }],
        tableData: [],
        selectParam: {
          page: 1,
          limit: 50,
          program: '本次考试',   //考试类型
          gradeid: '',
          field: '',   //排序字段
          find: '考号调用',
          examinationid: '',
          screen: '',   //搜索框的值
          order: ''   //正序还是倒叙
        },
        totalNum: 0,
        joinTests: 0,
        testNumbers: 0,
        loading: false
      }
    },
    created: function () {
      var routeParam = this.$route.params;
      this.selectParam.gradeid = routeParam.gradeid;
      this.selectParam.examinationid = routeParam.examinationid;
      this.loadData(this.selectParam);
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/examManagerHome');
      },
      goSearch() {  //输入框查询
        this.selectParam.page = 1;
        this.selectParam.order = '';
        this.selectParam.field = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.order = column.order || '';
        this.selectParam.field = column.prop || '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      changeData(){
        this.selectParam.order = '';
        this.selectParam.field = '';
        this.selectParam.screen = '';
        this.selectParam.page = 1;
        this.loadData(this.selectParam);
      },
      operationTable(type){
        let sAy = [], hdData = {
          className: '班级',
          serialNumber: '班级座号',
          name: '姓名',
          number: this.selectParam.program
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'out') {
          req.downloadFile('.testNumberSetting', '/school/Examination/exmanagement/type/exnumber/typename/export?program=' + this.selectParam.program + '&gradeid=' + this.selectParam.gradeid + '&examinationid=' + this.selectParam.examinationid, 'post')
        } else if (type == 'copy') {
          req.copyTableData('.testNumberSetting', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      saveTestNumber(){
        var self = this;
        var data = {
          program: self.selectParam.program,
          gradeid: self.selectParam.gradeid,
          examinationid: self.selectParam.examinationid
        };
        req.ajaxSend('/school/Examination/exmanagement/type/exnumber/typename/examnumberin', 'post', self.selectParam, function (res) {
          if (res.return) {
            self.vmMsgSuccess('保存成功！');
          } else {
            self.vmMsgError('保存失败！');
          }
        })
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Examination/exmanagement/type/exnumber/typename/exnumberfind', 'post', data, function (res) {
          self.loading = false;
          self.tableData = res.data;
          self.totalNum = Number.parseInt(res.page.count);
          self.joinTests = res.num.student;
          self.testNumbers = res.num.number;
        })
      }
    }
  }
</script>
<style>
  .testNumberSetting .saveTestNumber {
    border-radius: 20px;
  }
</style>
