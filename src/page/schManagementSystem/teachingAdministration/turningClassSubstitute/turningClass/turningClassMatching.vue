<template>
  <div class="turningClassMatching">
    <el-row type="flex" align="middle">
      <el-col :span="12">
        <h3>教师非指定调课</h3>
        <span class="breadcrumb"><router-link to="/unSpecifiedTurningClass"
                                              tag="span">调课申请</router-link><span>|</span><span
          class="breadcrumb_active">调课匹配</span></span>
      </el-col>
      <el-col :span="12">
        <el-row type="flex" justify="end">
          <el-button type="primary" class="submittedBtn" @click="save">提交申请</el-button>
        </el-row>
      </el-col>
    </el-row>
    <el-row :gutter="20" class="turningClassMatching_row">
      <el-col :span="9">
        <el-row class="specifiedTurning_body">
          <el-row type="flex" align="middle" class="scheduleSearch">
            <el-col :span="14">
              <el-row :gutter="10" type="flex" align="middle">
                <el-col :span="8">
                  <span class="scheduleSearch_txt">{{userInfo.name}}--课表</span>
                </el-col>
                <el-col :span="8">
                  <el-select v-model="selectParam.gradeId" style="width: 100%;" placeholder="请选择" @change="chooseClass">
                    <el-option
                      v-for="item in gradeList"
                      :key="item.gradeId"
                      :label="item.gradeName"
                      :value="item.gradeId">
                    </el-option>
                  </el-select>
                </el-col>
                <el-col :span="8">
                  <el-select v-model="selectParam.classIdOne" style="width: 100%;" placeholder="请选择"
                             @change="getCurrTeacherSche">
                    <el-option
                      v-for="item in classList"
                      :key="item.classId"
                      :label="item.className"
                      :value="item.classId">
                    </el-option>
                  </el-select>
                </el-col>
              </el-row>
            </el-col>
            <el-col :span="10">
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
            <el-col :span="10">
              <el-row :gutter="20" type="flex" align="middle">
                <el-col :span="12">
                  <span class="scheduleSearch_txt">{{userInfoR.techerName}}--课表</span>
                </el-col>
                <el-col :span="12">
                  <el-select v-model="selectParam.classIdTow" style="width: 100%;" placeholder="请选择"
                             @change="selectTeacherL">
                    <el-option
                      v-for="item in classList"
                      :key="item.classId"
                      :label="item.className"
                      :value="item.classId">
                    </el-option>
                  </el-select>
                </el-col>
              </el-row>
            </el-col>
            <el-col :span="14">
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
                      <div class="schedule_subject_normal" v-if="data.pkListId&&data.statu==6">
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
              <h5>待选教师</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入查询姓名"
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
        userInfo: {},
        userInfoR: {},
        gradeList: [],
        classList: [],
        treeData: [],
        teacherIdL: '',
        defaultProps: {
          children: 'data',
          label: 'techerName'
        },
        selectParam: {
          gradeId: '',
          classIdOne: '',
          classIdTow: '',
          subjectId: []
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
      //查询年级
      req.ajaxSend('/school/Classreplacement/teacherTk?type=getGrade', 'get', '', function (res) {
        self.gradeList = res.data;
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
      chooseClass() {
        var self = this, data = {
          gradeId: self.selectParam.gradeId
        };
        self.selectParam.classIdOne = '';
        self.selectParam.classIdTow = '';
        req.ajaxSend('/school/Classreplacement/teacherTk?type=getClass', 'get', data, function (res) {
          self.classList = res.data;
        })
      },
      getCurrTeacherSche() {  //得到左边当前教师课表
        var self = this, data = {
          techerId: '',
          gradeId: self.selectParam.gradeId,
          classId: self.selectParam.classIdOne,
          startTime: self.weekListL[0].date,
          endTime: self.weekListL[6].date
        };
        req.ajaxSend('/school/Classreplacement/teacherTk?type=teacherZd', 'get', data, function (res) {
          for (let obj of res.data) {
            for (let mbj of obj) {
              mbj.checked = false;
            }
          }
          self.scheduleListL = res.data;
          self.selectParam.subjectId = res.subjectId;
        })
      },
      selectTeacherL() {  //得到待选教师列表
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Classreplacement/teacherTk?type=getTeacher', 'get', self.selectParam, function (res) {
          self.treeData = res.data;
          self.loading = false;
        })
      },
      chooseTeacher(node, event) {  //查询右边教师课表
        if (!node.data) {
          var data = {
            techerId: node.techerId,
            gradeId: this.selectParam.gradeId,
            classId: this.selectParam.classIdTow,
            startTime: this.weekListR[0].date,
            endTime: this.weekListR[6].date,
            zdOrNo: 1
          };
          this.teacherIdL = node.techerId;
          this.userInfoR = node;
          this.getRightTeacherSche(data);
        }
      },
      getRightTeacherSche(data) {  //得到右边教师课表
        var self = this;
        req.ajaxSend('/school/Classreplacement/teacherTk?type=teacherZd', 'get', data, function (res) {
          for (let obj of res.data) {
            for (let mbj of obj) {
              mbj.checked = false;
            }
          }
          self.scheduleListR = res.data;
        })
      },
      chooseTurningClass(data, ix1, ix2) {   //选择调课课程
        if ((this[data][ix1][ix2].statu != 5 && this[data][ix1][ix2].statu != 6) || this[data][ix1][ix2].checkd == 0) {
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
        var tData;
        if (type == 'now') { //本周
          this[data] = this.setDate(this.addDate(new Date(), 0));
        } else if (type == 'prev') {  //上周
          this[data] = this.setDate(this.addDate(this.currentFirstDate, -7));
        } else {   //下周
          this[data] = this.setDate(this.addDate(this.currentFirstDate, 7));
        }
        if (data == 'weekListL') {
          this.getCurrTeacherSche();
        } else {
          tData = {
            techerId: this.teacherIdL,
            gradeId: this.selectParam.gradeId,
            classId: this.selectParam.classIdTow,
            startTime: this.weekListR[0].date,
            endTime: this.weekListR[6].date,
            zdOrNo: 1
          };
          this.getRightTeacherSche(tData);
        }
      },
      save() {
        var self = this, data = self.submitData.scheduleListL.concat(self.submitData.scheduleListR), tData = {
          data: data
        };
        if (data.length < 2) {
          self.vmMsgWarning('您还未选择调课课程!');
          return false;
        }
        req.ajaxSend('/school/Classreplacement/teacherTk?type=startTk', 'post', tData, function (res) {
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
  .turningClassMatching {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .turningClassMatching .breadcrumb {
    font-size: 18px;
  }

  .turningClassMatching .breadcrumb > span {
    margin-left: 2rem;
    color: #999999;
    cursor: pointer;
  }

  .turningClassMatching .breadcrumb .breadcrumb_active {
    color: #4da1ff;
  }

  .turningClassMatching h3 {
    font-size: 1.25rem;
    display: inline-block;
  }

  .turningClassMatching .turningClassMatching_row {
    margin-top: 2rem;
  }

  .turningClassMatching .submittedBtn {
    border-radius: 20px;
    padding: 10px 2rem;
  }

  .turningClassMatching .specifiedTurning_body {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
  }

  .turningClassMatching .scheduleSearch {
    padding: .5rem 1rem;
  }

  .turningClassMatching .scheduleSearch_txt {
    margin-right: 2rem;
  }

  .turningClassMatching .specifiedTurning_body .el-select {
    width: 6.25rem;
  }

  .turningClassMatching .specifiedTurning_body .el-select + .el-select {
    margin-left: 1rem;
  }

  .turningClassMatching .weekBtnList {
    text-align: right;
    font-size: .875rem;
  }

  .turningClassMatching .weekBtn {
    padding: 0 .5rem;
    color: #4da1ff;
    cursor: pointer;
  }

  .turningClassMatching .weekBtn + .weekBtn {
    border-left: 2px solid #d2d2d2;
  }

  .turningClassMatching .scheduleContent_body td {
    width: 12.5%;
    text-align: center;
    border-bottom: 1px solid #d2d2d2;
  }

  .turningClassMatching .scheduleContent_head {
    background-color: #89bcf5;
    color: #fff;
  }

  .turningClassMatching .dateTime {
    font-size: 12px;
  }

  .turningClassMatching .weekTime {
    font-size: 14px;
  }

  .turningClassMatching .scheduleContent_head td {
    font-size: 14px;
    padding: .6rem 0;
  }

  .turningClassMatching td + td {
    border-left: 1px solid #d2d2d2;
  }

  .turningClassMatching .scheduleContent_body .scheduleContent_body_row + .scheduleContent_body_row {
    border-top: 1px solid #d2d2d2;
  }

  .turningClassMatching .scheduleContent_body .schedule_box {
    padding: 2.25rem 0;
    position: relative;
  }

  .turningClassMatching .closeClass {
    position: absolute;
    right: .5rem;
    top: .5rem;
    font-size: 12px;
    z-index: 1;
    color: #13b5b1;
    cursor: pointer;
  }

  .turningClassMatching .scheduleContent_body .schedule_box.schedule_box_act {
    border: 1px solid #13b5b1;
    color: #13b5b1;
  }

  .turningClassMatching .scheduleContent_body .idx_number {
    font-size: .875rem;
  }

  .turningClassMatching .scheduleContent_body .schedule_subject_normal {
    font-size: 1.125rem;
    font-weight: bold;
  }

  .turningClassMatching .schedule_subject_normal .tips {
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

  .turningClassMatching .schedule_subject_normal .tips p {
    margin: .5rem 0;
  }

  .turningClassMatching .schedule_box:hover .tips {
    display: block;
  }

  .turningClassMatching .noDataTips {
    text-align: center;
    padding: 1rem 0;
    font-size: .875rem;
  }

  .turningClassMatching .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
  }

  .turningClassMatching .treeList_body {
    padding: .875rem;
    height: 45rem;
    overflow: auto;
  }

  .turningClassMatching .treeList_title {
    padding: .875rem;
  }

  .turningClassMatching .treeList_title h5 {
    font-size: 1rem;
  }

  .turningClassMatching .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .turningClassMatching .treeList .el-tree {
    border: none;
  }

  .turningClassMatching .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .turningClassMatching .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

</style>
