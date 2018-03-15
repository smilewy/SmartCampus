<template>
  <div class="subScoreBasis">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-col :span="14">
        <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
          src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
          alt=""><span class="returnTxt">返回流程图</span></el-button>
        <h3>分班成绩依据</h3>
      </el-col>
      <el-col :span="10" class="operationBtns">
        <el-button type="primary" icon="plus" @click="addSubject('exam')">添加依据</el-button>
        <el-button type="primary" @click="addSubject('invoke')">调用考试</el-button>
      </el-col>
    </el-row>
    <el-row class="subClassDivision_row warmHint">
      温馨提示：学生分班成绩可以在'成绩管理'中的数据基础上进行修改，而不影响其他原来的成绩数据
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          type="index"
          width="100"
          label="序号">
        </el-table-column>
        <el-table-column
          prop="examination"
          label="考试名称">
        </el-table-column>
        <el-table-column
          label="依据科目">
          <template slot-scope="scope">
            <el-row class="l_name">
              <el-tag
                color="#f08bc5"
                v-for="(tag,ix) in scope.row.sub"
                :key="tag.subId"
                :closable="true" class="subjectName"
                @close="deleteSubject(ix,scope.$index)"
                close-transition
              >
                {{tag.subject}}
              </el-tag>
              <span class="addSubject" @click="addSubject('subjectName',scope.$index)"><i
                class="el-icon-plus"></i></span>
            </el-row>
          </template>
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="edit_primary" @click="toNext(scope.$index)">成绩管理</span>
            <span class="device_line">|</span>
            <span class="edit" @click="addSubject('edit',scope.$index)">编辑</span>
            <span class="device_line">|</span>
            <span class="edit" @click="deleteData(scope.$index)">删除</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="添加依据科目"
      :visible.sync="dialogVisible"
      :before-close="handleCloseDialog"
      :modal="false">
      <el-row class="formMsg">
        <el-form ref="form" :rules="formRules" :model="form" label-width="120px">
          <el-form-item label="科目名称：" prop="subject">
            <el-input v-model="form.subject"></el-input>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg('subjectName')">保存</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      :title="testTitle"
      :visible.sync="testDialogVisible"
      :before-close="handleCloseDialog"
      :modal="false">
      <el-row class="formMsg">
        <el-form ref="testForm" :rules="testFormRules" :model="testForm" label-width="120px">
          <el-form-item label="考试名称：" prop="exam">
            <el-input v-model="testForm.exam"></el-input>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg('exam')">保存</el-button>
        <el-button @click="testDialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      title="调用考试"
      :visible.sync="invokeDialogVisible"
      :before-close="handleCloseDialog"
      :modal="false" class="invokeMsg">
      <el-row class="alertsList">
        <el-table
          :data="testListDialog"
          max-height="400"
          style="width: 100%"
          @selection-change="handleSelectionChange"
          v-loading="loading1"
          element-loading-text="拼命加载中"
        >
          <el-table-column
            type="selection"
            width="55">
          </el-table-column>
          <el-table-column
            prop="grade"
            label="年级">
          </el-table-column>
          <el-table-column
            prop="name"
            label="考试名称">
          </el-table-column>
        </el-table>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg('invoke')">选择</el-button>
        <el-button @click="invokeDialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        tableData: [],
        testListDialog: [],
        dialogVisible: false,
        testDialogVisible: false,
        invokeDialogVisible: false,
        testTitle: '',
        form: {
          planId: '',
          type: 'addSubject',
          subject: '',
          examId: ''
        },
        testForm: {
          planId: '',
          type: '',
          exam: '',
          examId: ''
        },
        multipleSelection: [],
        pId: '',
        testFormRules: {
          exam: [
            {required: true, message: '请输入考试名称', trigger: 'blur'},
            {min: 1, max: 10, message: '长度在 1 到 10 个字符', trigger: 'blur'}
          ]
        },
        formRules: {
          subject: [
            {required: true, message: '请输入科目名称', trigger: 'blur'},
            {min: 1, max: 10, message: '长度在 1 到 10 个字符', trigger: 'blur'}
          ]
        },
        loading: false,
        loading1: false
      }
    },
    created: function () {
      this.pId = this.$route.params.planId;
      this.testForm.planId = this.pId;
      this.form.planId = this.pId;
      this.loadData();
    },
    methods: {
      returnFlowchart() {
        this.$router.push('/subClassDivisionHome');
      },
      addSubject(type, idx) {   //打开弹框
        var self = this, data;
        if (type == 'subjectName') {  //添加依据科目
          self.form.subject = '';
          self.form.examId = self.tableData[idx].examId;
          self.dialogVisible = true;
        } else if (type == 'exam') {
          self.testForm.type = 'addExam';
          self.testForm.exam = '';
          delete self.testForm.examId;
          self.testTitle = '添加依据考试';
          self.testDialogVisible = true;
        } else if (type == 'edit') {
          self.testTitle = '修改依据考试';
          self.testForm.type = 'editExam';
          self.testForm.exam = self.tableData[idx].examination;
          self.testForm.examId = self.tableData[idx].examId;
          self.testDialogVisible = true;
        } else {  //调用考试
          data = {
            func: 'callExam',
            param: {
              planId: self.pId
            }
          };
          self.invokeDialogVisible = true;
          self.loading1 = true;
          req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
            self.testListDialog = res.data;
            self.loading1 = false;
          })
        }
      },
      handleSelectionChange(val) {  //调用考试选择
        this.multipleSelection = val;
      },
      handleCloseDialog(done) {   //关闭弹框
        done();
      },
      deleteSubject(ix, idx) {  //删除依据科目
        var self = this, data = {
          planId: self.pId,
          type: 'delSubject',
          subId: self.tableData[idx].sub[ix].subId
        };
        self.$confirm('是否删除依据科目?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/DivideBranch/scoreBasis', 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('删除成功!');
              self.tableData[idx].sub.splice(ix, 1);
            } else {
              self.vmMsgError(res.msg);
            }
          });
        }).catch(() => {
        });
      },
      deleteData(idx) {    //删除考试
        var self = this, data = {
          planId: self.pId,
          type: 'delExam',
          examId: self.tableData[idx].examId
        };
        self.$confirm('确定删除记录?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/DivideBranch/scoreBasis', 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('删除成功!');
              self.tableData.splice(idx, 1);
            } else {
              self.vmMsgError(res.msg);
            }
          })
        }).catch(() => {
        });
      },
      toNext(idx) {  //进入成绩管理
        this.$router.push({name: 'scoresManagement', params: {planId: this.pId, examId: this.tableData[idx].examId}});
      },
      saveMsg(type) {   //保存信息
        var self = this, data;
        if (type == 'subjectName') {   //依据字段
          data = this.form;
          self.$refs['form'].validate((valid) => {
            if (valid) {
              req.ajaxSend('/school/DivideBranch/scoreBasis', 'post', data, function (res) {
                if (res.status == 1) {
                  self.dialogVisible = false;
                  self.testDialogVisible = false;
                  self.invokeDialogVisible = false;
                  self.vmMsgSuccess('添加成功！');
                  self.loadData();
                } else {
                  self.vmMsgError(res.msg);
                }
              })
            } else {
              return false;
            }
          });
        } else if (type == 'exam') {   //依据考试
          var msg = '修改成功！';
          data = self.testForm;
          if (self.testTitle == '添加依据考试') {
            msg = '添加成功！';
          }
          self.$refs['testForm'].validate((valid) => {
            if (valid) {
              req.ajaxSend('/school/DivideBranch/scoreBasis', 'post', data, function (res) {
                if (res.status == 1) {
                  self.testDialogVisible = false;
                  self.vmMsgSuccess(msg);
                  self.loadData();
                } else {
                  self.vmMsgError(res.msg);
                }
              })
            } else {
              return false;
            }
          });
        } else {    //调用考试
          if (self.multipleSelection.length == 0) {
            self.vmMsgWarning('请选择考试!');
            return false;
          }
          data = {
            func: 'callExam',
            param: {
              planId: self.pId,
              type: 'sure',
              exam: []
            }
          };
          for (let obj of self.multipleSelection) {
            let m = {
              id: obj.examId,
              name: obj.name
            };
            data.param.exam.push(m);
          }
          req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
            if (res.status == 1) {
              self.dialogVisible = false;
              self.testDialogVisible = false;
              self.invokeDialogVisible = false;
              self.vmMsgSuccess('调用成功！');
              self.loadData();
            } else {
              self.vmMsgError(res.msg);
            }
          })
        }
      },
      loadData() {
        var self = this, data = {
          planId: self.pId
        };
        self.loading = true;
        req.ajaxSend('/school/DivideBranch/scoreBasis', 'post', data, function (res) {
          self.tableData = res.data || [];
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .subScoreBasis .subjectName {
    margin: .2rem 1% .2rem 0;
    color: #fff;
  }

  .subScoreBasis .addSubject {
    width: 1.25rem;
    height: 1.25rem;
    border-radius: 5px;
    border: 1px solid #f08bc5;
    font-size: .75rem;
    color: #f08bc5;
    display: inline-block;
    line-height: 1.25rem;
    cursor: pointer;
    text-align: center;
  }

  .subScoreBasis .l_name {
    text-align: left;
  }

  .subScoreBasis .invokeMsg .el-dialog__footer {
    text-align: right;
  }

  .subScoreBasis .invokeMsg .alertsList {
    min-height: 0;
  }
</style>
