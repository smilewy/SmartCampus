<template>
  <div class="classTurningClass">
    <el-row type="flex" align="middle">
      <el-col :span="12">
        <h3>班级调课</h3>
      </el-col>
      <el-col :span="12">
        <el-row type="flex" justify="end">
          <el-button type="primary" class="submittedBtn" @click="save">提交申请</el-button>
        </el-row>
      </el-col>
    </el-row>
    <el-row :gutter="20" class="classTurningClass_row">
      <el-col :span="9">
        <el-row class="specifiedTurning_body">
          <el-row type="flex" align="middle" class="scheduleSearch">
            <el-col :span="8">
              <span class="scheduleSearch_txt">{{classInfo.gradeName}} {{classInfo.classname}}--课表</span>
            </el-col>
            <el-col :span="16">
              <el-row class="weekBtnList">
                <span class="weekBtn" @click="changeData('now','weekListL')">本周</span>
                <span class="weekBtn" @click="changeData('prev','weekListL')">上一周</span>
                <span class="weekBtn" @click="changeData('next','weekListL')">下一周</span>
              </el-row>
            </el-col>
          </el-row>
          <el-row class="scheduleContent">
            <el-row class="scheduleContent_body">
              <table cellspacing="0" cellpadding="0" style="width: 100%;">
                <thead class="scheduleContent_head">
                <tr>
                  <td><span class="weekTime">节/周</span></td>
                  <td v-for="week in weekListL" :key="week.date">
                    <p class="dateTime">{{week.date}}</p>
                    <p class="weekTime">{{week.week}}</p>
                  </td>
                </tr>
                </thead>
                <tbody v-if="scheduleListL.length!=0">
                <tr v-for="(schedule,index) in scheduleListL"
                    :key="index" class="scheduleContent_body_row">
                  <td v-for="(data,ix) in schedule">
                    <div class="schedule_box" :class="{'schedule_box_act':data.checked}"
                         @click.prevent="chooseTurningClass('scheduleListL',index,ix)">
                      <span class="idx_number" v-if="!data.pkListId">{{data.subjectName}}</span>
                      <span class="idx_number" v-if="data.pkListId&&data.statu==0">{{data.subjectName}}</span>
                      <span class="closeClass" v-if="data.checked"
                            @click.stop="closeTurningClass('scheduleListL',index,ix)"><i
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
              <el-row class="noDataTips" v-if="scheduleListL.length==0">暂无数据</el-row>
            </el-row>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="9">
        <el-row class="specifiedTurning_body">
          <el-row type="flex" align="middle" class="scheduleSearch">
            <el-col :span="8">
              <span class="scheduleSearch_txt">{{classInfo.gradeName}} {{classInfo.classname}}--课表</span>
            </el-col>
            <el-col :span="16">
              <el-row class="weekBtnList">
                <span class="weekBtn" @click="changeData('now','weekListR')">本周</span>
                <span class="weekBtn" @click="changeData('prev','weekListR')">上一周</span>
                <span class="weekBtn" @click="changeData('next','weekListR')">下一周</span>
              </el-row>
            </el-col>
          </el-row>
          <el-row class="scheduleContent">
            <el-row class="scheduleContent_body">
              <table cellspacing="0" cellpadding="0" style="width: 100%;">
                <thead class="scheduleContent_head">
                <tr>
                  <td><span class="weekTime">节/周</span></td>
                  <td v-for="week in weekListR" :key="week.date">
                    <p class="dateTime">{{week.date}}</p>
                    <p class="weekTime">{{week.week}}</p>
                  </td>
                </tr>
                </thead>
                <tbody v-if="scheduleListR.length!=0">
                <tr v-for="(schedule,index) in scheduleListR"
                    :key="index" class="scheduleContent_body_row">
                  <td v-for="(data,ix) in schedule">
                    <div class="schedule_box" :class="{'schedule_box_act':data.checked}"
                         @click.prevent="chooseTurningClass('scheduleListR',index,ix)">
                      <span class="idx_number" v-if="!data.pkListId">{{data.subjectName}}</span>
                      <span class="idx_number" v-if="data.pkListId&&data.statu==0">{{data.subjectName}}</span>
                      <span class="closeClass" v-if="data.checked"
                            @click.stop="closeTurningClass('scheduleListR',index,ix)"><i
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
              <el-row class="noDataTips" v-if="scheduleListR.length==0">暂无数据</el-row>
            </el-row>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="6">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>待调课班级</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入查询关键字"
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
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        classInfo: {},
        treeData: [],
        defaultProps: {
          children: 'data',
          label: 'classname'
        },
        selectParam: {
          grade: '',
          classid: ''
        },
        weekListL: [],
        weekListR: [],
        scheduleListL: [],
        scheduleListR: [],
        submitData: {
          scheduleListL: [],
          scheduleListR: []
        },
        currentFirstDate: '',
        filterText: '',
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
      self.weekListL = self.setDate(this.addDate(new Date(), 0));
      self.weekListR = self.setDate(this.addDate(new Date(), 0));
      self.userInfo = self.$store.state.userInfo;
      //查询待调课班级
      self.loading = true;
      req.ajaxSend('/school/classreplacement/classTk?type=getGradeAndClass', 'get', '', function (res) {
        self.treeData = res.data;
        self.loading = false;
      })
    },
    methods: {
      filterNode(value, data) {
        if (!value) return true;
        if (data.classname) {
          data.classname = data.classname.toString();
          return data.classname.indexOf(value) !== -1;
        }
      },
      chooseTeacher(node, event) {  //查询教师课表
        if (!node.data) {
          var data1 = {
            grade: node.grade,
            classid: node.classid,
            startTime: this.weekListL[0].date,
            endTime: this.weekListL[6].date
          }, data2 = {
            grade: node.grade,
            classid: node.classid,
            startTime: this.weekListR[0].date,
            endTime: this.weekListR[6].date
          };
          this.classInfo = node;
          this.selectParam.grade = node.grade;
          this.selectParam.classid = node.classid;
          this.getRightTeacherSche(data1, 'scheduleListL');
          this.getRightTeacherSche(data2, 'scheduleListR');
        }
      },
      chooseTurningClass(data, ix1, ix2) {   //选择调课课程
        if (this[data][ix1][ix2].statu != 5 || this[data][ix1][ix2].checkd == 0) {
          return false;
        }
        this.submitData[data] = [];
        if (!this[data][ix1][ix2].checked) {
          for (let obj of this[data]) {
            for (let mbj of obj) {
              mbj.checked = false;
            }
          }
          this.submitData[data].push(this[data][ix1][ix2]);
          this[data][ix1][ix2].checked = true;
        }
      },
      closeTurningClass(data, ix1, ix2) {   //取消调课课程
        this[data][ix1][ix2].checked = false;
        this.submitData[data] = [];
      },
      changeData(type, data) {
        var tData, name;
        if (!this.selectParam.grade) {
          this.vmMsgWarning('请先选择班级!');
          return false;
        }
        if (type == 'now') { //本周
          this[data] = this.setDate(this.addDate(new Date(), 0));
        } else if (type == 'prev') {  //上周
          this[data] = this.setDate(this.addDate(this.currentFirstDate, -7));
        } else {   //下周
          this[data] = this.setDate(this.addDate(this.currentFirstDate, 7));
        }
        if (data == 'weekListL') {
          name = 'scheduleListL';
        } else {
          name = 'scheduleListR';
        }
        tData = {
          grade: this.selectParam.grade,
          classid: this.selectParam.classid,
          startTime: this[data][0].date,
          endTime: this[data][6].date
        };
        this.getRightTeacherSche(tData, name);
      },
      save() {
        var self = this, data = self.submitData.scheduleListL.concat(self.submitData.scheduleListR), tData = {
          data: data
        };
        if (data.length < 2) {
          self.vmMsgWarning('您还未选择调课课程！');
          return false;
        }
        req.ajaxSend('/school/classreplacement/classTk?type=classCR', 'post', tData, function (res) {
          if (res.statu == 1) {
            self.vmMsgSuccess('调课申请成功!');
          } else {
            self.vmMsgError(res.message);
          }
        })
      },
      getRightTeacherSche(data, name) {  //得到教师课表
        var self = this;
        req.ajaxSend('/school/Classreplacement/classTk?type=getClassTable', 'get', data, function (res) {
          for (let obj of res.data) {
            for (let mbj of obj) {
              mbj.checked = false;
            }
          }
          self[name] = res.data;
        })
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
  .classTurningClass {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .classTurningClass h3 {
    font-size: 1.25rem;
    display: inline-block;
  }

  .classTurningClass .classTurningClass_row {
    margin-top: 2rem;
  }

  .classTurningClass .submittedBtn {
    border-radius: 20px;
    padding: 10px 2rem;
  }

  .classTurningClass .specifiedTurning_body {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
  }

  .classTurningClass .scheduleSearch {
    padding: .5rem 1rem;
  }

  .classTurningClass .scheduleSearch_txt {
    margin-right: 2rem;
  }

  .classTurningClass .weekBtnList {
    text-align: right;
    font-size: 14px;
  }

  .classTurningClass .weekBtn {
    padding: 0 .5rem;
    color: #4da1ff;
    cursor: pointer;
  }

  .classTurningClass .weekBtn + .weekBtn {
    border-left: 2px solid #d2d2d2;
  }

  .classTurningClass .scheduleContent_body td {
    width: 12.5%;
    text-align: center;
    border-bottom: 1px solid #d2d2d2;
  }

  .classTurningClass .scheduleContent_head {
    background-color: #89bcf5;
    color: #fff;
  }

  .classTurningClass .dateTime {
    font-size: 12px;
  }

  .classTurningClass .weekTime {
    font-size: 14px;
  }

  .classTurningClass .scheduleContent_head td {
    font-size: 14px;
    padding: .6rem 0;
  }

  .classTurningClass td + td {
    border-left: 1px solid #d2d2d2;
  }

  .classTurningClass .scheduleContent_body .scheduleContent_body_row + .scheduleContent_body_row {
    border-top: 1px solid #d2d2d2;
  }

  .classTurningClass .scheduleContent_body .schedule_box {
    padding: 2.25rem 0;
    position: relative;
  }

  .classTurningClass .closeClass {
    position: absolute;
    right: .5rem;
    top: .5rem;
    font-size: 12px;
    z-index: 1;
    color: #13b5b1;
    cursor: pointer;
  }

  .classTurningClass .scheduleContent_body .schedule_box.schedule_box_act {
    border: 1px solid #13b5b1;
    color: #13b5b1;
  }

  .classTurningClass .scheduleContent_body .idx_number {
    font-size: .875rem;
  }

  .classTurningClass .scheduleContent_body .schedule_subject_normal {
    font-size: 1.125rem;
    font-weight: bold;
  }

  .classTurningClass .schedule_subject_normal .tips {
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

  .classTurningClass .schedule_subject_normal .tips p {
    margin: .5rem 0;
  }

  .classTurningClass .schedule_box:hover .tips {
    display: block;
  }

  .classTurningClass .noDataTips {
    text-align: center;
    padding: 1rem 0;
    font-size: .875rem;
  }

  .classTurningClass .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
  }

  .classTurningClass .treeList_body {
    padding: .875rem;
    height: 45rem;
    overflow: auto;
  }

  .classTurningClass .treeList_title {
    padding: .875rem;
  }

  .classTurningClass .treeList_title h5 {
    font-size: 1rem;
  }

  .classTurningClass .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .classTurningClass .treeList .el-tree {
    border: none;
  }

  .classTurningClass .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .classTurningClass .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

</style>
