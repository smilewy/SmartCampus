<template>
  <div class="scoreDetails">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <span class="breadcrumb"><router-link
        :to="{name:'aggregateScoreCount',params:{planId:selectParam.planId}}"
        tag="span">成绩汇总</router-link><span>|</span><span class="breadcrumb_active">成绩明细</span></span>
    </el-row>
    <el-row>
      <el-form :inline="true" :model="selectParam" class="searchConditions">
        <el-form-item label="科类：">
          <el-select v-model="activeBranch" placeholder="请选择" class="subject" @change="chooseMajor">
            <el-option :label="branch.name" :value="index" v-for="(branch,index) in branchList"
                       :key="branch.branchId"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="专业：">
          <el-select v-model="selectParam.wishId" placeholder="请选择" class="major">
            <el-option :label="major.name" :value="major.wishId" v-for="major in majorList"
                       :key="major.wishId"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" icon="el-icon-search" @click="onSubmit">查询</el-button>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button-group>
          <el-button class="filt" title="复制" @click="operationData('copy')">
            <img class="filt_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                 alt="">
            <img class="filt_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                 alt="">
          </el-button>
          <el-button class="delete" title="打印" @click="operationData('print')">
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
            placeholder="请输年级/班级/姓名"
            suffix-icon="el-icon-search"
            v-model="selectParam.key"
            @change="goSearch">
          </el-input>
        </div>
      </el-col>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        border
        @sort-change="sort"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          prop="preGrade"
          min-width="100"
          label="年级" sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="100"
          prop="preClass"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="name"
          min-width="100"
          label="姓名" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="score"
          min-width="100"
          label="总分" sortable="custom">
        </el-table-column>
        <el-table-column :label="headData.name" v-for="headData in tableHeadData" :key="headData.examId">
          <el-table-column
            :label="data.name" v-for="data in headData.subs" :key="data.subId">
            <template slot-scope="scope">
              <span v-if="scope.row[data.subId]">{{scope.row[data.subId].score}}</span>
            </template>
          </el-table-column>
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableData.length!=0">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="selectParam.page"
        :page-size="selectParam.count"
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
        branchList: [],
        majorList: [],
        tableData: [],
        tableHeadData: [],
        activeBranch: '',
        selectParam: {
          wishId: '',
          page: 1,
          count: 50,
          key: '',
          planId: '',
          sort: 2,
          order: '',
          according: ''
        },
        totalNum: 0,
        loading: false
      }
    },
    created: function () {
      var self = this, data;
      self.selectParam.planId = self.$route.params.planId;
      data = {
        func: 'getWish',
        param: {
          planId: self.selectParam.planId
        }
      };
      //查询科类和专业
      req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
        self.branchList = res.data;
      });
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      },
      chooseMajor(){
        this.selectParam.wishId = '';
        this.majorList = this.branchList[this.activeBranch].majors;
      },
      onSubmit() {
        this.selectParam.page = 1;
        this.selectParam.order = '';
        this.selectParam.according = '';
        this.selectParam.key = '';
        if (!this.selectParam.wishId) {
          this.vmMsgWarning('请选择专业！');
          return false;
        }
        this.loadData(this.selectParam);
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.order = '';
        this.selectParam.according = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.order = column.order || '';
        this.selectParam.according = column.prop || '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      operationData(type) {
        var tablestr = '<table class="table table=bordered" style="margin-bottom:0"><thead>';
        let tableThTr = '<tr><th rowspan="2">年级</th><th rowspan="2">班级</th><th rowspan="2">姓名</th><th rowspan="2">总分</th>',
          tableThTr1 = '<tr>', tableTd = '';
        for (let obj of this.tableHeadData) {
          tableThTr += '<th colspan="' + obj.subs.length + '">' + obj.name + '</th>';
          for (let mbj of obj.subs) {
            tableThTr1 += '<th>' + mbj.name + '</th>';
          }
        }
        for (let obj of this.tableData) {
          let tTr = '<tr>';
          tTr += '<td>' + obj.preGrade + '</td><td>' + obj.preClass + '</td><td>' + obj.name + '</td><td>' + obj.score + '</td>';
          for (let mbj of this.tableHeadData) {
            for (let nbj of mbj.subs) {
              tTr += '<td>' + (obj[nbj.subId] ? obj[nbj.subId].score : '') + '</td>';
            }
          }
          tTr += '</tr>';
          tableTd += tTr;
        }
        tableThTr += '</tr>';
        tableThTr1 += '</tr>';
        tablestr += tableThTr + tableThTr1 + '</thead><tbody>';
        tablestr += tableTd;
        tablestr += '</tbody></table>';
        if (type == 'copy') {
          if ($("#bstableCopy").length < 1) {
            var element = "<textarea id='bstableCopy' cols='20' rows='10' style='opacity: 0;position: fixed;'>复制内容</textarea>";
            $('.scoreDetails').append(element);
          }
          $("#bstableCopy").html(tablestr);
          $("#bstableCopy").select(); // 选择对象
          document.execCommand("Copy");
          alert('复制成功,请粘贴到Excel表格中！');
        } else {
          var printCssStr = ".con{text-align:center;color: #343434;}.rostrum{width: 150px;padding: 8px 0;border: 1px solid #343434;margin:20px 0}";
          var printStyle, printContent;
          printStyle = "<style>" + printcss() + printCssStr + "</style>";
          printContent = printStyle + "<body>" + tablestr + "</body>";

          var newWin = window.open("");//新打开一个空窗口
          newWin.document.write(printContent);//将表格添加进新的窗口
          newWin.document.close();//在IE浏览器中使用必须添加这一句
          newWin.focus();//在IE浏览器中使用必须添加这一句

          newWin.print();//打印
          newWin.close();//关闭窗口

          function printcss() {
            var css = ".table-bordered {border: 1px solid #000;}.table {width: 100%;max-width: 100%;margin-bottom: 40px;}.table {border-spacing: 0;border-collapse: collapse;}.table td{border: 1px solid #000;vertical-align: middle;text-align:center;font-size:15px;height:30px;}.table th{padding:6px; vertical-align: top; border: 1px solid #000;text-align:center;  font-weight:bold;  font-size:13px;}.table div{  text-align:center; } .table .w_35{width:35px; }.table .w_100{width:100px; }.editdiv{ width: 100%;  border: 0;  height: 30px;   font-size: 15px;  font-weight: normal;  text-align: center;  line-height:30px; }.ng-table-filters{display:none;};input{width: 100%;border: 0;height:30px;line-height:30px;font-size: 15px;font-weight: normal;text-align: center;}";
            return css;
          }
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/DivideBranch/scoreStatistics', 'post', data, function (res) {
          self.tableData = res.data.student;
          self.tableHeadData = res.data.exam;
          self.totalNum = res.total;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .scoreDetails .el-table--border td {
    border-right: 0;
  }

  .scoreDetails .el-select.subject {
    width: 8.25rem;
  }

  .scoreDetails .el-select.major {
    width: 10.75rem;
  }

  .scoreDetails .searchConditions .el-button {
    border-radius: 20px;
    padding: 10px 30px;
  }

  .scoreDetails .el-form--inline .el-form-item {
    margin-right: 2rem;
  }
</style>
