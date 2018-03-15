<template>
  <div class="personnelManagement">
    <el-row class="personnelManagement_row">
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
              <h5>宿舍（生活老师）</h5>
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
            <div class="dTeacher" :class="{'active':data.state}" v-for="(data,index) in tableDataNew" :key="data.dormId"
                 @click="chooseTeacher(index)">{{data.dormName}}({{data.teaName}})
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
              <h5>待选学生</h5>
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
  export default{
    data(){
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
          dormId: '',
          stuId: [],
          stuName: [],
          number: '',
          floor: '',
          dormNumber: ''
        },
        activeDorm: {},
        loading: false
      }
    },
    watch: {
      filterText(val) {
        this.$refs.tree.filter(val);
      },
      filterTeacher(val){
        this.tableDataNew = this.tableData.filter(function (obj) {
          return obj.dormName.indexOf(val) !== -1;
        });
        return this.tableDataNew;
      }
    },
    created: function () {
      var self = this, data = {
        func: 'getStu'
      }, data1 = {
        func: 'getInfo'
      };
      //查询待选学生
      self.loading = true;
      req.ajaxSend('/school/StudentDorm/common', 'post', data, function (res) {
        self.studentList = res.data;
        self.loading = false;
      });
      //查询宿舍信息
      req.ajaxSend('/school/StudentDorm/common', 'post', data1, function (res) {
        self.dormitoryList = res.data;
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
      setFloor(){
        this.selectParam.floorIdx = '';
        this.selectParam.type = '';
        this.levelList = this.dormitoryList[this.selectParam.dormitoryIdx].child;
        this.typeList = [];
      },
      setType(){
        this.selectParam.type = '';
        if (typeof this.selectParam.floorIdx == 'string') {
          return false;
        }
        this.typeList = this.levelList[this.selectParam.floorIdx].child;
      },
      searchDormitory(){
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
          func: 'dormList',
          param: {
            number: self.dormitoryList[self.selectParam.dormitoryIdx].name,
            floor: self.levelList[self.selectParam.floorIdx].name,
            dormType: self.selectParam.type
          }
        };
        req.ajaxSend('/school/StudentDorm/common', 'post', data, function (res) {
          for (let obj of res.data) {
            obj.state = false;
          }
          self.tableData = res.data;
          self.tableDataNew = res.data;
        })
      },
      chooseTeacher(idx){
        var self = this, data = {
          option: 1,
          dormId: self.tableDataNew[idx].dormId
        };
        for (let obj of self.tableDataNew) {
          obj.state = false;
        }
        self.tableDataNew[idx].state = true;
        self.activeDorm = self.tableDataNew[idx];
        req.ajaxSend('/school/StudentDorm/stuManage', 'post', data, function (res) {
          self.actStudentList = res.data[0].stu;
        })
      },
      chooseStudent(data){  //选择学生
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
      save(type){
        var self = this;
        if (type == 'clear') {  //清空
          self.actStudentList = [];
        } else {   //保存
          if (!self.activeDorm.dormId) {
            self.vmMsgWarning('请先选择宿舍！');
            return false;
          }
          self.saveParam.dormId = self.activeDorm.dormId;
          self.saveParam.number = self.activeDorm.number;
          self.saveParam.floor = self.activeDorm.floor;
          self.saveParam.dormNumber = self.activeDorm.dormNumber;
          if (self.activeDorm.dormType == '1') {
            for (let obj of self.actStudentList) {
              if (obj.sex != '女') {
                self.vmMsgWarning('女宿舍只能女生入住！');
                return false;
              }
              self.saveParam.stuId.push(obj.stuId);
              self.saveParam.stuName.push(obj.name);
            }
          } else if (self.activeDorm.dormType == '2') {
            for (let obj of self.actStudentList) {
              if (obj.sex != '男') {
                self.vmMsgWarning('男宿舍只能男生入住！');
                return false;
              }
              self.saveParam.stuId.push(obj.stuId);
              self.saveParam.stuName.push(obj.name);
            }
          } else {
            for (let obj of self.actStudentList) {
              self.saveParam.stuId.push(obj.stuId);
              self.saveParam.stuName.push(obj.name);
            }
          }
          req.ajaxSend('/school/StudentDorm/stuManage', 'post', self.saveParam, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('保存成功！');
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
  .personnelManagement .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
  }

  .personnelManagement .treeList_body {
    padding: .875rem;
    height: 45rem;
    overflow: auto;
  }

  .personnelManagement .content_body {
    padding: 2rem .875rem;
    height: 45rem;
    overflow: auto;
  }

  .personnelManagement .treeList_title {
    padding: .875rem;
  }

  .personnelManagement h5 {
    font-size: 1rem;
  }

  .personnelManagement .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .personnelManagement .treeList .el-tree {
    border: none;
  }

  .personnelManagement .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .personnelManagement .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  .personnelManagement .treeList .el-tree {
    border: none;
  }

  .personnelManagement .warmTips {
    font-size: .875rem;
    color: #999999;
    margin-left: 1rem;
  }

  .personnelManagement .el-tag {
    background-color: #f08bc5;
    margin: 5px 0 5px 10px;
  }

  .personnelManagement .mainContent_title h5 {
    display: inline-block;
    padding: 1rem;
  }

  .personnelManagement .l_gap {
    margin-left: 2rem;
  }

  .personnelManagement .typeDormitory, .personnelManagement .dormitory {
    width: 8.75rem;
  }

  .personnelManagement .level {
    width: 6.25rem;
  }

  .personnelManagement .personnelManagement_row {
    font-size: 14px;
  }

  .personnelManagement .dTeacher {
    padding: .7rem 0;
    text-align: center;
    font-weight: bold;
    border-radius: 20px;
    cursor: pointer;
  }

  .personnelManagement .dTeacher.active {
    background-color: #4da1ff;
    color: #fff;
  }

  .personnelManagement .content_body_center {
    height: 44rem;
  }

  .personnelManagement .saveBtn {
    text-align: right;
    padding: 1rem;
  }

  .personnelManagement .saveBtn .el-button {
    padding: 10px 40px;
    border-radius: 20px;
  }

  .personnelManagement .saveBtn .el-button--danger {
    background-color: #ff8686;
    border-color: #ff8686;
  }
</style>
