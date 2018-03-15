<template>
  <div class="generationLeave">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <h3>代学生请假</h3>
    </el-row>
    <el-row :gutter="70" class="generationLeave_body">
      <el-col :span="7">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>待选学生</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="请输入查询班级或姓名"
                v-model="filterText">
                <template slot="prepend">
                  <i class="el-icon-search"></i>
                </template>
              </el-input>
            </el-row>
          </el-row>
          <el-row class="d_line"></el-row>
          <el-row class="treeList_body"
                  v-loading="loading"
                  element-loading-text="拼命加载中">
            <el-tree
              :data="treeData"
              node-key="id"
              ref="tree"
              :highlight-current="true"
              :filter-node-method="filterNode"
              @node-click="chooseStudent"
              :props="defaultProps">
            </el-tree>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="17">
        <el-form ref="form" :model="form" :rules="rules" label-width="120px">
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
          <el-form-item label="请假原因：">
            <el-row class="leaveReasonList">
            <span class="leaveReason" v-for="(leaveReason,index) in leaveReasonList"
                  :key="leaveReason.id" @click="chooseLeaveReason(index)">{{leaveReason.name}}</span>
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
            <el-button type="primary" @click="save('save')">提交</el-button>
            <el-button @click="save('reset')">重置</el-button>
          </el-form-item>
        </el-form>
      </el-col>
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
          userId: '',
          leaveTypeId: '',
          startTime: '',
          endTime: '',
          endTimeMinutes: '',
          startTimeMinutes: '',
          reason: '',
          title: ''
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
        treeData: [],
        defaultProps: {
          children: 'data',
          label: 'name'
        },
        filterText: '',
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
        },
        loading: false
      }
    },
    watch: {
      filterText(val) {
        this.$refs.tree.filter(val);
      }
    },
    created: function () {
      var self = this;
      self.loading = true;
      req.ajaxSend('/school/Studentleave/replaceLe?type=getStudentList', 'get', '', function (res) {
        self.treeData = res.data;
        self.loading = false;
      })
    },
    methods: {
      filterNode(value, data) {
        if (!value) return true;
        if (data.name) {
          data.name = data.name.toString();
          return data.name.indexOf(value) !== -1;
        }
      },
      chooseStudent(data) {
        if (!data.data) {
          this.form.userId = data.id;
          this.form.title = data.grade + ' - ' + data.className + ' - ' + data.name + '的请假单';
        }
      },
      chooseLeaveReason(idx) {
        this.leaveReasonActiveList = this.leaveReasonList[idx];
      },
      handleClose(idx) {
        this.leaveReasonActiveList = {};
      },
      save(type) {
        var self = this, data = {};
        if (type == 'save') {
          if (!self.form.userId) {
            self.vmMsgWarning('请点击选择学生！');
            return false;
          }
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
              req.ajaxSend('/school/Studentleave/replaceLe?type=createLeave', 'post', data, function (res) {
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
            title: '',
            leaveTypeId: '',
            startTime: '',
            endTime: '',
            reason: ''
          }
        }
      }
    }
  }
</script>
<style>
  .generationLeave {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .generationLeave h3 {
    font-size: 1.25rem;
  }

  .generationLeave .generationLeave_body {
    margin-top: 3.75rem;
  }

  .generationLeave .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
    -webkit-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    -moz-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    box-shadow: 0 0 1px 1px #d2d2d2 inset;
  }

  .generationLeave .treeList_body {
    padding: .875rem;
    height: 44rem;
    overflow: auto;
  }

  .generationLeave .treeList_title {
    padding: .875rem .875rem 1.5rem;
  }

  .generationLeave .treeList_title h5 {
    font-size: 1rem;
  }

  .generationLeave .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .generationLeave .treeList .el-tree {
    border: none;
  }

  .generationLeave .alertsBtn {
    margin: 0 0 1.25rem 0;
  }

  .generationLeave .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .generationLeave .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  .generationLeave .line {
    text-align: center;
  }

  .generationLeave .submitBtn {
    text-align: right;
  }

  .generationLeave .submitBtn .el-button {
    width: 7.5rem;
    padding: 10px 0;
    border-radius: 20px;
    border: 1px solid #4da1ff;
    color: #4da1ff;
  }

  .generationLeave .submitBtn .el-button--primary {
    color: #fff;
  }

  .generationLeave .leaveReason {
    display: inline-block;
    padding: 8px 16px;
    color: #f08bc5;
    border: 1px solid #f08bc5;
    border-radius: 4px;
    cursor: pointer;
    margin-bottom: 1rem;
    line-height: 1;
  }

  .generationLeave .leaveReason + .leaveReason {
    margin-left: 1.25rem;
  }

  .generationLeave .leaveReasonContent {
    border: 1px solid #bfcbd9;
    border-radius: 4px;
    padding: .5rem 1rem;
  }

  .generationLeave .el-textarea__inner {
    font-family: inherit;
    height: 10rem;
    margin-top: .5rem;
    border: none;
  }

  .generationLeave .leaveReasonContent .el-tag {
    background-color: #f08bc5;
    padding: 0px 10px;
    color: #fff;
  }

  .generationLeave .leaveReasonContent .el-tag + .el-tag {
    margin-left: 1rem;
  }
</style>
