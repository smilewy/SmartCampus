<template>
  <div class="specifiedStudentClass">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>指定学生到班</h3>
    </el-row>
    <el-row :gutter="20" class="subClassDivision_row">
      <el-col :span="5">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>待选班级</h5>
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
              check-strictly
              @node-click="chooseClass"
              :props="defaultProps">
            </el-tree>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="5">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>{{activeClass.branch}}-{{activeClass.grade}}-{{activeClass.name}} <span class="classNum">（<span>{{studentData.length}}</span>/{{activeClass.number}}人）</span>
              </h5>
            </el-row>
          </el-row>
          <el-row class="d_line"></el-row>
          <el-row class="nameLists">
            <el-row v-for="(tab,idx) in studentData" :key="tab.id" class="nameList">
              {{tab.name}}
              <i class="el-icon-close" @click="deleteData(idx)"></i>
            </el-row>
          </el-row>
          <el-row class="btns">
            <el-button @click="save('clear')">清空</el-button>
            <el-button type="primary" @click="save">保存</el-button>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="14">
        <el-row type="flex" align="middle" justify="center" class="listHeader">
          <el-col :span="7">待选学生</el-col>
          <el-col :span="16">
            <div class="g-fuzzyInput">
              <el-input
                placeholder="请输入姓名/班级/年级/专业"
                suffix-icon="el-icon-search"
                v-model="selectParam.param.key"
                @change="goSearch">
              </el-input>
            </div>
          </el-col>
        </el-row>
        <el-row class="studentsList">
          <el-table
            :data="tableData"
            style="width: 100%"
            border
            max-height="700"
            v-loading="loading1"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              type="index"
              width="80"
              label="序号">
            </el-table-column>
            <el-table-column
              prop="name"
              min-width="120"
              label="姓名" sortable>
              <template slot-scope="scope">
                <div @click="chooseStudent(scope.$index)">{{scope.row.name}}</div>
              </template>
            </el-table-column>
            <el-table-column
              prop="sex"
              min-width="120"
              label="性别" sortable>
            </el-table-column>
            <el-table-column
              prop="preGrade"
              min-width="150"
              label="当前年级" sortable>
            </el-table-column>
            <el-table-column
              prop="preClass"
              min-width="150"
              label="当前班级" sortable>
            </el-table-column>
            <el-table-column
              prop="assignClass"
              min-width="150"
              label="指定班级" sortable>
            </el-table-column>
            <el-table-column
            prop="major"
            min-width="150"
            label="志愿专业" sortable>
            </el-table-column>
          </el-table>
        </el-row>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        tableData: [],
        studentData: [],
        treeData: [],
        defaultProps: {
          children: 'child',
          label: 'name'
        },
        activeClass: {},
        selectParam: {
          func: 'getStu',
          param: {
            planId: '',
            key: ''
          }
        },
        loading: false,
        loading1: false
      }
    },
    created: function () {
      var self = this, data1;
      self.selectParam.param.planId = self.$route.params.planId;
      data1 = {
        func: 'getClass',
        param: {
          planId: self.selectParam.param.planId
        }
      };
      //查询待选班级
      self.loading = true;
      req.ajaxSend('/school/DivideBranch/common', 'post', data1, function (res) {
        self.treeData = res.data;
        self.loading = false;
      });
      //查询待选学生
      self.goSearch();
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      },
      goSearch() {  //查询
        var self = this;
        //查询待选学生
        self.loading1 = true;
        req.ajaxSend('/school/DivideBranch/common', 'post', self.selectParam, function (res) {
          self.tableData = res.data;
          self.loading1 = false;
        })
      },
      deleteData(idx){
        this.studentData.splice(idx, 1);
      },
      chooseClass(node, state, child){
        if (!node.classId) {
          if (!node.child) {
            this.vmMsgWarning('请点击选择班级!');
          }
          return false;
        }
        this.activeClass = node;
        let ary = node.child || [], nAry = ary.concat(this.studentData);
        this.studentData = [];  //类型1，点击清空所选的列表
        for (let obj of ary) {
          this.studentData.push(obj);
        }
        /*this.studentData = removeDuplicatedItem(nAry);  //类型2，不清空所选的列表
         function removeDuplicatedItem(ar) {
         var tmp = {}, ret = [];
         for (let obj of ar) {
         if (!tmp[obj.name]) {
         tmp[obj.name] = 1;
         ret.push(obj);
         }
         }
         return ret;
         }*/
      },
      chooseStudent(idx){
        if (!this.activeClass.classId) {
          this.vmMsgWarning('请选择班级!');
          return false;
        }
        for (let obj of this.studentData) {
          if (obj.userId == this.tableData[idx].userId) {
            this.vmMsgWarning('该学生已存在列表中!');
            return false;
          }
        }
        this.studentData.push(this.tableData[idx]);
      },
      save(type){
        var self = this, url = '/school/DivideBranch/assignStu', data, data1 = {
          func: 'getClass',
          param: {
            planId: self.selectParam.param.planId
          }
        };
        if (!self.activeClass.classId) {
          self.vmMsgWarning('请选择班级!');
          return false;
        }
        if (type == 'clear') {  //清空
          data = {
            planId: self.selectParam.param.planId,
            type: 'clean',
            classId: self.activeClass.classId
          };
          self.$confirm('有数据未保存，确定清空?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend(url, 'post', data, function (res) {
              if (res.status == 1) {
                self.vmMsgSuccess('清空成功!');
                self.studentData = [];
                //查询待选班级
                self.loading = true;
                req.ajaxSend('/school/DivideBranch/common', 'post', data1, function (res) {
                  self.treeData = res.data;
                  self.loading = false;
                });
                //查询待选学生
                self.goSearch();
              } else {
                self.vmMsgError(res.msg);
              }
            })
          }).catch(() => {
          });
        } else {   //保存
          data = {
            planId: self.selectParam.param.planId,
            type: 'save',
            classId: self.activeClass.classId,
            allot: {
              classId: self.activeClass.classId,
              className: self.activeClass.name,
              grade: self.activeClass.grade
            },
            stuIds: []
          };
          for (let obj of self.studentData) {
            data.stuIds.push(obj.id);
          }
          if (self.studentData.length > Number.parseInt(self.activeClass.number)) {
            self.vmMsgWarning('所选人数不能超过该班级的容纳人数！');
            return false;
          }
          req.ajaxSend(url, 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('保存成功!');
              //查询待选班级
              self.loading = true;
              req.ajaxSend('/school/DivideBranch/common', 'post', data1, function (res) {
                self.treeData = res.data;
                self.loading = false;
              });
              //查询待选学生
              self.goSearch();
            } else {
              self.vmMsgError(res.msg);
            }
          })
        }
      }
    }
  }
