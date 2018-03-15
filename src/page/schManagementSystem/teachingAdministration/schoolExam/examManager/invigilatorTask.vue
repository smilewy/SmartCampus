<template>
  <div class="invigilatorTask">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>监考任务</h3>
    </el-row>
    <el-row class="examManager_row">
      <span>应排总监考次数：{{countData.invigilatorall}}</span>
      <span>已排总监考次数：{{countData.invigilator}}</span>
      <span>巡考次数：{{countData.visits}}</span>
      <span>总巡考次数：{{countData.totalinspection}}</span>
      <span>总安排次数：{{countData.arrangeall}}</span>
      <span>总人均次数：{{countData.arrange}}</span>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button-group>
          <el-button class="filt" title="添加" @click="openDialog('add')">
            <img class="filt_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add.png"
                 alt="">
            <img class="filt_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png"
                 alt="">
          </el-button>
          <el-button class="delete" title="删除" @click="deleteAlerts">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete_highlight.png"
                 alt="">
          </el-button>
        </el-button-group>
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
            placeholder="请输入姓名"
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
        @selection-change="handleSelectionChange"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          type="selection"
          width="55">
        </el-table-column>
        <el-table-column
          type="index"
          width="80"
          label="序号">
        </el-table-column>
        <el-table-column
          prop="name"
          min-width="120"
          label="姓名">
        </el-table-column>
        <el-table-column
          prop="branch"
          min-width="120"
          label="任教科类">
        </el-table-column>
        <el-table-column
          prop="teachingSubjects"
          min-width="120"
          label="任教学科">
        </el-table-column>
        <el-table-column
          prop="invigilator"
          min-width="120"
          label="监考次数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="visits"
          min-width="120"
          label="巡考次数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="totalinspection"
          min-width="150"
          label="总巡考次数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="arrange"
          min-width="120"
          label="安排次数" sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="120"
          label="班主任">
          <template slot-scope="scope">
            <span v-if="scope.row.headmaster">是</span>
            <span v-if="!scope.row.headmaster">否</span>
          </template>
        </el-table-column>
        <el-table-column
          min-width="120"
          label="工作人员">
          <template slot-scope="scope">
            <span v-if="scope.row.staff">是</span>
            <span v-if="!scope.row.staff">否</span>
          </template>
        </el-table-column>
        <el-table-column
          min-width="100"
          fixed="right"
          label="操作">
          <template slot-scope="scope">
            <span class="delete" @click="openDialog('edit',scope.$index)">编辑</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableData.length!=0">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="selectParam.page"
        :page-size="selectParam.limit"
        layout="prev, pager, next, jumper"
        :total="totalNum">
      </el-pagination>
    </el-row>
    <el-dialog
      title="教师名单"
      :visible.sync="dialogVisible"
      :before-close="handleClose"
      :modal="false"
      class="seatLayout">
      <el-row>
        <el-table
          :data="teacherData"
          style="width: 100%"
          max-height="440"
          @sort-change="sort"
          @selection-change="chooseTeacher"
          v-loading="loading1"
          element-loading-text="拼命加载中"
        >
          <el-table-column
            type="selection"
            width="55">
          </el-table-column>
          <el-table-column
            prop="name"
            label="姓名">
          </el-table-column>
          <el-table-column
            prop="grade"
            label="年级">
          </el-table-column>
          <el-table-column
            prop="branch"
            label="科类">
          </el-table-column>
          <el-table-column
            prop="teachingSubjects"
            label="科目">
          </el-table-column>
        </el-table>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg('add')">选择</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      title="修改信息"
      :visible.sync="editDialogVisible"
      :before-close="handleClose"
      :modal="false">
      <el-row class="testNumber_dialog_body">
        <el-form ref="messageForm" :model="message" :rules="rules" label-width="120px">
          <el-form-item label="监考次数：" prop="invigilator">
            <el-input v-model.number="message.invigilator"></el-input>
          </el-form-item>
          <el-form-item label="巡考次数：" prop="visits">
            <el-input v-model.number="message.visits"></el-input>
          </el-form-item>
          <el-form-item label="总巡考次数：" prop="totalinspection">
            <el-input v-model.number="message.totalinspection"></el-input>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg('edit')">保存</el-button>
        <el-button @click="editDialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      var validateInvigilator = (rule, value, callback) => {
        if (!Number.isInteger(value)) {
          callback(new Error('请输入监考次数，且为数字'));
        } else {
          callback();
        }
      };
      var visitsInvigilator = (rule, value, callback) => {
        if (!Number.isInteger(value)) {
          callback(new Error('请输入巡考次数，且为数字'));
        } else {
          callback();
        }
      };
      var totalinspectionInvigilator = (rule, value, callback) => {
        if (!Number.isInteger(value)) {
          callback(new Error('请输入总巡考次数，且为数字'));
        } else {
          callback();
        }
      };
      return {
        countData: {},
        tableData: [],
        teacherData: [],
        searchValue: '',
        multipleSelection: [],
        multipleSelectionDialog: [],
        selectParam: {
          examinationid: '',
          order: '',
          field: '',
          screen: '',
          limit: 50,
          page: 1
        },
        totalNum: 0,
        dialogVisible: false,
        editDialogVisible: false,
        message: {
          id: '',
          invigilator: '',
          visits: '',
          totalinspection: ''
        },
        allSlot: 0,
        rules: {
          invigilator: [
            {required: true, validator: validateInvigilator, trigger: 'blur'}
          ],
          visits: [
            {required: true, validator: visitsInvigilator, trigger: 'blur'}
          ],
          totalinspection: [
            {required: true, validator: totalinspectionInvigilator, trigger: 'blur'}
          ]
        },
        loading: false,
        loading1: false
      }
    },
    created: function () {
      var self = this;
      self.selectParam.examinationid = self.$route.params.examinationid;
      self.loadData(self.selectParam);
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/examManagerHome');
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.field = '';
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.field = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      chooseTeacher(val) {
        this.multipleSelectionDialog = val;
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      handleClose(done) {   //关闭弹框
        done();
      },
      openDialog(val, idx){
        var self = this, data;
        if (val == 'add') {
          self.dialogVisible = true;
          data = {
            examinationid: self.selectParam.examinationid
          };
          self.loading1 = true;
          req.ajaxSend('/school/Examination/exmanagement/type/invigilatortask/typename/teacher', 'post', data, function (res) {
            self.teacherData = res;
            self.loading1 = false;
          })
        } else {
          self.editDialogVisible = true;
          self.message.id = self.tableData[idx].id;
          self.message.invigilator = Number.parseInt(self.tableData[idx].invigilator);
          self.message.visits = Number.parseInt(self.tableData[idx].visits);
          self.message.totalinspection = Number.parseInt(self.tableData[idx].totalinspection);
        }
      },
      operationData(type){
        let sAy = [], hdData = {
          name: '姓名',
          branch: '任教科类',
          teachingSubjects: '任教学科',
          invigilator: '监考次数',
          visits: '巡考次数',
          totalinspection: '总巡考次数',
          arrange: '安排次数',
          headmaster: '班主任',
          staff: '工作人员'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if (name == 'headmaster' || name == 'staff') {
              d[name] = obj[name] ? '是' : '否';
            } else {
              d[name] = obj[name] || '';
            }
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.invigilatorTask', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      saveMsg(type){
        var self = this, data, ary = [];
        if (type == 'add') {
          for (let obj of self.multipleSelectionDialog) {
            ary.push(obj.userid);
          }
          data = {
            examinationid: self.selectParam.examinationid,
            userid: ary
          };
          req.ajaxSend('/school/Examination/exmanagement/type/invigilatortask/typename/exteacherinsert', 'post', data, function (res) {
            if (res.return) {
              self.vmMsgSuccess('添加成功!');
              self.dialogVisible = false;
              self.selectParam.page = 1;
              self.selectParam.screen = '';
              self.selectParam.field = '';
              self.selectParam.order = '';
              self.loadData(self.selectParam);
            } else {
              self.vmMsgError('添加失败!');
            }
          })
        } else {
          self.$refs['messageForm'].validate((valid) => {
            if (valid) {
              if (Number.parseInt(self.message.invigilator) + Number.parseInt(self.message.visits) > self.allSlot) {
                self.vmMsgWarning('监考次数与巡考次数的和必须<=考试时间段数!');
                return false;
              }
              if (Number.parseInt(self.message.invigilator) + Number.parseInt(self.message.totalinspection) > self.allSlot) {
                self.vmMsgWarning('监考次数与总巡考次数的和必须<=考试时间段数!');
                return false;
              }
              req.ajaxSend('/school/Examination/exmanagement/type/invigilatortask/typename/exteacherup', 'post', self.message, function (res) {
                if (res.return) {
                  self.vmMsgSuccess('修改成功!');
                  self.editDialogVisible = false;
                  self.loadData(self.selectParam);
                } else {
                  self.vmMsgError('修改失败!');
                }
              });
            } else {
              return false;
            }
          });
        }
      },
      deleteAlerts(){
        var self = this;
        if (self.multipleSelection.length == 0) {
          self.vmMsgWarning('请选择记录！');
          return false;
        }
        self.$confirm('确定删除记录?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          var data = {
            id: []
          };
          for (let obj of self.multipleSelection) {
            data.id.push(obj.id);
          }
          req.ajaxSend('/school/Examination/exmanagement/type/invigilatortask/typename/exteacherdel', 'post', data, function (res) {
            if (res.return) {
              self.vmMsgSuccess('删除成功!');
              self.loadData(self.selectParam);
            } else {
              self.vmMsgError('删除失败!');
            }
          });
        }).catch(() => {
        });
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Examination/exmanagement/type/invigilatortask/typename/examteacher', 'post', data, function (res) {
          self.loading = false;
          self.countData = res.all;
          self.tableData = res.exam;
          self.allSlot = res.slot;
          self.totalNum = Number.parseInt(res.page.count);
        })
      }
    }
  }
</script>
<style>
  .invigilatorTask .el-dialog--large {
    width: 1000px;
  }

  .invigilatorTask .seatLayout .el-dialog__footer {
    -webkit-box-shadow: 0 -8px 30px -10px #d2d2d2;
    -moz-box-shadow: 0 -8px 30px -10px #d2d2d2;
    box-shadow: 0 -8px 30px -10px #d2d2d2;
    text-align: right;
  }

  .invigilatorTask .delete {
    color: #ff5b5a;
    cursor: pointer;
  }

  .invigilatorTask .search {
    margin-bottom: 30px;
  }

  .invigilatorTask .testNumber_dialog_body {
    padding: 0 60px;
  }
</style>
