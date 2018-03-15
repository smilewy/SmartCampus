<template>
  <div class="subClassVolunteerFill">
    <el-row type="flex" align="middle">
      <h3>填报分班志愿</h3>
    </el-row>
    <el-row class="subClassDivision_title">
      <el-form :inline="true">
        <el-form-item label="分班方案：">
          <el-select @change="getProgramme"
                     v-model="selectParam.planId" placeholder="请选择">
            <el-option
              v-for="item in planList"
              :key="item.id"
              :label="item.name"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="subClassDivision_title" v-if="activePlan.fillStart">
      志愿填报时间：{{Number.parseInt(activePlan.fillStart)|formatDate}} - {{Number.parseInt(activePlan.fillEnd)|formatDate}}
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
          label="科类">
          <template slot-scope="scope">
            {{scope.row.branch||'未选择'}}
          </template>
        </el-table-column>
        <el-table-column
          label="专业">
          <template slot-scope="scope">
            {{scope.row.major||'未选择'}}
          </template>
        </el-table-column>
        <el-table-column
          label="选择志愿">
          <template slot-scope="scope">
            <span class="edit_primary" @click="edit(scope.$index)">选择志愿</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="选择志愿"
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
        <el-button type="primary" @click="saveMsg">提交</el-button>
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
        branchList: [],
        majorList: [],
        planList: [],
        activePlan: {},
        selectParam: {
          planId: ''
        },
        saveParam: {
          id: '',
          planId: '',
          type: 'submit',
          wishId: '',
          branch: '',
          major: ''
        },
        dialogVisible: false,
        rules: {
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
      var self = this, data = {
        func: 'getBelong'
      };
      //查询方案列表
      req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
        self.planList = res.data;
      });
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      },
      getProgramme(){
        for (let obj of this.planList) {
          if (obj.id == this.selectParam.planId) {
            this.activePlan = obj;
          }
        }
        this.loadData(this.selectParam);
      },
      edit(ix){
        var self = this, time = new Date().getTime(), data = {
          func: 'getWish',
          param: {
            planId: self.selectParam.planId
          }
        };
        if (time < Number.parseInt(self.activePlan.fillStart) * 1000) {
          self.vmMsgWarning('还未到该志愿填报时间！');
          return false;
        }
        if (time > Number.parseInt(self.activePlan.fillEnd) * 1000) {
          self.vmMsgWarning('该志愿填报时间已过！');
          return false;
        }
        self.dialogVisible = true;
        self.saveParam.branch = self.tableData[ix].branch;
        self.saveParam.major = self.tableData[ix].major;
        self.saveParam.id = self.tableData[ix].id;
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
            req.ajaxSend('/school/DivideBranch/fillWish', 'post', self.saveParam, function (res) {
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
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/DivideBranch/fillWish', 'post', data, function (res) {
          self.tableData = [res.data];
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .subClassVolunteerFill {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .subClassVolunteerFill h3 {
    font-size: 1.25rem;
  }

  .subClassVolunteerFill .subClassDivision_title {
    margin: 2rem 0 1rem;
  }

  .subClassVolunteerFill .el-table td, .subClassVolunteerFill .el-table th {
    text-align: center;
  }

  .subClassVolunteerFill .el-table--border td {
    border-right: 0;
  }

  .subClassVolunteerFill .el-select.subject {
    width: 8.25rem;
  }

  .subClassVolunteerFill .el-select.major {
    width: 10.75rem;
  }

  .subClassVolunteerFill .searchConditions .el-button {
    border-radius: 20px;
    padding: 10px 30px;
  }

  .subClassVolunteerFill .el-form--inline .el-form-item {
    margin-right: 2rem;
    margin-bottom: 0;
  }

  .subClassVolunteerFill .edit_primary {
    color: #20a0ff;
    cursor: pointer;
  }

  .subClassVolunteerFill .el-dialog--small {
    width: 600px;
  }

  .subClassVolunteerFill .formMsg {
    width: 70%;
    margin: auto;
  }
</style>
