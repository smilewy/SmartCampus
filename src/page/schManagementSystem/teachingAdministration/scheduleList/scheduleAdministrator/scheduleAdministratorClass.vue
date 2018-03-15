<template>
  <div class="scheduleAdministratorClass">
    <el-row type="flex" align="middle">
      <span class="breadcrumb">
        <span class="breadcrumb_active">班级课表</span>
        <router-link tag="span" to="/scheduleAdministratorTeacher">教师课表</router-link>
        <router-link tag="span" to="/scheduleAdministratorAll">总课表</router-link>
      </span>
    </el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="12">
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
      </el-col>
      <el-col :span="12">
        <el-row type="flex" justify="end">
          <el-form :inline="true" :model="selectParam" class="paramForm">
            <el-form-item label="年级：">
              <el-select v-model="selectParam.grade" placeholder="请选择年级" @change="setClass">
                <el-option :label="gradeData.className" :value="ix" :key="ix"
                           v-for="(gradeData,ix) in gradeList"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="班级：">
              <el-select v-model="selectParam.class" placeholder="请选择班级" @change="selectData">
                <el-option :label="classData.className" :value="ix" :key="ix"
                           v-for="(classData,ix) in classList"></el-option>
              </el-select>
            </el-form-item>
          </el-form>
        </el-row>
      </el-col>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        border
        v-loading="loading"
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
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        weekData: ['节/周', '上课时间', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'],
        gradeList: [],
        classList: [],
        tableData: [],
        selectParam: {
          grade: '',
          class: ''
        },
        loading: false
      }
    },
    created: function () {
      var self = this;
      req.ajaxSend('/school/Schedule/admin?type=getGradeClass', 'get', '', function (res) {
        self.gradeList = res.data;
      });
    },
    methods: {
      operationTable(type){
        var url;
        if (this.tableData.length == 0) {
          this.vmMsgWarning('没有可以导出的数据！');
          return false;
        }
        url = '/school/Schedule/admin?type=exportClassTable&gradeId=' + this.classList[this.selectParam.class].gradeId + '&classId=' + this.classList[this.selectParam.class].classId;
        req.downloadFile('.scheduleAdministratorClass', url, 'post');
      },
      setClass(){
        this.selectParam.class = '';
        this.classList = this.gradeList[this.selectParam.grade].data;
      },
      selectData(){
        var data, url = '/school/Schedule/admin?type=classTable';
        this.tableData = [];
        if (typeof this.selectParam.class == 'string') {
          return false;
        }
        data = {
          gradeId: this.classList[this.selectParam.class].gradeId,
          classId: this.classList[this.selectParam.class].classId,
        };
        this.loadData(data, url);
      },
      loadData(data, url){
        var self = this;
        self.loading = true;
        req.ajaxSend(url, 'get', data, function (res) {
          self.loading = false;
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
  .scheduleAdministratorClass {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .scheduleAdministratorClass .breadcrumb {
    font-size: 18px;
  }

  .scheduleAdministratorClass .breadcrumb > span {
    color: #999999;
    padding-right: 2rem;
    cursor: pointer;
  }

  .scheduleAdministratorClass .breadcrumb > span + span {
    border-left: 2px solid #d2d2d2;
    padding: 0 2rem;
  }

  .scheduleAdministratorClass .breadcrumb .breadcrumb_active {
    color: #4da1ff;
  }

  .scheduleAdministratorClass .alertsBtn {
    margin: 2.5rem 0 1.25rem 0;
  }

  .scheduleAdministratorClass .el-table th, .scheduleAdministratorClass .el-table td {
    text-align: center;
  }

  .scheduleAdministratorClass .el-table td {
    padding: 1.5rem 0;
  }

  .scheduleAdministratorClass .hasClass {
    font-weight: bold;
  }

  .scheduleAdministratorClass .notHasClass {
    color: #999999;
  }

  .scheduleAdministratorClass .paramForm .el-select {
    width: 8.75rem;
  }

  .scheduleAdministratorClass .paramForm .el-form-item {
    margin-bottom: 0;
  }
</style>
