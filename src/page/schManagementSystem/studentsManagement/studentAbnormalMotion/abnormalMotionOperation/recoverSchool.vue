<template>
  <div class="recoverSchool">
    <el-row class="recoverSchool_row">
      <el-form :inline="true" :model="selectParam" class="recoverSchoolSelectForm">
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
          min-width="120"
          label="年级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="className"
          min-width="120"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="name"
          min-width="120"
          label="姓名" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="sex"
          min-width="120"
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
            <span class="edit" @click="recoverSchool(scope.$index)">复学</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="复学"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row type="flex" justify="center">
        <el-col :span="18">
          <el-row type="flex" justify="center">
            <el-col :span="8" class="headMsg">姓名：{{curStudent.name}}</el-col>
            <el-col :span="8" class="headMsg">年级：{{curStudent.gradeName}}</el-col>
            <el-col :span="8" class="headMsg">班级：{{curStudent.className}}</el-col>
          </el-row>
          <el-row class="headRow">
            <el-form ref="formData" :rules="formRules" :model="formData" label-width="100px">
              <el-form-item label="拟读年级：" prop="gradeid">
                <el-select v-model="formData.gradeid" placeholder="请选择拟读年级" style="width: 100%"
                           @change="changeClassDialog">
                  <el-option :label="grade.name" :value="grade.gradeid" :key="grade.gradeid"
                             v-for="grade in gradeList"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="拟读班级：" prop="classid">
                <el-select v-model="formData.classid" placeholder="请选择拟读班级" style="width: 100%">
                  <el-option :label="classData.classname" :value="classData.classid" :key="classData.classid"
                             v-for="classData in dialogClassList"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="报道日期：">
                <el-date-picker type="date" :editable="false" placeholder="选择日期" v-model="formData.reportdate"
                                style="width: 100%;"></el-date-picker>
              </el-form-item>
              <el-form-item label="申请理由：">
                <el-input resize="none" type="textarea" placeholder="请输入申请复学理由" v-model="formData.reason"></el-input>
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
          typename: '复学',
          classid: '',
          find: '',
          field: '',
          order: ''
        },
        dialogVisible: false,
        curStudent: {},
        form: {
          class: {
            classid: '',
            classname: ''
          },
          grade: {
            gradeid: '',
            name: ''
          },
          reportdate: '',
          reason: '',
          userid: ''
        },
        formData: {
          gradeid: '',
          classid: '',
          reportdate: '',
          reason: '',
          userid: ''
        },
        formRules: {
          gradeid: [
            {required: true, message: '请选择转入年级', trigger: 'change'}
          ],
          classid: [
            {required: true, message: '请选择转入班级', trigger: 'change'}
          ],
          reportdate: [
            {required: true, type: 'date', message: '请选择报道日期', trigger: 'change'}
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
      changeClassDialog() {
        var self = this, data = {
          gradeid: self.formData.gradeid
        };
        self.formData.classid = '';
        req.ajaxSend('/school/Transaction/operation/type/getClass', 'post', data, function (res) {
          self.dialogClassList = res;
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
      recoverSchool(idx) {
        this.dialogVisible = true;
        this.form.userid = this.tableData[idx].userid;
        $.extend(this.curStudent, this.tableData[idx]);
      },
      handleClose(done) {
        done();
      },
      save() {
        var self = this;
        self.$refs['formData'].validate((valid) => {
          if (valid) {
            for (let obj of self.gradeList) {
              if (obj.gradeid == self.formData.gradeid) {
                self.form.grade.gradeid = obj.gradeid;
                self.form.grade.name = obj.name;
              }
            }
            for (let obj of self.dialogClassList) {
              if (obj.classid == self.formData.classid) {
                self.form.class.classid = obj.classid;
                self.form.class.classname = obj.classname;
              }
            }
            self.form.reportdate = self.formData.reportdate ? moment(self.formData.reportdate).format('YYYY-MM-DD') : '';
            self.form.reason = self.formData.reason;
            req.ajaxSend('/school/Transaction/operation/type/fuxue', 'post', self.form, function (res) {
              if (res.return) {
                self.vmMsgSuccess('复学成功！');
                self.dialogVisible = false;
                self.loadData(self.selectParam);
              } else {
                self.vmMsgError('复学失败！');
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
  .recoverSchool .recoverSchool_row {
    margin-top: 2rem;
  }

  .recoverSchool .recoverSchoolSelectForm .el-form-item {
    margin-bottom: 0;
    margin-right: 2.5rem;
  }

  .recoverSchool .recoverSchoolSelectForm .el-button {
    border-radius: 20px;
    padding: 8px 25px;
  }

  .recoverSchool .recoverSchoolSelectForm .grade {
    width: 8.75rem;
  }

  .recoverSchool .recoverSchoolSelectForm .sClass {
    width: 9.375rem;
  }

  .recoverSchool .headMsg {
    text-align: center;
  }

  .recoverSchool .headRow {
    margin-top: 32px;
  }
</style>
