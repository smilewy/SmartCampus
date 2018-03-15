<template>
  <div class="scheduleTeacher">
    <el-row type="flex" align="middle">
      <span class="breadcrumb">
        <span :class="{'breadcrumb_active':actIndex==0}" @click="changeData(0)">班级课表</span>
        <span :class="{'breadcrumb_active':actIndex==1}" @click="changeData(1)">教师课表</span>
      </span>
    </el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="12">
        <el-button class="delete" title="导出" @click="operationTable('out')">
          <img class="delete_unactive"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
               alt="">
          <img class="delete_active"
               src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
               alt="">
        </el-button>
        <el-button-group class="secBtn-group">
          <el-button class="filt" title="复制">
            <img class="filt_unactive"
                 src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                 alt="">
            <img class="filt_active"
                 src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                 alt="">
          </el-button>
          <el-button class="delete" title="打印">
            <img class="delete_unactive"
                 src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                 alt="">
          </el-button>
        </el-button-group>
      </el-col>
      <el-col :span="12" v-show="actIndex==0">
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
        actIndex: 0,
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
      req.ajaxSend('/school/Schedule/teacher?type=getGradeAndClass', 'get', '', function (res) {
        self.gradeList = res.data;
      })
    },
    methods: {
      changeData(idx){
        var data, url = '';
        this.actIndex = idx;
        this.tableData = [];
        if (idx == 0) {
          if (typeof this.selectParam.class == 'string') {
            return false;
          }
          url = '/school/Schedule/teacher?type=getTeacherClassTable';
          data = {
            gradeId: this.classList[this.selectParam.class].gradeId,
            classId: this.classList[this.selectParam.class].classId,
          };
        } else {
          url = '/school/Schedule/teacher?type=getTeacherTable';
          data = '';
        }
        this.loadData(data, url);
      },
      operationTable(type){
        var url;
        if (this.tableData.length == 0) {
          this.vmMsgWarning('没有可以导出的数据！');
          return false;
        }
        if (type == 'out') {
          if (this.actIndex == 0) {
            url = '/school/Schedule/teacher?type=getTeacherClassTableExport&gradeId=' + this.classList[this.selectParam.class].gradeId + '&classId=' + this.classList[this.selectParam.class].classId;
          } else {
            url = '/school/Schedule/teacher?type=teacherExport';
          }
          req.downloadFile('.scheduleTeacher', url, 'post');
        }
      },
      setClass(){
        this.selectParam.class = '';
        this.classList = this.gradeList[this.selectParam.grade].data;
      },
      selectData(){
        var data, url = '/school/Schedule/teacher?type=getTeacherClassTable';
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
  .scheduleTeacher {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .scheduleTeacher .breadcrumb {
    font-size: 18px;
  }

  .scheduleTeacher .breadcrumb > span {
    color: #999999;
    padding-right: 2rem;
    cursor: pointer;
  }

  .scheduleTeacher .breadcrumb > span + span {
    border-left: 2px solid #d2d2d2;
    padding: 0 2rem;
  }

  .scheduleTeacher .breadcrumb .breadcrumb_active {
    color: #4da1ff;
  }

  .scheduleTeacher .alertsBtn {
    margin: 2.5rem 0 1.25rem 0;
  }

  .scheduleTeacher .el-table th, .scheduleTeacher .el-table td {
    text-align: center;
  }

  .scheduleTeacher .el-table td {
    padding: 1.5rem 0;
  }

  .scheduleTeacher .hasClass {
    font-weight: bold;
  }

  .scheduleTeacher .notHasClass {
    color: #999999;
  }

  .scheduleTeacher .paramForm .el-select {
    width: 8.75rem;
  }

  .scheduleTeacher .paramForm .el-form-item {
    margin-bottom: 0;
  }
</style>
