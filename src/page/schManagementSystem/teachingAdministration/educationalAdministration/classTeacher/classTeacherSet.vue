<template>
  <div class="classTeacherSet">
    <h3>任课教师设置</h3>
    <el-row :gutter="60">
      <el-col :span="10">
        <el-row class="classTeacher_row">
          <el-form :inline="true">
            <el-form-item label="年级：">
              <el-select v-model="teacherLeftParam.gradeid" placeholder="请选择" class="grade" @change="selectClass">
                <el-option
                  v-for="item in gradeList"
                  :key="item.gradeid"
                  :label="item.znName"
                  :value="item.gradeid">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="班级：">
              <el-select v-model="teacherLeftParam.classid" placeholder="请选择" class="grade">
                <el-option
                  v-for="item in classList"
                  :key="item.classid"
                  :label="item.classname"
                  :value="item.classid">
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
                prop="subjectname"
                label="科目">
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
          操作方式：先点击须填入任课教师的单元格，再选择右侧表格中的教师姓名。
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
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        gradeList: [],
        classList: [],
        teacherLeftTable: [],
        tableData: [],
        teacherLeftParam: {
          gradeid: '',
          classid: ''
        },
        teacherLeftParamAct: {
          gradeid: '',
          classid: ''
        },
        selectParam: {
          type: 'getTeacherList',
          sort: '',
          sortType: '',
          valueData: ''
        },
        actIdx: '',
        loading:false
      }
    },
    created: function () {
      var self = this;
      req.ajaxSend('/school/Educational/getSubjectList?type=getGradeList', 'get', '', function (res) {
        self.gradeList = res.data;
      });
      self.loadData(self.selectParam);
    },
    methods: {
      selectClass(){
        var self = this, data = {
          gradeid: self.teacherLeftParam.gradeid
        };
        self.teacherLeftParam.classid = '';
        self.classList = [];
        req.ajaxSend('/school/Educational/teacherSubject?type=getClass', 'get', data, function (res) {
          self.classList = res.data;
        })
      },
      search(){  //查询任课教师
        var self = this;
        self.teacherLeftParamAct.gradeid = self.teacherLeftParam.gradeid;
        self.teacherLeftParamAct.classid = self.teacherLeftParam.classid;
        if (!self.teacherLeftParamAct.gradeid || !self.teacherLeftParamAct.classid) {
          self.vmMsgWarning('请选择年级或班级！');
          return false;
        }
        req.ajaxSend('/school/Educational/teacherSubject?type=getClassTeacherSubject', 'get', self.teacherLeftParam, function (res) {
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
          req.ajaxSend('/school/Educational/teacherSubject?type=clearsAll', 'post', self.teacherLeftParamAct, function (res) {
            if (res.status == 1) {
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
          gradeid: self.teacherLeftParamAct.gradeid,
          classid: self.teacherLeftParamAct.classid,
          data: []
        };
        for (let obj of self.teacherLeftTable) {
          let person = {
            subjectname: obj.subjectname,
            techerId: obj.techerId,
            techerName: obj.techerName,
            subjectid: obj.subjectid,
          };
          data.data.push(person);
        }
        req.ajaxSend('/school/Educational/teacherSubject?type=getClassTeacherSubjectCreate', 'post', data, function (res) {
          if (res.status == 1) {
            self.vmMsgSuccess('保存成功！');
          } else {
            self.vmMsgError(res.message);
          }
        })
      },
      loadData(data){
        var self = this;
        self.loading=true;
        req.ajaxSend('/school/Educational/getSubjectList', 'get', data, function (res) {
          self.tableData = res.data;
          self.loading=false;
        })
      }
    }
  }
</script>
<style>
  .classTeacherSet .createBtn {
    margin-top: 2rem;
  }

  .classTeacherSet .showTips {
    color: #fff;
  }
</style>
