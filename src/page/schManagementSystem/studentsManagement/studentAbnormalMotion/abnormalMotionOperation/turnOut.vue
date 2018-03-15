<template>
  <div class="turnOut">
    <el-row class="turnOut_row">
      <el-form :inline="true" :model="selectParam" class="turnOutSelectForm">
        <el-form-item label="年级：">
          <el-select v-model="gradeId" placeholder="请选择年级" class="grade" @change="changeClass">
            <el-option :label="grade.name" :value="grade.gradeid" :key="grade.gradeid"
                       v-for="grade in gradeList"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="班级：">
          <el-select v-model="selectParam.classid" placeholder="请选择班级" class="sClass">
            <el-option :label="classData.classname" :value="classData.classid" :key="classData.classid"
                       v-for="classData in classList"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" icon="el-icon-search" @click="onSearch">查询</el-button>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="d_line abnormalMotionOperation_row"></el-row>
    <el-row type="flex" justify="end" class="alertsBtn">
      <div class="g-fuzzyInput">
        <el-input
          placeholder="请输入关键字"
          suffix-icon="el-icon-search"
          v-model="selectParam.find"
          @change="goSearch">
        </el-input>
      </div>
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
          prop="gradeName"
          min-width="100"
          label="年级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="className"
          min-width="100"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="name"
          min-width="100"
          label="姓名" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="sex"
          min-width="80"
          label="性别" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="studentCode"
          min-width="150"
          label="学籍号" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="certificate"
          min-width="150"
          label="身份证件类型" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="idCard"
          min-width="150"
          label="身份证号" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="hkAddress"
          min-width="150"
          label="户籍所在地" sortable="custom">
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="edit" @click="turnOut(scope.$index)">转出</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="转出"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row type="flex" justify="center">
        <el-col :span="18">
          <el-row type="flex" justify="center">
            <el-col :span="7" class="headMsg">姓名：{{curStudent.name}}</el-col>
            <el-col :span="7" class="headMsg">年级：{{curStudent.gradeName}}</el-col>
            <el-col :span="7" class="headMsg">班级：{{curStudent.className}}</el-col>
          </el-row>
          <el-row class="headRow">
            <el-form ref="form" :rules="formRules" :model="form" label-width="150px">
              <el-form-item label="转入学校名称：" prop="inschoolname">
                <el-input v-model="form.inschoolname" placeholder="请输入学校名称"></el-input>
              </el-form-item>
              <el-form-item label="转入学校标识编码：" prop="inschoolidentity">
                <el-input v-model="form.inschoolidentity" placeholder="请输入学校标识编码"></el-input>
              </el-form-item>
              <el-form-item label="拟读年级：" prop="togode">
                <el-input v-model="form.togode" placeholder="请输入拟读年级"></el-input>
              </el-form-item>
              <el-form-item label="转出日期：">
                <el-date-picker type="date" :editable="false" placeholder="选择日期" v-model="form.outdate"
                                style="width: 100%;"></el-date-picker>
              </el-form-item>
              <el-form-item label="申请理由：">
                <el-input resize="none" type="textarea" placeholder="请输入申请转出理由" v-model="form.reason"></el-input>
              </el-form-item>
            </el-form>
          </el-row>
        </el-col>
      </el-row>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="save">提交</el-button>
    <el-button @click="dialogVisible = false">取消</el-button>
  </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import moment from 'moment'

  export default {
    data() {
      return {
        gradeList: [],
        classList: [],
        dialogClassList: [],
        tableData: [],
        gradeId: '',
        selectParam: {
          typename: '转出',
          classid: '',
          find: '',
          field: '',
          order: ''
        },
        dialogVisible: false,
        curStudent: {},
        form: {
          userid: '',
          inschoolname: '',
          inschoolidentity: '',
          togode: '',
          outdate: '',
          reason: ''
        },
        toParam: {
          userid: '',
          inschoolname: '',
          inschoolidentity: '',
          togode: '',
          outdate: '',
          reason: ''
        },
        formRules: {
          inschoolname: [
            {required: true, message: '请输入转入学校名称', trigger: 'blur'}
          ],
          inschoolidentity: [
            {required: true, message: '请输入转入学校标识码', trigger: 'blur'}
          ],
          togode: [
            {required: true, message: '请输入拟读年级', trigger: 'blur'}
          ],
          outdate: [
            {required: true, type: 'date', message: '请选择转出日期', trigger: 'change'}
          ],
          reason: [
            {required: true, message: '请输入申请理由', trigger: 'blur'}
          ]
        },
        loading: false
      }
    },
    created: function () {
      var self = this;
      req.ajaxSend('/school/Transaction/operation/type/getGrade', 'post', '', function (res) {
        self.gradeList = res;
      })
    },
    methods: {
      changeClass() {
        var self = this, data = {
          gradeid: self.gradeId
        };
        self.selectParam.classid = '';
        req.ajaxSend('/school/Transaction/operation/type/getClass', 'post', data, function (res) {
          self.classList = res;
        })
      },
      onSearch() {
        if (!this.selectParam.classid) {
          this.vmMsgWarning('请选择班级！');
          return false;
        }
        this.selectParam.find = '';
        this.selectParam.field = '';
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      goSearch() {  //查询
        if (!this.selectParam.classid) {
          this.vmMsgWarning('请选择班级！');
          return false;
        }
        this.selectParam.field = '';
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      sort(column) {
        this.selectParam.field = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      turnOut(idx) {
        this.dialogVisible = true;
        this.form.userid = this.tableData[idx].userid;
        $.extend(this.curStudent, this.tableData[idx]);
      },
      handleClose(done) {
        done();
      },
      save() {
        var self = this;
        self.$refs['form'].validate((valid) => {
          if (valid) {
            for (let name in self.form) {
              if (name == 'outdate') {
                self.toParam[name] = self.form[name] ? moment(self.form[name]).format('YYYY-MM-DD') : '';
              } else {
                self.toParam[name] = self.form[name];
              }
            }
            req.ajaxSend('/school/Transaction/operation/type/zhuanchu', 'post', self.toParam, function (res) {
              if (res.return) {
                self.vmMsgSuccess('转出成功！');
                self.dialogVisible = false;
                self.loadData(self.selectParam);
              } else {
                self.vmMsgError('转出失败！');
              }
            })
          } else {
            return false;
          }
        });
      },
      loadData(data) {
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Transaction/operation/type/getStudents', 'post', data, function (res) {
          self.tableData = res;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .turnOut .turnOut_row {
    margin-top: 2rem;
  }

  .turnOut .turnOutSelectForm .el-form-item {
    margin-bottom: 0;
    margin-right: 2.5rem;
  }

  .turnOut .turnOutSelectForm .el-button {
    border-radius: 20px;
    padding: 8px 25px;
  }

  .turnOut .turnOutSelectForm .grade {
    width: 8.75rem;
  }

  .turnOut .turnOutSelectForm .sClass {
    width: 9.375rem;
  }

  .turnOut .headMsg {
    text-align: center;
  }

  .turnOut .headRow {
    margin-top: 32px;
  }
</style>
