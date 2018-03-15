<template>
  <div class="testTime">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>各科目考试时间</h3>
    </el-row>
    <el-row class="examManager_row switch" type="flex" align="middle">
      <span>各科考试时间（相同科目的考试时间自动同步）：</span>
      <el-switch
        v-model="isPublic"
        active-color="#13b5b1"
        inactive-color="#ff4949"
        active-text="是"
        inactive-text="否">
      </el-switch>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-button class="delete" title="导出" @click="operationData('out')">
        <img class="delete_unactive"
             src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
             alt="">
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
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          prop="branch"
          label="科类">
        </el-table-column>
        <el-table-column
          prop="subject"
          label="科目">
        </el-table-column>
        <el-table-column
          prop="date"
          label="考试日期">
        </el-table-column>
        <el-table-column
          prop="starttime"
          label="开考时间">
        </el-table-column>
        <el-table-column
          prop="endtime"
          label="结束时间">
        </el-table-column>
        <el-table-column
          prop="duration"
          label="考试时长（分钟）">
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="edit" @click="editMsg(scope.$index)">编辑</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="修改信息"
      :visible.sync="dialogVisible"
      :before-close="handleClose"
      :modal="false">
      <el-row class="testNumber_dialog_body">
        <el-form ref="form" :model="form" :rules="formRules" label-width="120px" class="testNumber_form">
          <el-form-item label="考试时间：" prop="date">
            <el-date-picker
              v-model="form.date"
              :editable="false"
              type="date"
              :picker-options="pickerBeginDate"
              placeholder="请选择">
            </el-date-picker>
          </el-form-item>
          <el-form-item label="开考时间：" style="width:100%;" prop="starttime">
            <el-time-select :editable="false"
                            :picker-options="pickerStart"
                            v-model="form.starttime"
                            placeholder="请选择">
            </el-time-select>
          </el-form-item>
          <el-form-item label="结束时间：" prop="endtime">
            <el-time-select
              :editable="false"
              :picker-options="pickerStart"
              v-model="form.endtime"
              placeholder="请选择">
            </el-time-select>
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
  import moment from 'moment'
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        isPublic: true,
        tableData: [],
        form: {
          id: '',
          subjectid: '',
          date: '',
          starttime: '',
          endtime: ''
        },
        dialogVisible: false,
        selectParam: {
          examinationid: ''
        },
        timeRange: {
          st: '',
          et: ''
        },
        formRules: {
          date: [
            {type: 'date', required: true, message: '请选择考试日期', trigger: 'change'}
          ],
          starttime: [
            {required: true, message: '请选择考试开始时间', trigger: 'change'}
          ],
          endtime: [
            {required: true, message: '请选择考试结束时间', trigger: 'change'}
          ]
        },
        pickerBeginDate: {
          disabledDate: (time) => {
            let endDateVal = this.timeRange.et, beginDateVal = this.timeRange.st;
            if (endDateVal || beginDateVal) {
              return time.getTime() > endDateVal || time.getTime() < beginDateVal;
            }
          }
        },
        pickerStart: {
          start: '00:00',
          step: '00:15',
          end: '23:59'
        },
        loading: false
      }
    },
    created: function () {
      this.selectParam.examinationid = this.$route.params.examinationid;
      this.form.examinationid = this.selectParam.examinationid;
      this.loadData(this.selectParam);
    },
    methods: {
      returnFlowchart() {
        this.$router.push('/examManagerHome');
      },
      editMsg(idx) {
        this.dialogVisible = true;
        this.form.id = this.isPublic ? '' : this.tableData[idx].id;
        this.form.subjectid = this.tableData[idx].subjectid;
        this.form.date = this.tableData[idx].date ? new Date(this.tableData[idx].date) : this.tableData[idx].date;
        this.form.starttime = this.tableData[idx].starttime;
        this.form.endtime = this.tableData[idx].endtime;
      },
      handleClose(done) {
        done();
      },
      operationData(type) {
        let sAy = [], hdData = {
          branch: '科类',
          subject: '科目',
          date: '考试日期',
          starttime: '开考时间',
          endtime: '结束时间',
          duration: '考试时长（分钟）',
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
          req.downloadFile('.testTime', '/school/Examination/exmanagement/type/exsubject/typename/exsexport?examinationid=' + this.selectParam.examinationid, 'post');
        } else if (type == 'copy') {
          req.copyTableData('.testTime', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      saveMsg() {
        var self = this, tData;
        self.$refs['form'].validate((valid) => {
          if (valid) {
            tData = {
              examinationid: self.selectParam.examinationid,
              subjectid: self.form.subjectid,
              id: self.form.id,
              date: moment(new Date(self.form.date)).format('YYYY-MM-DD'),
              starttime: self.form.starttime,
              endtime: self.form.endtime
            };
            if (new Date(tData.date + ' ' + tData.starttime).getTime() >= new Date(tData.date + ' ' + tData.endtime).getTime()) {
              self.vmMsgWarning('开考时间必须小于结束时间！');
              return false;
            }
            if (new Date(tData.date + ' ' + tData.starttime).getTime() < self.timeRange.st || new Date(tData.date + ' ' + tData.starttime).getTime() > self.timeRange.et + 24 * 60 * 60 * 1000) {
              self.vmMsgWarning('考试日期只能在创建的考试时间段内选择！');
              return false;
            }
            if (new Date(tData.date + ' ' + tData.endtime).getTime() < self.timeRange.st || new Date(tData.date + ' ' + tData.endtime).getTime() > self.timeRange.et + 24 * 60 * 60 * 1000) {
              self.vmMsgWarning('考试日期只能在创建的考试时间段内选择！');
              return false;
            }
            req.ajaxSend('/school/Examination/exmanagement/type/exsubject/typename/exsupdate', 'post', tData, function (res) {
              if (res.return) {
                self.vmMsgSuccess('保存成功！');
                self.dialogVisible = false;
                self.loadData(self.selectParam);
              } else {
                self.vmMsgError(res.message);
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
        req.ajaxSend('/school/Examination/exmanagement/type/exsubject/typename/exsfind', 'post', data, function (res) {
          self.timeRange.st = res.starttime;
          self.timeRange.et = res.endtime;
          self.tableData = res.data;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .testTime .el-date-editor.el-input {
    width: 300px;
  }

  .testTime .edit {
    color: #ff5b5a;
    cursor: pointer;
  }
</style>
