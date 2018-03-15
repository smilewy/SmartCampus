<template>
  <div class="classGradeManagement">
    <h3>班/年级管理</h3>
    <el-row type="flex" align="middle" class="classGradeManagement_row">
      <el-col :span="14">
        <el-form :inline="true">
          <el-form-item label="年级：">
            <el-select v-model="selectParam.gradeid" placeholder="请选择" class="grade">
              <el-option
                v-for="item in gradeList"
                :key="item.gradeid"
                :label="item.znName"
                :value="item.gradeid">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" icon="el-icon-search" class="search" @click="search">查询</el-button>
          </el-form-item>
        </el-form>
      </el-col>
      <el-col :span="10">
        <el-row class="createBtn">
          <el-button type="primary" icon="plus" @click="operationClass('createGrade')">创建年级</el-button>
        </el-row>
      </el-col>
    </el-row>
    <el-row class="d_line classGradeManagement_row"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-button-group>
        <el-button class="filt" title="添加" @click="operationClass('add')">
          <img class="filt_unactive"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add.png"
               alt="">
          <img class="filt_active"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png"
               alt="">
        </el-button>
        <el-button class="delete" title="删除" @click="deleteData">
          <img class="delete_unactive"
               src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete.png"
               alt="">
          <img class="delete_active"
               src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete_highlight.png"
               alt="">
        </el-button>
        <el-button class="delete" title="导出" @click="operationData('out')">
          <img class="delete_unactive"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
               alt="">
          <img class="delete_active"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
               alt="">
        </el-button>
      </el-button-group>
      <el-button-group class="secBtn-group">
        <el-button class="filt" title="复制" @click="operationData('copy')">
          <img class="filt_unactive"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
               alt="">
          <img class="filt_active"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
               alt="">
        </el-button>
        <el-button class="delete" title="打印" @click="operationData('print')">
          <img class="delete_unactive"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
               alt="">
          <img class="delete_active"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
               alt="">
        </el-button>
      </el-button-group>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        @selection-change="handleSelectionChange"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          type="selection"
          width="55">
        </el-table-column>
        <el-table-column
          prop="classname"
          label="班级名称">
        </el-table-column>
        <el-table-column
          prop="number"
          label="班级人数">
        </el-table-column>
        <el-table-column
          prop="branchname"
          label="科类">
        </el-table-column>
        <el-table-column
          prop="majorname"
          label="班级专业">
        </el-table-column>
        <el-table-column
          prop="levelname"
          label="班级级别">
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="edit" @click="operationClass('edit',scope.$index)">编辑</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="新增年级"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row type="flex" justify="center">
        <el-col :span="20" class="gradeForm">
          <el-form ref="gradeForm" :model="gradeForm" :rules="gradeFormRules" label-width="150px">
            <el-form-item label="年级代码：" prop="code">
              <el-input placeholder="请输入班级代码，如：C2016" v-model="gradeForm.code"></el-input>
            </el-form-item>
            <el-form-item label="所有年级自动升级：">
              <el-switch active-text="是" inactive-text="否" active-color="#09baa7" inactive-color="#ff4949"
                         v-model="autoUpgrade"></el-switch>
            </el-form-item>
            <el-form-item label="毕业最高年级：">
              <el-switch active-text="是" inactive-text="否" active-color="#09baa7" inactive-color="#ff4949"
                         v-model="maxGrade"></el-switch>
            </el-form-item>
          </el-form>
          <el-row class="tip">
            提示：年级代码高中以 <span class="spec">G</span> 开头，初中以 <span class="spec">C</span> 开头，小学以 <span class="spec">X</span>
            开头。
          </el-row>
        </el-col>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="save('grade')">保存</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      :title="classFormTitle"
      :visible.sync="classDialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row type="flex" justify="center">
        <el-col :span="18" class="classForm">
          <el-form ref="classForm" :rules="classFormRules" :model="classForm" label-width="100px">
            <el-form-item label="班级名称：" prop="classname">
              <el-input placeholder="请输入班级名称，如：1班" v-model="classForm.classname"></el-input>
            </el-form-item>
            <el-form-item label="班级人数：" prop="number">
              <el-input placeholder="请输入班级人数" v-model="classForm.number"></el-input>
            </el-form-item>
            <el-form-item label="班级科类：" prop="branch">
              <el-select v-model="classForm.branch" placeholder="请选择" @change="selectMajor">
                <el-option
                  v-for="item in branchList"
                  :key="item.branchid"
                  :label="item.branch"
                  :value="item.branchid">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="班级专业：" prop="major">
              <el-select v-model="classForm.major" placeholder="请选择">
                <el-option
                  v-for="item in majorList"
                  :key="item.majorid"
                  :label="item.majorname"
                  :value="item.majorid">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="班级级别：" prop="levelid">
              <el-select v-model="classForm.levelid" placeholder="请选择">
                <el-option
                  v-for="item in levelList"
                  :key="item.levelid"
                  :label="item.levelname"
                  :value="item.levelid">
                </el-option>
              </el-select>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="save('class')">保存</el-button>
        <el-button @click="classDialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      var checkNumber = (rule, value, callback) => {
        let reg = /^([+-]?)\d*\.?\d+$/;
        if (!reg.test(value)) {
          callback(new Error('请输入班级人数，且为数字'));
        } else {
          callback();
        }
      };
      var checkClassName = (rule, value, callback) => {
        let reg = /^([+-]?)\d*\.?\d+$/;
        if (!reg.test(value)) {
          callback(new Error('请输入班级名称，且为数字'));
        } else {
          callback();
        }
      };
      return {
        gradeList: [],
        selectParam: {
          gradeid: ''
        },
        tableData: [],
        multipleSelection: [],
        dialogVisible: false,
        autoUpgrade: true,
        maxGrade: true,
        gradeForm: {
          code: '',
          autoupdate: '',
          highestgrade: ''
        },
        branchList: [],
        majorList: [],
        levelList: [],
        classFormTitle: '',
        classDialogVisible: false,
        classForm: {
          classname: '',
          number: '',
          branch: '',
          levelid: '',
          major: '',
          classid: '',
          grade: ''
        },
        gradeFormRules: {
          code: [
            {required: true, message: '请输入年级代码', trigger: 'blur'},
            {min: 1, max: 6, message: '长度在 1 到 6 个字符', trigger: 'blur'}
          ]
        },
        classFormRules: {
          classname: [
            {required: true, validator: checkClassName, trigger: 'blur'}
          ],
          number: [
            {required: true, validator: checkNumber, trigger: 'blur'}
          ],
          levelid: [
            {required: true, message: '请选择班级级别', trigger: 'change'}
          ],
          branch: [
            {required: true, message: '请选择科类', trigger: 'change'}
          ],
          major: [
            {required: true, message: '请选择专业', trigger: 'change'}
          ]
        },
        loading: false
      }
    },
    created: function () {
      var self = this;
      req.ajaxSend('/school/Educational/getSubjectList?type=getGradeList', 'get', '', function (res) {
        self.gradeList = res.data;
        self.selectParam.gradeid = res.data[0].gradeid;
        self.loadTableData(self.selectParam);
      })
    },
    methods: {
      search() {
        this.loadTableData(this.selectParam);
      },
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      operationClass(type, idx) {
        var self = this, data;
        if (type == 'createGrade') {  //创建年级弹框
          self.dialogVisible = true;
        } else if (type == 'edit') {  //编辑弹框
          self.classDialogVisible = true;
          self.classFormTitle = '编辑信息';
          self.classForm.classname = self.tableData[idx].classname;
          self.classForm.number = self.tableData[idx].number;
          self.classForm.branch = self.tableData[idx].branch;
          self.classForm.levelid = self.tableData[idx].levelid;
          self.classForm.major = self.tableData[idx].major;
          self.classForm.classid = self.tableData[idx].classid;
          self.majorList = [];
          data = {
            branchid: self.classForm.branch
          };
          req.ajaxSend('/school/Educational/classAndgradeGl?type=getClassList&type11=getMajor', 'get', data, function (res) {
            self.majorList = res.data;
          })
        } else {   //添加弹框
          self.classDialogVisible = true;
          self.classFormTitle = '添加班级';
          for (let obj in self.classForm) {
            self.classForm[obj] = '';
          }
          this.$refs['classForm'].resetFields();
        }
      },
      selectMajor(){
        var self = this, data = {
          branchid: self.classForm.branch
        };
        self.classForm.major = '';
        self.majorList = [];
        req.ajaxSend('/school/Educational/classAndgradeGl?type=getClassList&type11=getMajor', 'get', data, function (res) {
          self.majorList = res.data;
        })
      },
      handleClose(done) {
        done();
      },
      operationData(type) {
        let sAy = [], hdData;
        hdData = {
          classname: '班级名称',
          number: '班级人数',
          branchname: '科类',
          majorname: '班级专业',
          levelname: '班级级别'
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
          req.downloadFile('.classGradeManagement', '/school/Educational/classAndgradeGl?type=export&gradeid=' + this.selectParam.gradeid, 'post');
        } else if (type == 'copy') {
          req.copyTableData('.classGradeManagement', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      deleteData() {
        var self = this;
        if (self.multipleSelection.length == 0) {
          self.vmMsgSuccess('请选择记录！');
          return false;
        }
        self.$confirm('确定删除?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          var data = {
            classid: []
          };
          for (let obj of self.multipleSelection) {
            data.classid.push(obj.classid);
          }
          req.ajaxSend('/school/Educational/classAndgradeGl?type=deleteClass', 'post', data, function (res) {
            if (res.statu == 1) {
              self.vmMsgSuccess('删除成功！');
              self.loadTableData(self.selectParam);
            } else {
              self.vmMsgError(res.message);
            }
          });
        }).catch(() => {
        });
      },
      save(type) {
        var self = this;
        if (type == 'grade') {  //新建年级保存
          self.gradeForm.autoupdate = self.autoUpgrade ? 1 : 0;
          self.gradeForm.highestgrade = self.maxGrade ? 1 : 0;
          self.$refs['gradeForm'].validate((valid) => {
            if (valid) {
              req.ajaxSend('/school/Educational/classAndgradeGl?type=createGrade', 'post', self.gradeForm, function (res) {
                if (res.statu == 1) {
                  self.vmMsgSuccess('新建成功！');
                  self.dialogVisible = false;
                  self.loadGradeData();
                } else {
                  self.vmMsgError(res.message);
                }
              })
            } else {
              return false;
            }
          });
        } else {   //修改或新建班级保存
          var msg = '修改成功！';
          if (self.classFormTitle == '添加班级') {
            self.classForm.classid = '';
            msg = '添加成功！';
          }
          self.classForm.grade = self.selectParam.gradeid;
          self.$refs['classForm'].validate((valid) => {
            if (valid) {
              req.ajaxSend('/school/Educational/classAndgradeGl?type=createOrUpdataClass', 'post', self.classForm, function (res) {
                if (res.statu == 1) {
                  self.vmMsgSuccess(msg);
                  self.classDialogVisible = false;
                  self.loadTableData(self.selectParam);
                } else {
                  self.vmMsgError(res.message);
                }
              })
            } else {
              return false;
            }
          });
        }
      },
      loadGradeData() {
        var self = this;
        req.ajaxSend('/school/Educational/getSubjectList?type=getGradeList', 'get', '', function (res) {
          self.gradeList = res.data;
        })
      },
      loadTableData(data) {
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Educational/classAndgradeGl?type=getClassList', 'get', data, function (res) {
          self.tableData = res.data;
          self.branchList = res.branch;
          self.levelList = res.classLeavl;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .classGradeManagement {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .classGradeManagement h3 {
    font-size: 1.25rem;
  }

  .classGradeManagement .classGradeManagement_row {
    margin: 1.25rem 0;
  }

  .classGradeManagement .el-form--inline .el-form-item {
    margin-bottom: 0;
  }

  .classGradeManagement .alertsBtn {
    margin: 1.25rem 0;
  }

  .classGradeManagement .edit {
    color: #4da1ff;
    cursor: pointer;
  }

  .classGradeManagement .el-table td, .classGradeManagement .el-table th {
    text-align: center;
  }

  .classGradeManagement .grade {
    width: 8.75rem;
  }

  .classGradeManagement .createBtn {
    text-align: right;
  }

  .classGradeManagement .search, .classGradeManagement .createBtn .el-button {
    padding: 10px 25px;
    border-radius: 20px;
  }

  .classGradeManagement .el-form--inline .el-form-item {
    margin-right: 2rem;
  }

  .classGradeManagement .el-dialog--small {
    width: 600px;
  }

  .classGradeManagement .gradeForm .tip {
    text-align: center;
    color: #888888;
  }

  .classGradeManagement .gradeForm .spec {
    color: #4da1ff;
  }

  .classGradeManagement .classForm .el-select {
    width: 100%;
  }
</style>
