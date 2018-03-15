<template>
  <div class="specifiedStudentDormitory">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>指定学生到宿舍</h3>
    </el-row>
    <el-row class="specifiedStudentDormitory_row">
      <span>宿舍栋号：</span>
      <el-select v-model="selectParam.dormitoryIdx" placeholder="请选择" class="dormitory" @change="setFloor">
        <el-option
          v-for="(item,ix) in dormitoryList"
          :key="ix"
          :label="item.name"
          :value="ix">
        </el-option>
      </el-select>
      <span class="l_gap">楼层：</span>
      <el-select v-model="selectParam.floorIdx" placeholder="请选择" class="level" @change="setType">
        <el-option
          v-for="(item,ix) in levelList"
          :key="ix"
          :label="item.name"
          :value="ix">
        </el-option>
      </el-select>
      <span class="l_gap">宿舍类型：</span>
      <el-select v-model="selectParam.type" placeholder="请选择" class="typeDormitory">
        <el-option
          v-for="item in typeList"
          :key="item.name"
          :label="item.znName"
          :value="item.name">
        </el-option>
      </el-select>
      <el-button type="primary" icon="el-icon-search" class="l_gap" @click="searchDormitory">查询</el-button>
    </el-row>
    <el-row :gutter="20">
      <el-col :span="5">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>宿舍</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入关键字查询"
                v-model="filterTeacher">
                <template slot="prepend">
                  <i class="el-icon-search"></i>
                </template>
              </el-input>
            </el-row>
          </el-row>
          <el-row class="d_line"></el-row>
          <el-row class="content_body">
            <div class="dTeacher" :class="{'active':data.state}" v-for="(data,index) in tableDataNew" :key="data.name"
                 @click="chooseDorm(index)"><span>{{data.dormName}}（</span><span class="act">{{data.total}}</span><span>/{{data.capacity}}）</span>
            </div>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="12">
        <el-row class="treeList">
          <el-row class="mainContent_title">
            <h5>{{activeDorm.dormName}}({{activeDorm.teaName}})</h5>
            <span class="warmTips">操作提示：1、选择左边的宿舍，再点击右边的待选学生进行添加；</span>
          </el-row>
          <el-row class="d_line"></el-row>
          <el-row class="content_body content_body_center">
            <el-tag
              :key="tag.stuId"
              v-for="(tag,ix) in actStudentList"
              :closable="true"
              :close-transition="false"
              @close="handleClose(ix)"
            >
              {{tag.name}}
            </el-tag>
          </el-row>
          <el-row class="saveBtn">
            <el-button type="danger" @click="save('clear')">清空</el-button>
            <el-button type="primary" @click="save('save')">保存</el-button>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="7">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>候选名单</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入查询班级或姓名"
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
              :data="studentList"
              node-key="id"
              ref="tree"
              :filter-node-method="filterNode"
              @node-click="chooseStudent"
              :render-content="renderContent"
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
        tableData: [],
        tableDataNew: [],
        actStudentList: [],
        studentList: [],
        defaultProps: {
          children: 'child',
          label: 'name'
        },
        filterText: '',
        filterTeacher: '',
        dormitoryList: [],
        levelList: [],
        typeList: [],
        selectParam: {
          dormitoryIdx: '',
          floorIdx: '',
          type: ''
        },
        saveParam: {
          type: 'save',
          planId: '',
          dormId: '',
          stuId: []
        },
        activeDorm: {},
        loading:false
      }
    },
    watch: {
      filterText(val) {
        this.$refs.tree.filter(val);
      },
      filterTeacher(val) {
        this.tableDataNew = this.tableData.filter(function (obj) {
          return obj.dormName.indexOf(val) !== -1;
        });
        return this.tableDataNew;
      }
    },
    created: function () {
      var self = this, data = {
        func: 'getPlanStu',
        param: {
          planId: self.$route.params.planId
        }
      }, data1 = {
        func: 'requestInfo',
        param: {
          planId: self.$route.params.planId
        }
      };
      self.saveParam.planId = self.$route.params.planId;
      //查询待选学生
      self.loading=true;
      req.ajaxSend('/school/StudentDorm/common', 'post', data, function (res) {
        self.studentList = res.data;
        self.loading=false;
      });
      //查询宿舍信息
      req.ajaxSend('/school/StudentDorm/common', 'post', data1, function (res) {
        self.dormitoryList = res.data;
      })
    },
    methods: {
      returnFlowchart() {
        this.$router.go(-1);
      },
      filterNode(value, data) {
        if (!value) return true;
        if (data.name) {
          data.name = data.name.toString();
          return data.name.indexOf(value) !== -1;
        }
      },
      setFloor() {
        this.selectParam.floorIdx = '';
        this.selectParam.type = '';
        this.levelList = this.dormitoryList[this.selectParam.dormitoryIdx].child;
        this.typeList = [];
      },
      setType() {
        this.selectParam.type = '';
        if (typeof this.selectParam.floorIdx == 'string') {
          return false;
        }
        this.typeList = this.levelList[this.selectParam.floorIdx].child;
      },
      searchDormitory() {
        var self = this, data;
        if (typeof self.selectParam.dormitoryIdx == 'string') {
          self.vmMsgWarning('请选择宿舍栋号！');
          return false;
        }
        if (typeof self.selectParam.floorIdx == 'string') {
          self.vmMsgWarning('请选择楼层！');
          return false;
        }
        if (!self.selectParam.type) {
          self.vmMsgWarning('请选择宿舍类型！');
          return false;
        }
        data = {
          func: 'getPlanDorm',
          param: {
            planId: self.saveParam.planId,
            number: self.dormitoryList[self.selectParam.dormitoryIdx].name,
            floor: self.levelList[self.selectParam.floorIdx].name,
            dormType: self.selectParam.type
          }
        };
        req.ajaxSend('/school/StudentDorm/common', 'post', data, function (res) {
          for (let obj of res.data) {
            obj.state = false;
          }
          self.actStudentList=[];
          self.activeDorm={};
          self.tableData = res.data;
          self.tableDataNew = res.data;
        })
      },
      chooseDorm(idx) {
        var self = this, data = {
          planId: self.saveParam.planId,
          dormId: self.tableDataNew[idx].dormId
        };
        for (let obj of self.tableDataNew) {
          obj.state = false;
        }
        self.tableDataNew[idx].state = true;
        self.activeDorm = self.tableDataNew[idx];
        req.ajaxSend('/school/StudentDorm/assignStu', 'post', data, function (res) {
          self.actStudentList = res.data;
        })
      },
      chooseStudent(data) {  //选择学生
        if (!data.child) {
          if (!this.activeDorm.dormId) {
            this.vmMsgWarning('请先选择宿舍！');
            return false;
          }
          for (let obj of this.actStudentList) {
            if (obj.stuId == data.stuId) {
              this.vmMsgWarning('已添加过该成员！');
              return false;
            }
          }
          this.actStudentList.push(data);
        }
      },
      handleClose(ix) {
        this.actStudentList.splice(ix, 1);
      },
      save(type) {
        var self = this;
        if (type == 'clear') {  //清空
          self.actStudentList = [];
        } else {   //保存
          if (!self.activeDorm.dormId) {
            self.vmMsgWarning('请先选择宿舍！');
            return false;
          }
          if (self.actStudentList.length > Number.parseInt(self.activeDorm.capacity)) {
            self.vmMsgWarning('添加的人数不能超过该寝室的总人数！');
            return false;
          }
          self.saveParam.stuId=[];
          self.saveParam.dormId = self.activeDorm.dormId;
          if(self.activeDorm.dormType=='1'){
            for (let obj of self.actStudentList) {
              if(obj.sex!='女'){
                self.vmMsgWarning('女宿舍只能女生入住！');
                return false;
              }
              self.saveParam.stuId.push(obj.stuId);
            }
          }else if(self.activeDorm.dormType=='2'){
            for (let obj of self.actStudentList) {
              if(obj.sex!='男'){
                self.vmMsgWarning('男宿舍只能男生入住！');
                return false;
              }
              self.saveParam.stuId.push(obj.stuId);
            }
          }else{
            for (let obj of self.actStudentList) {
              self.saveParam.stuId.push(obj.stuId);
            }
          }
          req.ajaxSend('/school/StudentDorm/assignStu', 'post', self.saveParam, function (res) {
            if (res.status == 1) {
                let data = {
                  func: 'getPlanStu',
                  param: {
                    planId: self.saveParam.planId
                  }
                };
              self.vmMsgSuccess('保存成功！');
              //查询待选学生
              req.ajaxSend('/school/StudentDorm/common', 'post', data, function (res) {
                self.studentList = res.data;
              });
              // self.searchDormitory();
            } else {
              self.vmMsgError(res.msg);
            }
          })
        }
      },
      renderContent(h, {node, data, store}) {   //树空间自定义渲染
        var nSpan = data.sex ?
      <
        span
        style = "color: #05adaa;" >（{
          data.sex
        }）</
        span >
      :
        '';
        return ( < span >
          < span >
          < span > {node.label
      }</
        span >
        < / span >
        < span
        style = "font-size:12px" > {nSpan} < / span >
          < / span >
      )
        ;
      }
    }
  }
