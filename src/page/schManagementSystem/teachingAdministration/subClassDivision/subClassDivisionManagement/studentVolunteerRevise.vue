<template>
  <div class="scoreDetails">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-col :span="14">
        <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
          src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
          alt=""><span class="returnTxt">返回流程图</span></el-button>
        <h3>学生志愿调整</h3>
      </el-col>
      <el-col :span="10" class="operationBtns">
        <el-button type="primary" @click="toNext">批量导入志愿</el-button>
      </el-col>
    </el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button class="filt" title="复制" @click="operationData('copy')">
          <img class="filt_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
               alt="">
          <img class="filt_active"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
               alt="">
        </el-button>
      </el-col>
      <el-col :span="6">
        <div class="g-fuzzyInput">
          <el-input
            placeholder="请输入关键字"
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
          label="年级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="preClass"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="name"
          label="姓名" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="branch"
          label="科类" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="major"
          label="专业" sortable="custom">
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
        <el-form ref="form" :rules="rules" :model="saveParam.wish" label-width="80px">
          <el-form-item label="科类：" prop="branch">
            <el-select v-model="saveParam.wish.branch" placeholder="请选择" @change="chooseMajor">
              <el-option
                v-for="item in branchList"
                :key="item.branchId"
                :label="item.name"
                :value="item.name">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="专业：" prop="major">
            <el-select v-model="saveParam.wish.major" placeholder="请选择">
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
        branchList: [],
        majorList: [],
        selectParam: {
          planId: '',
          page: 1,
          count: 50,
          key: '',
          according: '',
          order: ''
        },
        saveParam: {
          planId: '',
          type: 'edit',
          sId: '',
          wish: {
            wishId: '',
            branch: '',
            major: ''
          }
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
        loading: false
      }
    },
    created: function () {
      var self = this, data;
      self.selectParam.planId = self.$route.params.planId;
      self.saveParam.planId = self.selectParam.planId;
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
      self.loadData(self.selectParam);
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.order = '';
        this.selectParam.sort = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.order = column.order || '';
        this.selectParam.sort = column.prop || '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      edit(idx){
        this.dialogVisible = true;
        this.saveParam.wish.branch = this.tableData[idx].branch;
        this.saveParam.wish.major = this.tableData[idx].major;
        this.saveParam.sId = this.tableData[idx].sId;
        for (let obj of this.branchList) {
          if (obj.name == this.saveParam.wish.branch) {
            this.majorList = obj.majors;
          }
        }
      },
      chooseMajor(){
        for (let obj of this.branchList) {
          if (this.saveParam.wish.branch == obj.name) {
            this.majorList = obj.majors;
            this.saveParam.wish.major = this.majorList[0].name;
          }
        }
      },
      handleCloseDialog(done){   //关闭弹框
        done();
      },
      saveMsg(){
        var self = this;
        for (let obj of self.majorList) {
          if (obj.name == self.saveParam.wish.major) {
            self.saveParam.wish.wishId = obj.wishId;
          }
        }
        self.$refs['form'].validate((valid) => {
          if (valid) {
            req.ajaxSend('/school/DivideBranch/wishAdjust', 'post', self.saveParam, function (res) {
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
      toNext(){  //进入批量导入志愿页面
        this.$router.push({name: 'importStudentVolunteer', params: {planId: this.selectParam.planId}});
      },
      operationData(type) {
        let sAy = [], hdData;
        hdData = {
          preGrade: '年级',
          preClass: '班级',
          name: '姓名',
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
        if (type == 'copy') {
          req.copyTableData('.scoreDetails', sAy);
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/DivideBranch/wishAdjust', 'post', data, function (res) {
          self.loading = false;
          self.tableData = res.data;
          self.totalNum = res.total;
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
