<template>
  <div class="teacherToWorker">
    <el-row>
      <h3>教师转职工</h3>
      <span class="tips">备注：教师转为职工，只是权限改变，原有的任教信息、个人资料不变。</span>
    </el-row>
    <el-row class="scoreRow">
      <el-row type="flex" align="middle">
        <el-col :span="16">
          <el-form :inline="true" class="formInline">
            <el-form-item label="任教科目：">
              <el-select v-model="selectParam.subjectid" placeholder="请选择" class="test" @change="chooseTeacherL">
                <el-option
                  v-for="item in subjectList"
                  :key="item.subjectid"
                  :label="item.subjectname"
                  :value="item.subjectid">
                </el-option>
              </el-select>
            </el-form-item>
          </el-form>
        </el-col>
        <el-col :span="8">
          <el-row type="flex" justify="end">
            <el-button type="primary" @click="saveMsg">保存</el-button>
          </el-row>
        </el-col>
      </el-row>
      <el-row>
        <span class="tip">操作提示：在左侧列表点击待选教师的角色框，然后在角色列表中点击系统角色赋予当前教师</span>
      </el-row>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row :gutter="20" class="scoreRow">
      <el-col :span="7">
        <el-row class="contentBox">
          <el-row type="flex" align="middle" class="contentBox_head">
            <el-col :span="8">
              <h5>待选教师</h5>
            </el-col>
            <el-col :span="16">
              <el-row class="treeInput">
                <el-input
                  placeholder="请输入查询姓名"
                  v-model="filterText">
                  <template slot="prepend">
                    <i class="el-icon-search"></i>
                  </template>
                </el-input>
              </el-row>
            </el-col>
          </el-row>
          <el-row class="alertsList contentBox_body">
            <el-table
              :data="tableData"
              style="width: 100%"
              v-loading="loading"
              element-loading-text="拼命加载中"
            >
              <el-table-column
                prop="name"
                label="姓名">
              </el-table-column>
              <el-table-column
                label="角色">
                <template slot-scope="scope">
                  <div class="roleC" :class="{'active':scope.row.checked}" @click="chooseTeacher(scope.$index)">
                    {{scope.row.roleName}}
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="5">
        <el-row class="contentBox">
          <el-row type="flex" align="middle" class="contentBox_head">
            <h5>角色表</h5>
          </el-row>
          <el-row class="contentBox_body">
            <ul>
              <li class="roleMenu" :class="{'active':role.checked}" v-for="(role,ix) in roleList" :key="role.roleId"
                  @click="chooseRole(ix)">{{role.roleName}}
              </li>
            </ul>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="12">
        <el-row class="contentBox">
          <el-row type="flex" align="middle" class="contentBox_head">
            <h5>教师转职工一览表</h5>
          </el-row>
          <el-row class="alertsList contentBox_body">
            <el-table
              :data="tableDataList"
              style="width: 100%"
            >
              <el-table-column
                type="index"
                width="80"
                label="序号">
              </el-table-column>
              <el-table-column
                prop="name"
                label="姓名">
              </el-table-column>
              <el-table-column
                prop="teachingSubjects"
                label="任教科目">
              </el-table-column>
              <el-table-column
                prop="roleName"
                label="角色">
              </el-table-column>
              <el-table-column
                label="操作">
                <template slot-scope="scope">
                  <span class="edit" @click="deleteData(scope.$index)">删除</span>
                </template>
              </el-table-column>
            </el-table>
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
        subjectList: [],
        selectParam: {
          subjectid: ''
        },
        filterText: '',
        tableData: [],
        tableAllData: [],
        roleList: [],
        tableDataList: [],
        indexObj: {
          idxL: '',
          idxC: '',
          idxR: ''
        },
        loading:false
      }
    },
    created: function () {
      var self = this;
      req.ajaxSend('/school/user/teacherToZg?type=getSubject', 'get', '', function (res) {
        self.subjectList = res.data;
      })
    },
    watch: {
      filterText(val) {
        this.tableData = this.tableAllData.filter(function (data) {
          return data.name.indexOf(val) !== -1;
        });
        return this.tableData;
      }
    },
    methods: {
      chooseTeacherL(){
        var self = this;
        self.filterText = '';
        self.loading=true;
        req.ajaxSend('/school/user/teacherToZg?type=getTeacher', 'get', self.selectParam, function (res) {
          self.loading=false;
          // 防止res.data 为null的情况出现报错
          res.data = res.data || [];
          for (let obj of res.data) {
            obj.roleName = '';
            obj.roleId = '';
            obj.checked = false;
          }
          self.tableData = res.data;
          self.tableAllData = res.data;
        });
        req.ajaxSend('/school/user/teacherToZg?type=getRole', 'get', self.selectParam, function (res) {
          for (let obj of res.data) {
            obj.checked = false;
          }
          self.roleList = res.data;
        });
      },
      chooseTeacher(ix){
        for (let obj of this.roleList) {
          obj.checked = false;
        }
        for (let obj of this.tableData) {
          obj.checked = false;
        }
        this.tableData[ix].checked = true;
        this.indexObj.idxL = ix;
        this.indexObj.idxC = '';
      },
      chooseRole(ix){
        if (typeof this.indexObj.idxL == 'string') {
          this.vmMsgWarning('请先选择待选教师！');
          return false;
        }
        for (let obj of this.roleList) {
          obj.checked = false;
        }
        this.roleList[ix].checked = true;
        this.indexObj.idxC = ix;
        this.tableData[this.indexObj.idxL].roleName = this.roleList[ix].roleName;
        this.tableData[this.indexObj.idxL].roleId = this.roleList[ix].roleId;
        for (let obj of this.tableDataList) {
          if (obj.id == this.tableData[this.indexObj.idxL].id) {
            return false;
          }
        }
        this.tableDataList.push(this.tableData[this.indexObj.idxL]);
      },
      deleteData(idx){
        this.tableDataList[idx].roleName = '';
        this.tableDataList[idx].roleId = '';
        this.tableDataList.splice(idx, 1);
      },
      saveMsg(){
        var self = this, data = {
          data: []
        };
        if (self.tableDataList.length == 0) {
          self.vmMsgWarning('请先选择要转职工的教师！');
          return false;
        }
        for (let obj of self.tableDataList) {
          let d = {
            id: obj.id,
            roleId: obj.roleId,
            roleName: obj.roleName
          };
          data.data.push(d);
        }
        req.ajaxSend('/school/user/teacherToZg?type=create', 'get', data, function (res) {
          if (res.statu == 1) {
            self.vmMsgSuccess('保存成功！');
          } else {
            self.vmMsgError(res.message);
          }
        })
      }
    }
  }
