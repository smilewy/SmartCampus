<template>
  <div class="scheduleAdministratorTeacher">
    <el-row type="flex" align="middle">
      <span class="breadcrumb">
        <router-link tag="span" to="/scheduleAdministratorClass">班级课表</router-link>
        <span class="breadcrumb_active">教师课表</span>
        <router-link tag="span" to="/scheduleAdministratorAll">总课表</router-link>
      </span>
    </el-row>
    <el-row class="teacherSchedule" :gutter="20">
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
              :highlight-current="true"
              :filter-node-method="filterNode"
              @node-click="chooseTeacher"
              :props="defaultProps">
            </el-tree>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="18">
        <el-row type="flex" align="middle" class="alertsBtn">
          <el-button class="delete" title="导出" @click="operationTable('out')">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
                 alt="">
          </el-button>
          <el-button-group class="secBtn-group">
            <el-button class="filt" title="复制">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                   alt="">
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                   alt="">
            </el-button>
            <el-button class="delete" title="打印">
              <img class="delete_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                   alt="">
              <img class="delete_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                   alt="">
            </el-button>
          </el-button-group>
        </el-row>
        <el-row class="alertsList">
          <el-table
            :data="tableData"
            style="width: 100%"
            border
            v-loading="loading1"
            element-loading-text="拼命加载中"
          >
            <el-table-column v-for="n in 9" :key="n" :label="weekData[n-1]">
              <template slot-scope="scope">
                <span v-if="n==1 || n==2">{{scope.row[n-1].subjectName}}</span>
                <div v-if="n!=1 && n!=2">
                  <div class="notHasClass" v-if="scope.row[n-1].statu==0">不上课</div>
                  <div class="hasClass" v-if="scope.row[n-1].statu!=0">
                    <p>{{scope.row[n-1].subjectName}}</p>
                    <p v-if="scope.row[n-1].teacherName">（{{scope.row[n-1].teacherName}}）</p>
                  </div>
                </div>
              </template>
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
        weekData: ['节/周', '上课时间', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'],
        treeData: [],
        defaultProps: {
          children: 'data',
          label: 'techerName'
        },
        filterText: '',
        tableData: [],
        selectParam: {
          techerId: ''
        },
        loading: false,
        loading1: false
      }
    },
    watch: {
      filterText(val) {
        this.$refs.tree.filter(val);
      }
    },
    created: function () {
      var self = this;
      self.loading = true;
      req.ajaxSend('/school/Schedule/admin?type=getTeacherList', 'get', '', function (res) {
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
      chooseTeacher(data, node, event){
        if (data.techerId) {
          var dat = {
            techerId: data.techerId
          };
          this.tableData = [];
          this.selectParam.techerId = data.techerId;
          this.loadData(dat, '/school/Schedule/admin?type=teacherTable');
        }
      },
      operationTable(type){
        var url;
        if (this.tableData.length == 0) {
          this.vmMsgWarning('没有可以导出的数据！');
          return false;
        }
        url = '/school/Schedule/teacher?type=exportTeacherTable&techerId=' + this.selectParam.techerId;
        req.downloadFile('.scheduleAdministratorTeacher', url, 'post');
      },
      loadData(data, url){
        var self = this;
        self.loading1 = true;
        req.ajaxSend(url, 'get', data, function (res) {
          self.loading1 = false;
          if (res.statu == 1) {
            self.tableData = res.data;
          } else {
            self.vmMsgError(res.message);
          }
        })
      }
    }
  }
</script>
<style>
  .scheduleAdministratorTeacher {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .scheduleAdministratorTeacher .breadcrumb {
    font-size: 18px;
  }

  .scheduleAdministratorTeacher .breadcrumb > span {
    color: #999999;
    padding-right: 2rem;
    cursor: pointer;
  }

  .scheduleAdministratorTeacher .breadcrumb > span + span {
    border-left: 2px solid #d2d2d2;
    padding: 0 2rem;
  }

  .scheduleAdministratorTeacher .breadcrumb .breadcrumb_active {
    color: #4da1ff;
  }

  .scheduleAdministratorTeacher .alertsBtn {
    margin: 0 0 1.25rem 0;
  }

  .scheduleAdministratorTeacher .el-table th, .scheduleAdministratorTeacher .el-table td {
    text-align: center;
  }

  .scheduleAdministratorTeacher .el-table td {
    padding: 1.5rem 0;
  }

  .scheduleAdministratorTeacher .hasClass {
    font-weight: bold;
  }

  .scheduleAdministratorTeacher .notHasClass {
    color: #999999;
  }

  .scheduleAdministratorTeacher .teacherSchedule {
    margin-top: 2.5rem;
  }

  .scheduleAdministratorTeacher .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
  }

  .scheduleAdministratorTeacher .treeList_body {
    padding: .875rem;
    height: 45rem;
    overflow: auto;
  }

  .scheduleAdministratorTeacher .treeList_title {
    padding: .875rem;
  }

  .scheduleAdministratorTeacher .treeList_title h5 {
    font-size: 1rem;
  }

  .scheduleAdministratorTeacher .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .scheduleAdministratorTeacher .treeList .el-tree {
    border: none;
  }

  .scheduleAdministratorTeacher .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .scheduleAdministratorTeacher .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }
</style>