</script>
<style>
  .specifiedStudentClass .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
    -webkit-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    -moz-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    box-shadow: 0 0 1px 1px #d2d2d2 inset;
  }

  .specifiedStudentClass .treeList_body {
    padding: .875rem;
    height: 48rem;
    overflow: auto;
  }

  .specifiedStudentClass .treeList_title {
    padding: .875rem;
  }

  .specifiedStudentClass h5 {
    font-size: 1rem;
  }

  .specifiedStudentClass h5 .classNum {
    float: right;
    font-size: 14px;
    font-weight: normal;
  }

  .specifiedStudentClass .classNum > span {
    color: #4da1ff;
  }

  .specifiedStudentClass .treeList .el-tree {
    border: none;
  }

  .specifiedStudentClass .nameList {
    padding: .625rem 1rem;
    font-size: .875rem;
    position: relative;
  }

  .specifiedStudentClass .nameList:hover {
    background-color: #deeefe;
  }

  .specifiedStudentClass .nameLists {
    height: 43rem;
    overflow: auto;
  }

  .specifiedStudentClass .nameList i {
    font-size: 12px;
    color: #ff5b5a;
    position: absolute;
    right: 1rem;
    top: .625rem;
    display: none;
    cursor: pointer;
  }

  .specifiedStudentClass .nameList:hover > i {
    display: inline-block;
  }

  .specifiedStudentClass .btns {
    text-align: center;
    margin-top: 1.25rem;
  }

  .specifiedStudentClass .btns .el-button {
    border-radius: 20px;
    width: 6.25rem;
    padding: 10px 0;
  }

  .specifiedStudentClass .listHeader {
    height: 3.375rem;
    background-color: #89bcf5;
    color: #fff;
    font-size: .875rem;
  }

  .specifiedStudentClass .studentsList .el-table th {
    background-color: #deeefe;
    height: 3rem;
  }

  .specifiedStudentClass .studentsList .el-table td {
    height: 3rem;
    font-size: .875rem;
  }

  .specifiedStudentClass .studentsList .el-table__footer-wrapper thead div, .specifiedStudentClass .studentsList .el-table__header-wrapper thead div {
    background-color: #deeefe;
  }
</style>
