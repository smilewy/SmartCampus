<template>
  <div class="fillVolunteerSet">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-col :span="14">
        <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
          src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
          alt=""><span class="returnTxt">返回流程图</span></el-button>
        <h3>填报志愿设置</h3>
      </el-col>
      <el-col :span="10" class="operationBtns">
        <el-button type="primary" icon="plus" @click="addSubject('branch')">添加科类</el-button>
        <el-button type="primary" @click="addSubject('invokeBranch')">调用科类</el-button>
      </el-col>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column type="expand">
          <template slot-scope="props">
            <el-table
              :data="props.row.major"
              border
              style="width: 100%"
              class="childTable"
            >
              <el-table-column
                prop="major"
                label="专业名称">
              </el-table-column>
              <el-table-column
                label="操作">
                <template slot-scope="scope">
                  <span class="deleteMajor" @click="deleteData('major',props.$index,scope.$index)"><i
                    class="el-icon-close"></i></span>
                </template>
              </el-table-column>
            </el-table>
          </template>
        </el-table-column>
        <el-table-column
          prop="branch"
          label="名称">
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="invokeMajor" @click="addSubject('invokeMajor',scope.$index)">调用专业</span>
            <span class="device_line">|</span>
            <span class="edit_primary" @click="addSubject('major',scope.$index)">添加新专业</span>
            <span class="device_line">|</span>
            <span class="edit" @click="deleteData('branch',scope.$index)">删除</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="新增专业"
      :visible.sync="dialogVisible"
      :before-close="handleCloseDialog"
      :modal="false">
      <el-row class="formMsg">
        <el-form ref="form" :rules="formRules" :model="form" label-width="120px">
          <el-form-item label="专业名字：" prop="major">
            <el-input placeholder="请输入专业名称" v-model="form.major"></el-input>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg('major')">确定</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      title="添加新科类"
      :visible.sync="testDialogVisible"
      :before-close="handleCloseDialog"
      :modal="false">
      <el-row class="formMsg">
        <el-form ref="testForm" :rules="testFormRules" :model="testForm" label-width="120px">
          <el-form-item label="科类名称：" prop="branch">
            <el-input placeholder="请输入科类名称" v-model="testForm.branch"></el-input>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg('branch')">创建</el-button>
        <el-button @click="testDialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      title="调用科类"
      :visible.sync="branchDialogVisible"
      :before-close="handleCloseDialog"
      :modal="false" class="invokeMsg">
      <el-row>
        <el-row class="tips">请选择科类（可多选）</el-row>
        <el-row class="alertsList">
          <el-table
            :data="branchList"
            max-height="400"
            style="width: 100%"
            @selection-change="handleSelectionBranch"
            v-loading="loading1"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              type="selection"
              width="55">
            </el-table-column>
            <el-table-column
              prop="branch"
              label="科类名称">
            </el-table-column>
          </el-table>
        </el-row>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg('invokeBranch')">选择</el-button>
        <el-button @click="branchDialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      title="调用专业"
      :visible.sync="majorDialogVisible"
      :before-close="handleCloseDialog"
      :modal="false" class="invokeMsg">
      <el-row>
        <el-row class="tips">请选择专业（不可多选）</el-row>
        <el-row class="alertsList">
          <el-table
            :data="majorList"
            max-height="400"
            style="width: 100%"
            v-loading="loading2"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              prop="major"
              label="专业名称">
            </el-table-column>
            <el-table-column
              label="操作">
              <template slot-scope="scope">
                <span class="edit" @click="saveMsg('invokeMajor',scope.$index)">选择</span>
              </template>
            </el-table-column>
          </el-table>
        </el-row>
      </el-row>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        tableData: [],
        branchList: [],
        majorList: [],
        dialogVisible: false,
        testDialogVisible: false,
        branchDialogVisible: false,
        majorDialogVisible: false,
        form: {
          planId: '',
          type: 'addMajor',
          branch: '',
          major: '',
          majorId: '',
          branchId: ''
        },
        testForm: {
          planId: '',
          type: 'addBranch',
          branch: ''
        },
        multipleSelectionBranch: [],
        pId: '',
        majorIdx: '',
        testFormRules: {
          branch: [
            {required: true, message: '请输入科类名称', trigger: 'blur'},
            {min: 1, max: 10, message: '长度在 1 到 10 个字符', trigger: 'blur'}
          ]
        },
        formRules: {
          major: [
            {required: true, message: '请输入专业名称', trigger: 'blur'},
            {min: 1, max: 10, message: '长度在 1 到 10 个字符', trigger: 'blur'}
          ]
        },
        loading: false,
        loading1: false,
        loading2: false
      }
    },
    created: function () {
      this.pId = this.$route.params.planId;
      this.testForm.planId = this.pId;
      this.form.planId = this.pId;
      this.loadData();
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      },
      addSubject(type, idx){   //打开弹框
        var self = this, data;
        if (type == 'major') {  //添加专业
          self.form.type = 'addMajor';
          self.form.major = '';
          self.form.majorId = '';
          self.form.branch = self.tableData[idx].branch;
          self.form.branchId = self.tableData[idx].branchId;
          self.majorIdx = idx;
          self.dialogVisible = true;
        } else if (type == 'branch') {   //添加科类
          self.testForm.branch = '';
          self.testDialogVisible = true;
        } else if (type == 'invokeBranch') {  //调用科类
          data = {
            func: 'callBranch',
            param: {
              planId: self.pId
            }
          };
          self.branchDialogVisible = true;
          self.loading1 = true;
          req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
            self.branchList = res.data;
            self.loading1 = false;
          })
        } else {   //调用专业
          data = {
            func: 'callMajor',
            param: {
              branchId: self.tableData[idx].branchId,
              planId: self.pId
            }
          };
          self.majorIdx = idx;
          self.majorDialogVisible = true;
          self.loading2 = true;
          req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
            self.majorList = res.data;
            self.loading2 = false;
          })
        }
      },
      handleSelectionBranch(val) {  //调用科类选择
        this.multipleSelectionBranch = val;
      },
      handleCloseDialog(done){   //关闭弹框
        done();
      },
      deleteData(type, idx, ix){    //删除
        var self = this, data;
        if (type == 'major') {
          data = {
            planId: self.pId,
            type: 'delMajor',
            wishId: self.tableData[idx].major[ix].wishId
          };
          self.$confirm('确定删除专业?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/DivideBranch/wishSetting', 'post', data, function (res) {
              if (res.status == 1) {
                self.vmMsgSuccess('删除成功!');
                self.tableData[idx].major.splice(ix, 1);
              } else {
                self.vmMsgError(res.msg);
              }
            })
          }).catch(() => {
          });
        } else {
          data = {
            planId: self.pId,
            type: 'delBranch',
            branchId: self.tableData[idx].branchId
          };
          self.$confirm('确定删除科类?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/DivideBranch/wishSetting', 'post', data, function (res) {
              if (res.status == 1) {
                self.vmMsgSuccess('删除成功!');
                self.tableData.splice(idx, 1);
              } else {
                self.vmMsgError(res.msg);
              }
            })
          }).catch(() => {
          });
        }
      },
      saveMsg(type, idx){   //保存信息
        var self = this;
        if (type == 'major') {   //添加专业
          self.$refs['form'].validate((valid) => {
            if (valid) {
              req.ajaxSend('/school/DivideBranch/wishSetting', 'post', self.form, function (res) {
                if (res.status == 1) {
                  self.vmMsgSuccess('添加成功!');
                  self.dialogVisible = false;
                } else {
                  self.vmMsgError(res.msg);
                }
              })
            } else {
              return false;
            }
          });
        } else if (type == 'branch') {   //添加科类
          self.$refs['testForm'].validate((valid) => {
            if (valid) {
              req.ajaxSend('/school/DivideBranch/wishSetting', 'post', self.testForm, function (res) {
                if (res.status == 1) {
                  self.vmMsgSuccess('添加成功!');
                  self.testDialogVisible = false;
                } else {
                  self.vmMsgError(res.msg);
                }
              })
            } else {
              return false;
            }
          });
        } else if (type == 'invokeBranch') {    //调用科类
          var obj = {}, nAry = [], newArr = [];
          self.branchDialogVisible = false;
          nAry = self.tableData.concat(self.multipleSelectionBranch);
          for (let data of nAry) {
            let value = data.branch;
            if (!data.major) {
              data.major = [];
            }
            if (data.id) {
              data.branchId = data.id;
            }
            if (!obj[value]) {
              obj[value] = 1;
              newArr.push(data);
            }
          }
          self.tableData = newArr;
        } else {    //调用专业
          self.form.type = 'majorFromCall';
          self.form.major = self.majorList[idx].major;
          self.form.majorId = self.majorList[idx].majorId;
          self.form.branch = self.tableData[self.majorIdx].branch;
          self.form.branchId = self.tableData[self.majorIdx].branchId;
          req.ajaxSend('/school/DivideBranch/wishSetting', 'post', self.form, function (res) {
            if (res.status == 1) {
              let d = {
                major: res.name,
                majorId: res.majorId,
                wishId: res.wishId,
              };
              self.vmMsgSuccess('调用成功!');
              self.majorDialogVisible = false;
              self.tableData[self.majorIdx].major.push(d);
            } else {
              self.vmMsgError(res.msg);
            }
          })
        }
      },
      loadData(){
        var self = this, data = {
          planId: self.pId
        };
        self.loading = true;
        req.ajaxSend('/school/DivideBranch/wishSetting', 'post', data, function (res) {
          self.tableData = res.data || [];
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .fillVolunteerSet .invokeMajor {
    color: #09baa6;
    cursor: pointer;
  }

  .fillVolunteerSet .el-table__expanded-cell {
    padding: 0;
  }

  .fillVolunteerSet .el-table.childTable {

  }

  .fillVolunteerSet .alertsList .el-table.childTable th {
    background-color: #deeefe;
    height: 2.5rem;
  }

  .fillVolunteerSet .alertsList .el-table.childTable td {
    height: 2.5rem;
    font-size: .875rem;
  }

  .fillVolunteerSet .childTable .el-table__footer-wrapper thead div, .fillVolunteerSet .childTable .el-table__header-wrapper thead div {
    background-color: #deeefe;
    color: #666666;
  }

  .fillVolunteerSet .el-table.childTable td {
    background-color: #f0f0f0;
  }

  .fillVolunteerSet .invokeMsg .alertsList {
    min-height: 0;
  }

  .fillVolunteerSet .deleteMajor {
    display: inline-block;
    color: #d2d2d2;
    width: 1.5rem;
    height: 1.5rem;
    font-size: 12px;
    text-align: center;
    line-height: 1.5rem;
    border: 2px solid #d2d2d2;
    border-radius: 100%;
    cursor: pointer;
  }

  .fillVolunteerSet .invokeMsg .el-dialog__footer {
    -webkit-box-shadow: 0 -5px 20px -5px #d2d2d2;
    -moz-box-shadow: 0 -5px 20px -5px #d2d2d2;
    box-shadow: 0 -5px 20px -5px #d2d2d2;
  }

  .fillVolunteerSet .invokeMsg .tips {
    color: #999999;
    margin-bottom: 1rem;
  }
</style>
