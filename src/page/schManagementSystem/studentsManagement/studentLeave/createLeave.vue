<template>
  <div class="createLeave">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <h3>新建请假</h3>
    </el-row>
    <el-row class="createLeave_body">
      <el-form ref="form" :model="form" :rules="rules" label-width="120px" class="subMsg">
        <el-form-item label="请假标题：" prop="title">
          <el-input v-model="form.title" placeholder="--的请假单"></el-input>
        </el-form-item>
        <el-form-item label="请假类型：" prop="leaveTypeId">
          <el-select v-model="form.leaveTypeId" placeholder="请选择" style="width:100%;">
            <el-option
              v-for="item in leaveTypeList"
              :key="item.id"
              :label="item.name"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="起始时间：" required>
          <el-row>
            <el-col :span="8">
              <el-form-item prop="startTime">
                <el-date-picker type="date" placeholder="选择日期" v-model="form.startTime"
                                style="width: 100%;" :picker-options="pickerBeginDateBefore"
                                :editable="false"></el-date-picker>
              </el-form-item>
            </el-col>
            <el-col class="line" :span="1">-</el-col>
            <el-col :span="8">
              <el-form-item prop="startTimeMinutes">
                <el-time-select type="fixed-time" placeholder="选择时间" v-model="form.startTimeMinutes"
                                style="width: 100%;" :editable="false"
                                :picker-options="startTimeMinutesRange"></el-time-select>
              </el-form-item>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="结束时间：" required>
          <el-row>
            <el-col :span="8">
              <el-form-item prop="endTime">
                <el-date-picker type="date" placeholder="选择日期" v-model="form.endTime"
                                style="width: 100%;" :picker-options="pickerBeginDateAfter"
                                :editable="false"></el-date-picker>
              </el-form-item>
            </el-col>
            <el-col class="line" :span="1">-</el-col>
            <el-col :span="8">
              <el-form-item prop="endTimeMinutes">
                <el-time-select type="fixed-time" placeholder="选择时间" v-model="form.endTimeMinutes"
                                style="width: 100%;" :editable="false"
                                :picker-options="endTimeMinutesRange"></el-time-select>
              </el-form-item>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="请假原因：" prop="reason">
          <el-row class="leaveReasonList">
            <span class="leaveReason" v-for="(leaveReason,index) in leaveReasonList"
                  :key="index" @click="chooseLeaveReason(index)">{{leaveReason.name}}</span>
          </el-row>
          <el-row class="leaveReasonContent">
            <el-row>
              <el-tag v-if="leaveReasonActiveList.name"
                      :closable="true"
                      :close-transition="false"
                      @close="handleClose"
              >
                {{leaveReasonActiveList.name}}
              </el-tag>
            </el-row>
            <el-input resize="none" type="textarea" v-model="form.reason" placeholder="请输入请假原因"></el-input>
          </el-row>
        </el-form-item>
        <el-form-item class="submitBtn">
          <el-button type="primary" @click="save('save')">创建</el-button>
          <el-button @click="save('reset')">重置</el-button>
        </el-form-item>
      </el-form>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import moment from 'moment'

  export default {
    data() {
      return {
        form: {
          title: '高一 - 1班 - 张三的请假单',
          leaveTypeId: '',
          startTime: '',
          endTime: '',
          endTimeMinutes: '',
          startTimeMinutes: '',
          times: '',
          reason: ''
        },
        leaveTypeList: [
          {
            name: '事假',
            id: 1
          }, {
            name: '病假',
            id: 2
          }, {
            name: '其他',
            id: 3
          }
        ],
        leaveReasonList: [
          {
            name: '因病请假'
          }, {
            name: '因事请假'
          }, {
            name: '其他'
          }
        ],
        leaveReasonActiveList: {},
        rules: {
          title: [
            {required: true, message: '请输入请假标题', trigger: 'blur'},
            {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
          ],
          leaveTypeId: [
            {required: true, type: 'number', message: '请选择请假类型', trigger: 'change'}
          ],
          startTime: [
            {type: 'date', required: true, message: '请选择日期', trigger: 'change'}
          ],
          startTimeMinutes: [
            {required: true, type: 'string', message: '请选择时间', trigger: 'change'}
          ],
          endTime: [
            {type: 'date', required: true, message: '请选择日期', trigger: 'change'}
          ],
          endTimeMinutes: [
            {required: true, type: 'string', message: '请选择时间', trigger: 'change'}
          ]
        },
        pickerBeginDateBefore: {
          disabledDate: (time) => {
            let beginDateVal = this.form.endTime;
            if (beginDateVal) {
              return time.getTime() > beginDateVal;
            }
          }
        },
        pickerBeginDateAfter: {
          disabledDate: (time) => {
            let beginDateVal = this.form.startTime;
            if (beginDateVal) {
              return time.getTime() < beginDateVal;
            }
          }
        },
        startTimeMinutesRange: {
          start: '00:00',
          step: '00:01',
          end: '23:59'
        },
        endTimeMinutesRange: {
          start: '00:00',
          step: '00:01',
          end: '23:59'
        }
      }
    },
    methods: {
      chooseLeaveReason(idx) {
        this.leaveReasonActiveList = this.leaveReasonList[idx];
      },
      handleClose(idx) {
        this.leaveReasonActiveList = {};
      },
      save(type) {
        var self = this, data = {};
        if (type == 'save') {
          self.$refs['form'].validate((valid) => {
            if (valid) {
              for (let name in self.form) {
                if (name == 'startTime' || name == 'endTime') {
                  data[name] = moment(self.form[name]).format('YYYY-MM-DD') + ' ' + self.form[name + 'Minutes'];
                } else if (name == 'reason') {
                  data[name] = (self.leaveReasonActiveList.name || '') + ' ' + self.form[name];
                } else if (name != 'endTimeMinutes' && name != 'startTimeMinutes') {
                  data[name] = self.form[name];
                }
              }
              if (new Date(data.startTime).getTime() > new Date(data.endTime).getTime()) {
                self.vmMsgWarning('请假起始时间必须小于结束时间！');
                return false;
              }
              req.ajaxSend('/school/Studentleave/createLeave?type=createLeave', 'post', data, function (res) {
                if (res.statu == 1) {
                  self.vmMsgSuccess('创建成功！');
                } else {
                  self.vmMsgError(res.message);
                }
              })
            } else {
              return false;
            }
          });
        } else {
          self.form = {
            title: '高一 - 1班 - 张三的请假单',
            leaveTypeId: '',
            startTime: '',
            endTime: '',
            times: '',
            reason: ''
          }
        }
      }
    }
  }
</script>
<style>
  .createLeave {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .createLeave h3 {
    font-size: 1.25rem;
  }

  .createLeave .createLeave_body {
    margin: 3.43rem 0 10rem;
  }

  .createLeave .subMsg {
    width: 65%;
    margin: auto;
  }

  .createLeave .line {
    text-align: center;
  }

  .createLeave .submitBtn {
    text-align: right;
  }

  .createLeave .submitBtn .el-button {
    width: 7.5rem;
    padding: 10px 0;
    border-radius: 20px;
    border: 1px solid #4da1ff;
    color: #4da1ff;
  }

  .createLeave .submitBtn .el-button--primary {
    color: #fff;
  }

  .createLeave .leaveReason {
    display: inline-block;
    padding: 8px 16px;
    color: #f08bc5;
    border: 1px solid #f08bc5;
    border-radius: 4px;
    cursor: pointer;
    margin-bottom: 1rem;
    line-height: 1;
  }

  .createLeave .leaveReason + .leaveReason {
    margin-left: 1.25rem;
  }

  .createLeave .leaveReasonContent {
    border: 1px solid #bfcbd9;
    border-radius: 4px;
    padding: .5rem 1rem;
  }

  .createLeave .el-textarea__inner {
    font-family: inherit;
    height: 10rem;
    margin-top: .5rem;
    border: none;
  }

  .createLeave .leaveReasonContent .el-tag {
    background-color: #f08bc5;
    padding: 0px 10px;
    color: #fff;
  }

  .createLeave .leaveReasonContent .el-tag + .el-tag {
    margin-left: 1rem;
  }
</style>
