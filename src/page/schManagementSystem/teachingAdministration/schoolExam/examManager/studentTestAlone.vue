<template>
  <div class="studentTestAlone">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>学生单独参考</h3>
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
            placeholder="请输入查询信息"
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
          width="80"
          label="序号">
        </el-table-column>
        <el-table-column
          prop="grade"
          label="年级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="className"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="serialNumber"
          label="座号" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="name"
          label="姓名" sortable="custom">
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="joinTest" @click="addMsg(scope.$index)">
              <i class="el-icon-plus"></i><span>参加考试</span>
            </span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="学生单独参加考试"
      :visible.sync="dialogVisible"
      :before-close="handleClose"
      :modal="false"
      class="seatLayout">
      <el-form ref="form" :model="form" label-width="90px" class="testNumber_form">
        <el-form-item label="学生姓名：">
          <span>{{showMsg.name}}</span>
        </el-form-item>
        <el-form-item label="考号类型：">
          <el-select v-model="form.program" placeholder="请选择">
            <el-option label="省考号" value="省考号"></el-option>
            <el-option label="市考号" value="市考号"></el-option>
            <el-option label="校考号" value="校考号"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="学生座号：">
          <el-input readonly v-model="showMsg.number"></el-input>
        </el-form-item>
      </el-form>
      <el-row class="testNumber_tips">
        提示：单独参加考试的学生仅参与成绩录入，系统将不为该学生安排考场
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg">保存</el-button>
        <el-button @click="dialogVisible = false">关闭</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        tableData: [],
        selectParam: {
          examinationid: '',
          field: '',
          order: '',
          screen: '',
          gradeid: ''
        },
        dialogVisible: false,
        form: {
          examinationid: '',
          gradeid: '',
          program: '省考号',
          id: ''
        },
        showMsg: {
          name: '',
          number: ''
        },
        loading: false
      }
    },
    created: function () {
      var routeParam = this.$route.params;
      this.selectParam.examinationid = routeParam.examinationid;
      this.selectParam.gradeid = routeParam.gradeid;
      this.form.examinationid = routeParam.examinationid;
      this.form.gradeid = routeParam.gradeid;
      this.loadData(this.selectParam);
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/examManagerHome');
      },
      goSearch() {  //查询
        this.selectParam.field = '';
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.field = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      handleClose(done) {   //关闭弹框
        done();
      },
      addMsg(idx){
        this.dialogVisible = true;
        this.showMsg.name = this.tableData[idx].name;
        this.showMsg.number = this.tableData[idx].serialNumber;
        this.form.id = this.tableData[idx].id;
      },
      operationData(type){
        let sAy = [], hdData = {
          grade: '年级',
          className: '班级',
          serialNumber: '座号',
          name: '姓名'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.studentTestAlone', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      saveMsg(){
        var self = this;
        req.ajaxSend('/school/Examination/exmanagement/type/joinalone/typename/insert', 'post', self.form, function (res) {
          if (res.return) {
            self.vmMsgSuccess('保存成功！');
            self.dialogVisible = false;
            self.loadData(self.selectParam);
          } else {
            self.vmMsgError('保存失败！');
          }
        })
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Examination/exmanagement/type/joinalone/typename/select', 'post', data, function (res) {
          self.tableData = res;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .studentTestAlone .joinTest {
    color: #20a0ff;
    cursor: pointer;
  }

  .studentTestAlone .joinTest i {
    vertical-align: middle;
    margin-right: .6rem;
  }

  .studentTestAlone .testNumber_tips {
    text-align: center;
  }

  .studentTestAlone .el-select {
    width: 100%;
  }
</style>