</script>
<style>
  .specifiedStudentDormitory .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
  }

  .specifiedStudentDormitory .treeList_body {
    padding: .875rem;
    height: 45rem;
    overflow: auto;
  }

  .specifiedStudentDormitory .content_body {
    padding: 2rem .875rem;
    height: 45rem;
    overflow: auto;
  }

  .specifiedStudentDormitory .treeList_title {
    padding: .875rem;
  }

  .specifiedStudentDormitory h5 {
    font-size: 1rem;
  }

  .specifiedStudentDormitory .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .specifiedStudentDormitory .treeList .el-tree {
    border: none;
  }

  .specifiedStudentDormitory .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .specifiedStudentDormitory .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  .specifiedStudentDormitory .treeList .el-tree {
    border: none;
  }

  .specifiedStudentDormitory .warmTips {
    font-size: .875rem;
    color: #999999;
    margin-left: 1rem;
  }

  .specifiedStudentDormitory .el-tag {
    background-color: #f08bc5;
    margin: 5px 0 5px 10px;
  }

  .specifiedStudentDormitory .mainContent_title h5 {
    display: inline-block;
    padding: 1rem;
  }

  .specifiedStudentDormitory .l_gap {
    margin-left: 2rem;
  }

  .specifiedStudentDormitory .typeDormitory, .specifiedStudentDormitory .dormitory {
    width: 8.75rem;
  }

  .specifiedStudentDormitory .level {
    width: 6.25rem;
  }

  .specifiedStudentDormitory .specifiedStudentDormitory_row {
    margin: 2rem 0;
  }

  .specifiedStudentDormitory .dTeacher {
    padding: .7rem 0;
    text-align: center;
    font-weight: bold;
    border-radius: 20px;
    cursor: pointer;
  }

  .specifiedStudentDormitory .dTeacher .act {
    color: #4da1ff;
  }

  .specifiedStudentDormitory .dTeacher.active {
    background-color: #4da1ff;
    color: #fff;
  }

  .specifiedStudentDormitory .dTeacher.active .act {
    color: #fff;
  }

  .specifiedStudentDormitory .content_body_center {
    height: 44rem;
  }

  .specifiedStudentDormitory .saveBtn {
    text-align: right;
    padding: 1rem;
  }

  .specifiedStudentDormitory .saveBtn .el-button {
    padding: 10px 40px;
    border-radius: 20px;
  }

  .specifiedStudentDormitory .saveBtn .el-button--danger {
    background-color: #ff8686;
    border-color: #ff8686;
  }
</style>
