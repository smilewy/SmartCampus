<template>
  <div class="unSpecifiedTurningClass">
    <el-row type="flex" align="middle">
      <el-col :span="12">
        <h3>教师非指定调课</h3>
        <span class="breadcrumb"><span class="breadcrumb_active">调课申请</span><span>|</span><router-link
          to="/turningClassMatching"
          tag="span">调课匹配</router-link></span>
      </el-col>
      <el-col :span="12">
        <el-row type="flex" justify="end">
          <el-button type="primary" class="submittedBtn" @click="save">提交申请</el-button>
        </el-row>
      </el-col>
    </el-row>
    <el-row :gutter="20" class="unSpecifiedTurningClass_row">
      <el-col :span="14">
        <el-row class="specifiedTurning_body">
          <el-row type="flex" align="middle" class="scheduleSearch">
            <el-col :span="12">
              <span class="scheduleSearch_txt">{{userInfo.name}}--课表</span>
              <el-select v-model="selectParam.gradeId" placeholder="请选择" @change="chooseClass">
                <el-option
                  v-for="item in gradeList"
                  :key="item.gradeId"
                  :label="item.gradeName"
                  :value="item.gradeId">
                </el-option>
              </el-select>
              <el-select v-model="selectParam.classId" placeholder="请选择" @change="getCurrTeacherSche">
                <el-option
                  v-for="item in classList"
                  :key="item.classId"
                  :label="item.className"
                  :value="item.classId">
                </el-option>
              </el-select>
            </el-col>
            <el-col :span="12">
              <el-row class="weekBtnList">
                <span class="weekBtn" @click="changeData('now')">本周</span>
                <span class="weekBtn" @click="changeData('prev')">上一周</span>
                <span class="weekBtn" @click="changeData('next')">下一周</span>
              </el-row>
            </el-col>
          </el-row>
          <el-row class="scheduleContent">
            <el-row class="scheduleContent_body tableCustom">
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
                         @click.prevent="chooseTurningClass(index,ix)">
                      <span class="idx_number" v-if="!data.pkListId">{{data.subjectName}}</span>
                      <span class="idx_number" v-if="data.pkListId&&data.statu==0">{{data.subjectName}}</span>
                      <span class="closeClass" v-if="data.checked"
                            @click.stop="closeTurningClass(index,ix)"><i
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
      <el-col :span="10">
        <el-row class="specifiedTurning_body">
          <el-row class="turningSection">申请调课节次</el-row>
          <el-table
            :data="tableData"
            border
            style="width: 100%">
            <el-table-column
              prop="time"
              label="日期"
              min-width="150">
            </el-table-column>
            <el-table-column
              prop="className"
              label="班级"
              min-width="100">
            </el-table-column>
            <el-table-column
              min-width="100"
              label="节次">
              <template slot-scope="scope">第{{scope.row.x+1}}节</template>
            </el-table-column>
            <el-table-column
              prop="subjectName"
              min-width="100"
              label="科目">
            </el-table-column>
          </el-table>
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
        gradeList: [],
        classList: [],
        selectParam: {
          gradeId: '',
          classId: ''
        },
        userInfo: {},
        weekList: [],
        scheduleList: [],
        currentFirstDate: '',
        tableData: []
      }
    },
    created: function () {
      var self = this;
      self.weekList = self.setDate(self.addDate(new Date(), 0));
      self.userInfo = self.$store.state.userInfo;
      //查询年级
      req.ajaxSend('/school/Classreplacement/teacherTk?type=getGrade', 'get', '', function (res) {
        self.gradeList = res.data;
      })
    },
    methods: {
      changeData(type) {
        if (type == 'now') { //本周
          this.weekList = this.setDate(this.addDate(new Date(), 0));
        } else if (type == 'prev') {  //上周
          this.weekList = this.setDate(this.addDate(this.currentFirstDate, -7));
        } else {   //下周
          this.weekList = this.setDate(this.addDate(this.currentFirstDate, 7));
        }
        this.getCurrTeacherSche();
      },
      chooseClass() {
        var self = this, data = {
          gradeId: self.selectParam.gradeId
        };
        self.selectParam.classId = '';
        req.ajaxSend('/school/Classreplacement/teacherTk?type=getClass', 'get', data, function (res) {
          self.classList = res.data;
        })
      },
      getCurrTeacherSche() {  //得到当前教师课表
        var self = this, data = {
          techerId: '',
          gradeId: self.selectParam.gradeId,
          classId: self.selectParam.classId,
          startTime: self.weekList[0].date,
          endTime: self.weekList[6].date
        };
        req.ajaxSend('/school/Classreplacement/teacherTk?type=teacherZd', 'get', data, function (res) {
          for (let obj of res.data) {
            for (let mbj of obj) {
              mbj.checked = false;
            }
          }
          self.scheduleList = res.data;
        })
      },
      chooseTurningClass(ix1, ix2) {   //选择调课课程
        if (this.scheduleList[ix1][ix2].statu != 5 || this.scheduleList[ix1][ix2].checkd == 0) {
          return false;
        }
        this.tableData = [];
        if (!this.scheduleList[ix1][ix2].checked) {
          for (let obj of this.scheduleList) {
            for (let mbj of obj) {
              mbj.checked = false;
            }
          }
          this.tableData.push(this.scheduleList[ix1][ix2]);
          this.scheduleList[ix1][ix2].checked = true;
        }
      },
      closeTurningClass(ix1, ix2) {   //取消调课课程
        this.scheduleList[ix1][ix2].checked = false;
        this.tableData = [];
      },
      save() {
        var self = this, tData = {
          data: self.tableData[0]
        };
        if (self.tableData.length == 0) {
          self.vmMsgWarning('您还未选择调课课程!');
          return false;
        }
        req.ajaxSend('/school/Classreplacement/teacherTk?type=noZd', 'post', tData, function (res) {
          if (res.statu == 1) {
            self.vmMsgSuccess('调课申请成功!');
          } else {
            self.vmMsgError(res.message);
          }
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
  .unSpecifiedTurningClass {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .unSpecifiedTurningClass .breadcrumb {
    font-size: 18px;
  }

  .unSpecifiedTurningClass .breadcrumb > span {
    margin-left: 2rem;
    color: #999999;
    cursor: pointer;
  }

  .unSpecifiedTurningClass .breadcrumb .breadcrumb_active {
    color: #4da1ff;
  }

  .unSpecifiedTurningClass h3 {
    font-size: 1.25rem;
    display: inline-block;
  }

  .unSpecifiedTurningClass .unSpecifiedTurningClass_row {
    margin-top: 2rem;
  }

  .unSpecifiedTurningClass .submittedBtn {
    border-radius: 20px;
    padding: 10px 2rem;
  }

  .unSpecifiedTurningClass .specifiedTurning_body {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
  }

  .unSpecifiedTurningClass .scheduleSearch {
    padding: .5rem 1rem;
  }

  .unSpecifiedTurningClass .scheduleSearch_txt {
    margin-right: 2rem;
  }

  .unSpecifiedTurningClass .specifiedTurning_body .el-select {
    width: 90px;
  }

  .unSpecifiedTurningClass .specifiedTurning_body .el-select + .el-select {
    margin-left: 1rem;
  }

  .unSpecifiedTurningClass .weekBtnList {
    text-align: right;
    font-size: 14px;
  }

  .unSpecifiedTurningClass .weekBtn {
    padding: 0 .5rem;
    color: #4da1ff;
    cursor: pointer;
  }

  .unSpecifiedTurningClass .weekBtn + .weekBtn {
    border-left: 2px solid #d2d2d2;
  }

  .unSpecifiedTurningClass .tableCustom td {
    width: 12.5%;
    text-align: center;
    border-bottom: 1px solid #d2d2d2;
  }

  .unSpecifiedTurningClass .scheduleContent_head {
    background-color: #89bcf5;
    color: #fff;
  }

  .unSpecifiedTurningClass .dateTime {
    font-size: 12px;
  }

  .unSpecifiedTurningClass .weekTime {
    font-size: 14px;
  }

  .unSpecifiedTurningClass .scheduleContent_head td {
    font-size: 14px;
    padding: .6rem 0;
  }

  .unSpecifiedTurningClass .tableCustom td + td {
    border-left: 1px solid #d2d2d2;
  }

  .unSpecifiedTurningClass .scheduleContent_body .scheduleContent_body_row + .scheduleContent_body_row {
    border-top: 1px solid #d2d2d2;
  }

  .unSpecifiedTurningClass .scheduleContent_body .schedule_box {
    padding: 2.25rem 0;
    position: relative;
  }

  .unSpecifiedTurningClass .closeClass {
    position: absolute;
    right: .5rem;
    top: .5rem;
    font-size: 12px;
    z-index: 1;
    color: #13b5b1;
    cursor: pointer;
  }

  .unSpecifiedTurningClass .scheduleContent_body .schedule_box.schedule_box_act {
    border: 1px solid #13b5b1;
    color: #13b5b1;
  }

  .unSpecifiedTurningClass .scheduleContent_body .idx_number {
    font-size: .875rem;
  }

  .unSpecifiedTurningClass .scheduleContent_body .schedule_subject_normal {
    font-size: 1.125rem;
    font-weight: bold;
  }

  .unSpecifiedTurningClass .schedule_subject_normal .tips {
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

  .unSpecifiedTurningClass .schedule_subject_normal .tips p {
    margin: .5rem 0;
  }

  .unSpecifiedTurningClass .schedule_box:hover .tips {
    display: block;
  }

  .unSpecifiedTurningClass .noDataTips {
    text-align: center;
    padding: 1rem 0;
    font-size: .875rem;
  }

  .unSpecifiedTurningClass .el-table th {
    background-color: #deeefe;
    height: 3rem;
    text-align: center;
    font-size: .875rem;
  }

  .unSpecifiedTurningClass .el-table td {
    height: 3rem;
    font-size: .875rem;
    text-align: center;
    border-right: none;
  }

  .unSpecifiedTurningClass .el-table__footer-wrapper thead div, .unSpecifiedTurningClass .el-table__header-wrapper thead div {
    background-color: #deeefe;
  }

  .unSpecifiedTurningClass .turningSection {
    padding: 1rem;
  }
</style>
