<template>
  <div class="classTeacherManagement">
    <h3>任课教师管理</h3>
    <el-row :gutter="60">
      <el-col :span="10">
        <el-row class="classTeacher_row">
          <el-form :inline="true">
            <el-form-item label="年级：">
              <el-select v-model="teacherLeftParam.gradeId" placeholder="请选择" class="grade">
                <el-option
                  v-for="item in gradeList"
                  :key="item.gradeid"
                  :label="item.znName"
                  :value="item.gradeid">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="科目：">
              <el-select v-model="teacherLeftParam.subjectId" placeholder="请选择" class="grade">
                <el-option
                  v-for="item in subjectList"
                  :key="item.subjectid"
                  :label="item.subjectname"
                  :value="item.subjectid">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" icon="el-icon-search" class="search" @click="search">查询</el-button>
            </el-form-item>
          </el-form>
        </el-row>
        <el-row>
          <el-row class="alertsList">
            <el-table
              :data="teacherLeftTable"
              max-height="650"
              style="width: 100%"
            >
              <el-table-column
                prop="className"
                label="年级/班级">
              </el-table-column>
              <el-table-column
                label="任课教师">
                <template slot-scope="scope">
                  <div class="teacherName" :class="{'edit':scope.row.checked}" @click="edit(scope.$index)">
                    {{scope.row.techerName||'- -'}}
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="14">
        <el-row type="flex" align="middle" class="classTeacher_row teacherTip">
          <el-button type="primary" class="teacherTitle" @click="openList">任课教师一览表</el-button>
          <span style="margin-left: 1rem;">操作方式：先点击须填入任课教师的单元格，再选择右侧表格中的教师姓名。</span>
        </el-row>
        <el-row type="flex" align="middle" class="teacherHeader">
          <el-col :span="10">
            <span class="showTips">教师一览表</span>
          </el-col>
          <el-col :span="14">
            <div class="g-fuzzyInput">
              <el-input
                placeholder="请输入查询关键字"
                suffix-icon="el-icon-search"
                v-model="selectParam.valueData"
                @change="goSearch">
              </el-input>
            </div>
          </el-col>
        </el-row>
        <el-row class="teacherList">
          <el-table
            :data="tableData"
            max-height="600"
            style="width: 100%"
            border
            @sort-change="sort"
            v-loading="loading"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              type="index"
              width="120"
              label="序号">
            </el-table-column>
            <el-table-column
              prop="jobNumber"
              label="工号" sortable="custom">
            </el-table-column>
            <el-table-column
              prop="name"
              label="姓名" sortable="custom">
              <template slot-scope="scope">
                <span class="setName" @click="setTeacherName(scope.$index)">{{scope.row.name}}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="teachingSubjects"
              label="科目" sortable="custom">
            </el-table-column>
            <el-table-column
              prop="phone"
              label="联系电话" sortable="custom">
            </el-table-column>
          </el-table>
        </el-row>
      </el-col>
    </el-row>
    <el-row class="createBtn">
      <el-button @click="clear">清空</el-button>
      <el-button type="primary" @click="save">保存</el-button>
    </el-row>
    <el-dialog
      title="任课教师一览表"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row>
        <el-row type="flex" align="middle" class="alertsBtn">
          <el-col :span="16">
            <el-button class="delete" title="导出" @click="operationTeacher('out')">
              <img class="delete_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
                   alt="">
              <img class="delete_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
                   alt="">
            </el-button>
            <el-button-group class="secBtn-group" @click="operationTeacher('copy')">
              <el-button class="filt" title="复制">
                <img class="filt_unactive"
                     src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                     alt="">
                <img class="filt_active"
                     src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                     alt="">
              </el-button>
              <el-button class="delete" title="打印" @click="operationTeacher('print')">
                <img class="delete_unactive"
                     src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                     alt="">
                <img class="delete_active"
                     src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                     alt="">
              </el-button>
            </el-button-group>
          </el-col>
          <el-col :span="8" class="dialogGradeBox">
            <span>年级：</span>
            <el-select v-model="teacherAllParam.gradeId" placeholder="请选择" class="dialogGrade"
                       @change="selectAllTeacher">
              <el-option
                v-for="item in gradeList"
                :key="item.gradeid"
                :label="item.znName"
                :value="item.gradeid">
              </el-option>
            </el-select>
          </el-col>
        </el-row>
        <el-row class="teacherList">
          <el-table
            :data="teacherAllData.data"
            max-height="400"
            border
            style="width: 100%"
            v-loading="loading1"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              prop="className"
              label="名称">
            </el-table-column>
            <el-table-column
              prop="techerName"
              label="班主任">
            </el-table-column>
            <el-table-column
              :prop="teacherData.subjectname"
              :label="teacherData.subjectname" v-for="(teacherData,idx) in teacherAllData.suject" :key="idx">
            </el-table-column>
          </el-table>
        </el-row>
      </el-row>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        gradeList: [],
        subjectList: [],
        teacherLeftTable: [],
        tableData: [],
        teacherAllData: [],
        teacherLeftParam: {
          gradeId: '',
          subjectId: ''
        },
        teacherLeftParamAct: {
          gradeId: '',
          subjectId: ''
        },
        selectParam: {
          type: 'getTeacherList',
          sort: '',
          sortType: '',
          valueData: ''
        },
        teacherAllParam: {
          gradeId: ''
        },
        dialogVisible: false,
        actIdx: '',
        loading: false,
        loading1: false
      }
    },
    created: function () {
      var self = this;
      req.ajaxSend('/school/Educational/getSubjectList?type=getGradeList', 'get', '', function (res) {
        self.gradeList = res.data;
      });
      req.ajaxSend('/school/Educational/getSubjectList?type=getSubjectList', 'get', '', function (res) {
        self.subjectList = res.data;
      });
      self.loadData(self.selectParam);
    },
    methods: {
      search(){  //查询任课教师
        var self = this;
        self.teacherLeftParamAct.gradeId = self.teacherLeftParam.gradeId;
        self.teacherLeftParamAct.subjectId = self.teacherLeftParam.subjectId;
        if (!self.teacherLeftParamAct.gradeId || !self.teacherLeftParamAct.subjectId) {
          self.vmMsgWarning('请选择年级或科目！');
          return false;
        }
        req.ajaxSend('/school/Educational/teacherSubject?type=getTeacherSubject', 'get', self.teacherLeftParam, function (res) {
          for (let obj of res.data) {
            obj.checked = false;
          }
          self.teacherLeftTable = res.data;
        })
      },
      goSearch() {  //查询
        this.selectParam.sort = '';
        this.selectParam.sortType = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.sort = column.order || '';
        this.selectParam.sortType = column.prop || '';
        this.loadData(this.selectParam);
      },
      openList(){
        this.dialogVisible = true;
      },
      handleClose(done) {
        done();
      },
      edit(idx){
        for (let obj of this.teacherLeftTable) {
          obj.checked = false;
        }
        this.teacherLeftTable[idx].checked = true;
        this.actIdx = idx;
      },
      setTeacherName(idx){
        if (typeof this.actIdx != 'string') {
          this.teacherLeftTable[this.actIdx].techerName = this.tableData[idx].name;
          this.teacherLeftTable[this.actIdx].techerId = this.tableData[idx].id;
        }
      },
      selectAllTeacher(){  //查询任课教师一览表
        var self = this;
        self.dialogVisible = true;
        self.loading1 = true;
        req.ajaxSend('/school/Educational/teacherSubject?type=teacherList', 'get', self.teacherAllParam, function (res) {
          self.teacherAllData = res;
          self.loading1 = false;
        })
      },
      operationTeacher(type){
        if (!this.teacherAllParam.gradeId) {
          this.vmMsgWarning('请选择年级！');
          return false;
        }
        let sAy = [], hdData = {
          className: '名称',
          techerName: '班主任'
        };
        for (let obj of this.teacherAllData.suject) {
          hdData[obj.subjectname] = obj.subjectname;
        }
        sAy.push(hdData);
        for (let obj of this.teacherAllData.data) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'out') {
          req.downloadFile('.classTeacherManagement', '/school/educational/teacherSubject?type=export&gradeId=' + this.teacherAllParam.gradeId, 'post')
        } else if (type == 'copy') {
          req.copyTableData('.classTeacherManagement', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      clear(){
        var self = this;
        if (self.teacherLeftTable.length == 0) {
          self.vmMsgWarning('没有可清空的数据！');
          return false;
        }
        self.$confirm('可能有数据还未保存，确定清空数据?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Educational/teacherSubject?type=clearsTask', 'post', self.teacherLeftParamAct, function (res) {
            if (res.statu == 1) {
              self.vmMsgSuccess('清空成功！');
              for (let obj of self.teacherLeftTable) {
                obj.techerName = '';
                obj.techerId = '';
              }
            } else {
              self.vmMsgError(res.message);
            }
          })
        }).catch(() => {
        });
      },
      save(){
        var self = this, data = {
          gradeId: self.teacherLeftParamAct.gradeId,
          subjectId: self.teacherLeftParamAct.subjectId,
          data: []
        };
        for (let obj of self.teacherLeftTable) {
          let person = {
            className: obj.className,
            techerId: obj.techerId,
            techerName: obj.techerName,
            classId: obj.classId,
          };
          data.data.push(person);
        }
        req.ajaxSend('/school/Educational/teacherSubject?type=createTeacherSubject', 'post', data, function (res) {
          if (res.statu == 1) {
            self.vmMsgSuccess('保存成功！');
          } else {
            self.vmMsgError(res.message);
          }
        })
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Educational/getSubjectList', 'get', data, function (res) {
          self.tableData = res.data;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .classTeacherManagement .createBtn {
    margin-top: 2rem;
  }

  .classTeacherManagement .showTips {
    color: #fff;
  }
</style>