</script>
<style>
  .teacherToWorker {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    font-size: 14px;
  }

  .teacherToWorker h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
    display: inline-block;
  }

  .teacherToWorker .tips {
    color: #ff4f4f;
    font-size: .875rem;
    margin-left: 1rem;
  }

  .teacherToWorker .tip {
    font-size: .875rem;
    color: #666666;
  }

  .teacherToWorker .scoreRow {
    margin: 1.125rem 0;
  }

  .teacherToWorker .el-table td, .teacherToWorker .el-table th {
    text-align: center;
  }

  .teacherToWorker .test {
    width: 15.625rem;
  }

  .teacherToWorker .formInline .el-form-item {
    margin-right: 2.5rem;
  }

  .teacherToWorker .contentBox {
    border: 1px solid #d2d2d2;
    border-radius: 6px;
    height: 46.25rem;
  }

  .teacherToWorker h5 {
    font-size: 1rem;
  }

  .teacherToWorker .treeInput {
    width: 100%;
  }

  .teacherToWorker .contentBox .contentBox_head {
    height: 3.25rem;
    padding: 0 1rem;
  }

  .teacherToWorker .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .teacherToWorker .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  .teacherToWorker .contentBox_body {
    height: 43rem;
    overflow: auto;
    border-top: 1px solid #d2d2d2;
  }

  .teacherToWorker .roleMenu {
    text-align: center;
    padding: 1rem 0;
    cursor: pointer;
  }

  .teacherToWorker .roleMenu.active {
    background-color: #4da1ff;
    color: #fff;
  }

  .teacherToWorker .edit {
    color: #ff4f4f;
    cursor: pointer;
  }

  .teacherToWorker .roleC {
    cursor: pointer;
    border: 1px solid #d2d2d2;
    border-radius: 4px;
    height: 2rem;
  }

  .teacherToWorker .roleC.active {
    border: 1px solid #4da1ff;
  }
</style>
