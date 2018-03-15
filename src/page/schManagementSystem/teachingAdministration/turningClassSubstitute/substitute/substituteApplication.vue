<template>
  <div class="substituteApplication">
    <el-row type="flex" align="middle">
      <el-col :span="8">
        <h3>代课申请</h3>
      </el-col>
      <el-col :span="16" class="operationBtn">
        <el-button type="primary" class="clearBtn" @click="save('clear')">清空</el-button>
        <el-button type="primary" @click="chooseTime">生效时间</el-button>
        <el-button type="primary" @click="save('save')">提交申请</el-button>
      </el-col>
    </el-row>
    <el-row :gutter="20" class="substituteApplication_row">
      <el-col :span="6">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>可选代课教师（单选）</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入查询分类或姓名"
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
              :filter-node-method="filterNode"
              @node-click="chooseTeacher"
              :props="defaultProps">
            </el-tree>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="18">
        <el-row class="specifiedTurning_body">
          <el-row type="flex" align="middle" class="scheduleSearch">
            <el-col :span="8">
              {{userInfoR.techerName}}--课表
            </el-col>
            <el-col :span="16">
              <el-row class="weekBtnList">
                <span class="weekBtn" @click="changeData('now')">本周</span>
                <span class="weekBtn" @click="changeData('prev')">上一周</span>
                <span class="weekBtn" @click="changeData('next')">下一周</span>
              </el-row>
            </el-col>
          </el-row>
          <el-row class="scheduleContent">
            <el-row class="scheduleContent_body">
              <table cellspacing="0" cellpadding="0" style="width: 100%;">
                <thead class="scheduleContent_head">
                <tr>
                  <td><span class="weekTime">节/周</span></td>
                  <td v-for="week in weekList" :key="week.date">
                    <p class="dateTime">{{week.date}}</p>
                    <p class="weekTime">{{week.week}}</p>
                  </td>
                </tr>
                </thead>
                <tbody v-if="scheduleList.length!=0">
                <tr v-for="(schedule,index) in scheduleList"
                    :key="index" class="scheduleContent_body_row">
                  <td v-for="(data,ix) in schedule">
                    <div class="schedule_box" :class="{'schedule_box_act':data.checked}"
                         @click.prevent="chooseTurningClass('scheduleList',index,ix)">
                      <span class="idx_number" v-if="!data.pkListId">{{data.subjectName}}</span>
                      <span class="idx_number" v-if="data.pkListId&&data.statu==0">{{data.subjectName}}</span>
                      <span class="closeClass" v-if="data.checked"
                            @click.stop="closeTurningClass('scheduleList',index,ix)"><i
                        class="el-icon-close"></i></span>
                      <div class="schedule_subject_normal" v-if="data.pkListId&&data.statu==5">
                        <span v-if="data.checkd==1">{{data.subjectName}}</span>
                        <span style="color: #d2d2d2" v-if="data.checkd==0">{{data.subjectName}}</span>
                        <div class="tips" v-if="data.subjectName">
                          <h5>{{data.teacherName}}</h5>
                          <p>{{data.time}}</p>
                          <p>{{data.show}}</p>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
                </tbody>
              </table>
              <el-row class="noDataTips" v-if="scheduleList.length==0">暂无数据</el-row>
            </el-row>
          </el-row>
        </el-row>
      </el-col>
    </el-row>
    <el-dialog
      title="选择生效时间"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row>
        <el-form ref="form" :rules="formRule" :model="form" label-width="80px">
          <el-form-item label="时间段：" required>
            <el-col :span="11">
              <el-form-item prop="sxStartTime">
                <el-date-picker type="date"
                                :picker-options="pickerBeginDateBefore"
                                placeholder="选择日期" v-model="form.sxStartTime"
                                style="width: 100%;"></el-date-picker>
              </el-form-item>
            </el-col>
            <el-col class="line" :span="2">-</el-col>
            <el-col :span="11">
              <el-form-item prop="sxEndTime">
                <el-date-picker type="date" placeholder="选择时间" v-model="form.sxEndTime"
                                :picker-options="pickerBeginDateAfter"
                                style="width: 100%;"></el-date-picker>
              </el-form-item>
            </el-col>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="save('time')">确 定</el-button>
    <el-button @click="dialogVisible = false">取 消</el-button>
  </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import moment from 'moment'

  export default {
    data() {
      return {
        treeData: [],
        defaultProps: {
          children: 'data',
          label: 'techerName'
        },
        filterText: '',
        weekList: [],
        scheduleList: [],
        teacherIdL: '',
        userInfoR: {},
        currentFirstDate: '',
        form: {
          sxStartTime: '',
          sxEndTime: ''
        },
        dialogVisible: false,
        submitData: {
          data: {},
          startTime: '',
          endTime: '',
          sxStartTime: '',
          sxEndTime: ''
        },
        pickerBeginDateBefore: {
          disabledDate: (time) => {
            let beginDateVal = this.form.sxEndTime;
            if (beginDateVal) {
              return time.getTime() > beginDateVal;
            }
          }
        },
        pickerBeginDateAfter: {
          disabledDate: (time) => {
            let beginDateVal = this.form.sxStartTime;
            if (beginDateVal) {
              return time.getTime() < beginDateVal;
            }
          }
        },
        formRule: {
          sxStartTime: [
            {type: 'date', required: true, message: '请选择日期', trigger: 'change'}
          ],
          sxEndTime: [
            {type: 'date', required: true, message: '请选择日期', trigger: 'change'}
          ]
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
      self.weekList = self.setDate(self.addDate(new Date(), 0));
      //得到待选教师
      self.loading = true;
      req.ajaxSend('/school/classreplacement/dKapprover?type=getTeacher', 'get', '', function (res) {
        self.treeData = res.data;
        self.loading = false;
      })
    },
    methods: {
      filterNode(value, data) {
        if (!value) return true;
        if (data.techerName) {
          data.techerName = data.techerName.toString();
          return data.techerName.indexOf(value) !== -1;
        }
      },
      chooseTeacher(node) {  //查询教师课表
        if (!node.data) {
          var data = {
            techerId: node.techerId,
            startTime: this.weekList[0].date,
            endTime: this.weekList[6].date
          };
          this.teacherIdL = node.techerId;
          this.userInfoR = node;
          this.getRightTeacherSche(data);
        }
      },
      getRightTeacherSche(data) {  //得到右边教师课表
        var self = this;
        req.ajaxSend('/school/classreplacement/dKapprover?type=getTeacherTable', 'get', data, function (res) {
          for (let obj of res.data) {
            for (let mbj of obj) {
              mbj.checked = false;
            }
          }
          self.scheduleList = res.data;
        })
      },
      chooseTurningClass(data, ix1, ix2) {   //选择调课课程
        if (this[data][ix1][ix2].statu != 5 || this[data][ix1][ix2].checkd == 0) {
          return false;
        }
        this.submitData.data = {};
        if (!this[data][ix1][ix2].checked) {
          for (let obj of this[data]) {
            for (let mbj of obj) {
              mbj.checked = false;
            }
          }
          this.submitData.data = this[data][ix1][ix2];
          this[data][ix1][ix2].checked = true;
        }
      },
      closeTurningClass(data, ix1, ix2) {   //取消调课课程
        this[data][ix1][ix2].checked = false;
        this.submitData.data = {};
      },
      chooseTime() {  //选择生效时间
        this.dialogVisible = true;
      },
      handleClose(done) {
        done();
      },
      changeData(type) {
        var data = {
          techerId: this.teacherIdL,
          startTime: this.weekList[0].date,
          endTime: this.weekList[6].date
        };
        if (type == 'now') { //本周
          this.weekList = this.setDate(this.addDate(new Date(), 0));
        } else if (type == 'prev') {  //上周
          this.weekList = this.setDate(this.addDate(this.currentFirstDate, -7));
        } else {   //下周
          this.weekList = this.setDate(this.addDate(this.currentFirstDate, 7));
        }
        this.getRightTeacherSche(data);
      },
      save(type) {
        var self = this, data;
        if (type == 'clear') {  //清空
          self.scheduleList = [];
          this.submitData.data = {};
        } else if (type == 'time') {  //选择有效时间
          self.$refs['form'].validate((valid) => {
            if (valid) {
              self.dialogVisible = false;
              self.submitData.sxStartTime = moment(self.form.sxStartTime).format('YYYY-MM-DD');
              self.submitData.sxEndTime = moment(self.form.sxEndTime).format('YYYY-MM-DD');
            } else {
              return false;
            }
          });
        } else {  //提交申请
          self.submitData.startTime = self.weekList[0].date;
          self.submitData.endTime = self.weekList[6].date;
          if (!self.submitData.data.subjectId) {
            self.vmMsgWarning('您还未选择代课课程！');
            return false
          }
          data = {
            techerId: this.teacherIdL,
            startTime: this.weekList[0].date,
            endTime: this.weekList[6].date
          };
          req.ajaxSend('/school/classreplacement/dKapprover?type=submit', 'get', self.submitData, function (res) {
            if (res.statu == 1) {
              self.vmMsgSuccess('申请代课成功！');
              self.getRightTeacherSche(data);
            } else {
              self.vmMsgError(res.message);
            }
          })
        }
      },
      addDate(date, n) {
        date.setDate(date.getDate() + n);
        return date;
      },
      setDate(date) {
        var clen = 7;
        var week = date.getDay() - 1, weekAry = [];
        date = this.addDate(date, week * -1);
        this.currentFirstDate = new Date(date);
        //格式化日期
        var formatDate = function (date) {
          var year = date.getFullYear() + '-';
          var month = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
          var day = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
          var week = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'][date.getDay()];
          return {
            date: year + month + day,
            week: week
          };
        };
        for (var i = 0; i < clen; i++) {
          var nn = formatDate(i == 0 ? date : this.addDate(date, 1));
          weekAry.push(nn);
        }
        return weekAry;
      }
    }
  }
</script>
<style>
  .substituteApplication {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .substituteApplication h3 {
    font-size: 1.25rem;
  }

  .substituteApplication .substituteApplication_row {
    margin-top: 2rem;
  }

  .substituteApplication h4 {
    font-size: 1.25rem;
    font-weight: normal;
    padding: .875rem 1rem;
    border-bottom: 1px solid #d2d2d2;
  }

  .substituteApplication .operationBtn {
    text-align: right;
  }

  .substituteApplication .operationBtn .el-button {
    padding: 10px 2.625rem;
    border-radius: 20px;
  }

  .substituteApplication .operationBtn .el-button.clearBtn {
    background: #ff8686;
    border-color: #ff8686;
  }

  .substituteApplication .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
  }

  .substituteApplication .treeList_body {
    padding: .875rem;
    height: 45rem;
    overflow: auto;
  }

  .substituteApplication .treeList_title {
    padding: .875rem;
  }

  .substituteApplication .treeList_title h5 {
    font-size: 1rem;
  }

  .substituteApplication .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .substituteApplication .treeList .el-tree {
    border: none;
  }

  .substituteApplication .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .substituteApplication .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  .substituteApplication .specifiedTurning_body {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
  }

  .substituteApplication .scheduleSearch {
    padding: .5rem 1rem;
  }

  .substituteApplication .weekBtnList {
    text-align: right;
    font-size: 14px;
  }

  .substituteApplication .weekBtn {
    padding: 0 .5rem;
    color: #4da1ff;
    cursor: pointer;
  }

  .substituteApplication .weekBtn + .weekBtn {
    border-left: 2px solid #d2d2d2;
  }

  .substituteApplication .scheduleContent_body td {
    width: 12.5%;
    text-align: center;
    border-bottom: 1px solid #d2d2d2;
  }

  .substituteApplication .scheduleContent_head {
    background-color: #89bcf5;
    color: #fff;
  }

  .substituteApplication .dateTime {
    font-size: 12px;
  }

  .substituteApplication .weekTime {
    font-size: 14px;
  }

  .substituteApplication .scheduleContent_head td {
    font-size: 14px;
    padding: .6rem 0;
  }

  .substituteApplication td + td {
    border-left: 1px solid #d2d2d2;
  }

  .substituteApplication .scheduleContent_body .scheduleContent_body_row + .scheduleContent_body_row {
    border-top: 1px solid #d2d2d2;
  }

  .substituteApplication .scheduleContent_body .schedule_box {
    padding: 2.25rem 0;
    position: relative;
  }

  .substituteApplication .closeClass {
    position: absolute;
    right: .5rem;
    top: .5rem;
    font-size: 12px;
    z-index: 1;
    color: #13b5b1;
    cursor: pointer;
  }

  .substituteApplication .scheduleContent_body .schedule_box.schedule_box_act {
    border: 1px solid #13b5b1;
    color: #13b5b1;
  }

  .substituteApplication .scheduleContent_body .idx_number {
    font-size: .875rem;
  }

  .substituteApplication .scheduleContent_body .schedule_subject_normal {
    font-size: 1.125rem;
    font-weight: bold;
  }

  .substituteApplication .schedule_subject_normal .tips {
    position: absolute;
    bottom: -100%;
    left: 50%;
    -webkit-transform: translateX(-50%);
    -moz-transform: translateX(-50%);
    -ms-transform: translateX(-50%);
    -o-transform: translateX(-50%);
    transform: translateX(-50%);
    width: 8rem;
    padding: 1rem;
    font-weight: normal;
    font-size: .875rem;
    border: 1px solid #d2d2d2;
    background-color: #fff;
    -webkit-box-shadow: 0 0 30px -5px #d2d2d2;
    -moz-box-shadow: 0 0 30px -5px #d2d2d2;
    box-shadow: 0 0 30px -5px #d2d2d2;
    z-index: 1;
    border-radius: 4px;
    text-align: left;
    display: none;
    color: #2c3e50;
  }

  .substituteApplication .schedule_subject_normal .tips p {
    margin: .5rem 0;
  }

  .substituteApplication .schedule_box:hover .tips {
    display: block;
  }

  .substituteApplication .noDataTips {
    text-align: center;
    padding: 1rem 0;
    font-size: .875rem;
  }

  .substituteApplication .line {
    text-align: center;
  }

  .substituteApplication .el-dialog--small {
    width: 600px;
  }
</style>
