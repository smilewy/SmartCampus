<template>
  <div class="subClassVolunteerHeadteacher">
    <el-row type="flex" align="middle">
      <h3>调整学生志愿</h3>
    </el-row>
    <el-row class="subClassDivision_title">
      <el-form ref="selectForm" :inline="true" :rules="selectRules" :model="selectParam" class="searchConditions">
        <el-form-item label="分班方案：" prop="planId">
          <el-select v-model="selectParam.planId" placeholder="请选择" @change="choosePlan">
            <el-option
              v-for="item in planList"
              :key="item.id"
              :label="item.name"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="年级：" prop="gradeId">
          <el-select v-model="selectParam.gradeId" placeholder="请选择" class="subject" @change="chooseClass">
            <el-option :label="grade.gradeName" :value="grade.gradeId" v-for="grade in gradeList"
                       :key="grade.gradeId"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="班级：" prop="className">
          <el-select v-model="selectParam.className" placeholder="请选择" class="major">
            <el-option :label="classData.className" :value="classData.className" v-for="classData in classList"
                       :key="classData.classId"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" icon="el-icon-search" @click="onSubmit">查询</el-button>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="subClassDivision_row" v-if="activePlan.changeStart">
      志愿修改时间：{{Number.parseInt(activePlan.changeStart)|formatDate}} -
      {{Number.parseInt(activePlan.changeEnd)|formatDate}}
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button class="delete" title="导出" @click="operationData('out')">
          <img class="delete_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" alt="">
          <img class="delete_active"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
               alt="">
        </el-button>
        <el-button-group class="secBtn-group">
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
            placeholder="请输入姓名/专业/科类"
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
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          prop="name"
          label="姓名">
        </el-table-column>
        <el-table-column
          prop="preGrade"
          label="年级">
        </el-table-column>
        <el-table-column
          prop="preClass"
          label="班级">
        </el-table-column>
        <el-table-column
          prop="branch"
          label="科类">
        </el-table-column>
        <el-table-column
          prop="major"
          label="专业">
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="edit_primary" @click="edit(scope.$index)">编辑</span>
          </template>
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
    <el-dialog
      title="编辑信息"
      :visible.sync="dialogVisible"
      :before-close="handleCloseDialog"
      :modal="false">
      <el-row class="formMsg">
        <el-form ref="form" :rules="rules" :model="saveParam" label-width="80px">
          <el-form-item label="科类：" prop="branch">
            <el-select v-model="saveParam.branch" placeholder="请选择" style="width: 100%" @change="chooseMajor">
              <el-option
                v-for="item in branchList"
                :key="item.branchId"
                :label="item.name"
                :value="item.name">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="专业：" prop="major">
            <el-select v-model="saveParam.major" placeholder="请选择" style="width: 100%">
              <el-option
                v-for="item in majorList"
                :key="item.wishId"
                :label="item.name"
                :value="item.name">
              </el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg">保存</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
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
        gradeList: [],
        planList: [],
        classList: [],
        branchList: [],
        majorList: [],
        activePlan: {},
        selectParam: {
          page: 1,
          count: 50,
          key: '',
          planId: '',
          gradeId: '',
          className: ''
        },
        saveParam: {
          planId: '',
          type: 'adjust',
          wishId: '',
          branch: '',
          major: '',
          id: ''
        },
        totalNum: 0,
        dialogVisible: false,
        rules: {
          branch: [
            {required: true, message: '请选择科类', trigger: 'change'}
          ],
          major: [
            {required: true, message: '请选择专业', trigger: 'change'}
          ]
        },
        selectRules: {
          planId: [
            {required: true, message: '请选择方案', trigger: 'change'}
          ],
          gradeId: [
            {required: true, message: '请选择年级', trigger: 'change'}
          ],
          className: [
            {required: true, message: '请选择班级', trigger: 'change'}
          ]
        },
        loading: false
      }
    },
    created: function () {
      var self = this, data = {
        func: 'classList'
      }, data1 = {
        func: 'getAllPlan'
      };
      //查询年级班级
      req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
        self.gradeList = res.data;
      });
      //查询方案列表
      req.ajaxSend('/school/DivideBranch/common', 'post', data1, function (res) {
        self.planList = res;
      });
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      },
      choosePlan(){
        for (let obj of this.planList) {
          if (obj.id == this.selectParam.planId) {
            this.activePlan = obj;
          }
        }
      },
      chooseClass(){
        this.selectParam.className = '';
        for (let obj of this.gradeList) {
          if (this.selectParam.gradeId == obj.gradeId) {
            this.classList = obj.classes;
          }
        }
      },
      onSubmit(){
        this.$refs['selectForm'].validate((valid) => {
          if (valid) {
            this.loadData(this.selectParam);
          } else {
            return false;
          }
        });
      },
      goSearch() {  //查询
        if (!this.selectParam.gradeId || !this.selectParam.className || !this.selectParam.planId) {
          this.vmMsgWarning('请选择相应参数！');
          return false;
        }
        this.selectParam.page = 1;
        this.loadData(this.selectParam);
      },
      handleCurrentChange(vale){
        this.selectParam.page = vale;
        this.loadData(this.selectParam);
      },
      edit(idx){
        var self = this, time = new Date().getTime(), data = {
          func: 'getWish',
          param: {
            planId: self.selectParam.planId
          }
        };
        if (time < Number.parseInt(self.activePlan.changeStart) * 1000) {
          self.vmMsgWarning('还未到该志愿修改时间！');
          return false;
        }
        if (time > Number.parseInt(self.activePlan.changeEnd) * 1000) {
          self.vmMsgWarning('该志愿修改时间已过！');
          return false;
        }
        self.dialogVisible = true;
        self.saveParam.branch = self.tableData[idx].branch;
        self.saveParam.major = self.tableData[idx].major;
        self.saveParam.id = self.tableData[idx].id;
        //查询科类和专业
        req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
          self.branchList = res.data;
          for (let obj of self.branchList) {
            if (obj.name == self.saveParam.branch) {
              self.majorList = obj.majors;
            }
          }
        });
      },
      chooseMajor(){
        this.saveParam.major = '';
        for (let obj of this.branchList) {
          if (this.saveParam.branch == obj.name) {
            this.majorList = obj.majors;
          }
        }
      },
      handleCloseDialog(done){   //关闭弹框
        done();
      },
      saveMsg(){
        var self = this;
        self.saveParam.planId = self.selectParam.planId;
        for (let obj of self.majorList) {
          if (obj.name == self.saveParam.major) {
            self.saveParam.wishId = obj.wishId;
          }
        }
        self.$refs['form'].validate((valid) => {
          if (valid) {
            req.ajaxSend('/school/DivideBranch/adjustWish', 'post', self.saveParam, function (res) {
              if (res.status == 1) {
                self.vmMsgSuccess('修改成功！');
                self.dialogVisible = false;
                self.loadData(self.selectParam);
              } else {
                self.vmMsgError(res.msg);
              }
            })
          } else {
            return false;
          }
        });
      },
      operationData(type){
        if (!this.selectParam.gradeId || !this.selectParam.className || !this.selectParam.planId) {
          this.vmMsgWarning('请选择相应参数！');
          return false;
        }
        let sAy = [], hdData;
        hdData = {
          name: '姓名',
          preGrade: '年级',
          preClass: '班级',
          branch: '科类',
          major: '专业'
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
          req.downloadFile('.subClassVolunteerHeadteacher', '/school/DivideBranch/adjustWish?export=ensure&gradeId=' + this.selectParam.gradeId + '&className=' + this.selectParam.className + '&planId=' + this.selectParam.planId, 'post');
        } else if (type == 'copy') {
          req.copyTableData('.subClassVolunteerHeadteacher', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/DivideBranch/adjustWish', 'post', data, function (res) {
          self.tableData = res.data;
          self.totalNum = res.total;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .subClassVolunteerHeadteacher {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .subClassVolunteerHeadteacher h3 {
    font-size: 1.25rem;
  }

  .subClassVolunteerHeadteacher .subClassDivision_title {
    margin: 2rem 0 0;
  }

  .subClassVolunteerHeadteacher .el-table td, .subClassVolunteerHeadteacher .el-table th {
    text-align: center;
  }

  .subClassVolunteerHeadteacher .el-table--border td {
    border-right: 0;
  }

  .subClassVolunteerHeadteacher .el-select.subject {
    width: 8.25rem;
  }

  .subClassVolunteerHeadteacher .el-select.major {
    width: 10.75rem;
  }

  .subClassVolunteerHeadteacher .searchConditions .el-button {
    border-radius: 20px;
    padding: 10px 30px;
  }

  .subClassVolunteerHeadteacher .el-form--inline .el-form-item {
    margin-right: 2rem;
  }

  .subClassVolunteerHeadteacher .alertsBtn {
    margin: 2rem 0 1.25rem;
  }

  .subClassVolunteerHeadteacher .g-fuzzyInput {
    float: right;
  }

  .subClassVolunteerHeadteacher .edit_primary {
    color: #20a0ff;
    cursor: pointer;
  }

  .subClassVolunteerHeadteacher .el-dialog--small {
    width: 600px;
  }

  .subClassVolunteerHeadteacher .formMsg {
    width: 70%;
    margin: auto;
  }

  .subClassVolunteerHeadteacher .subClassDivision_row {
    margin: 0 0 2rem;
  }
</style>
