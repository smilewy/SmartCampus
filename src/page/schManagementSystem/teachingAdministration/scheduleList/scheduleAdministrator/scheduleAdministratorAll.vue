<template>
  <div class="scheduleAdministratorAll">
    <el-row type="flex" align="middle">
      <span class="breadcrumb">
        <router-link tag="span" to="/scheduleAdministratorClass">班级课表</router-link>
        <router-link tag="span" to="/scheduleAdministratorTeacher">教师课表</router-link>
        <span class="breadcrumb_active">总课表</span>
      </span>
    </el-row>
    <el-row class="alertsList teacherSchedule">
      <el-table
        :data="tableData"
        style="width: 100%"
        border
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column label="班级" prop="name"></el-table-column>
        <el-table-column v-for="n in 8" :key="n" :label="weekData[n-1]">
          <template slot-scope="scope">
            <el-row type="flex" align="middle" justify="center" class="table-cell" :key="columnI"
                    v-for="(content,columnI) in scope.row.data[n-1]">
              <span v-if="n==1">{{content.subjectName}}</span>
              <div v-if="n!=1">
                <div class="notHasClass" v-if="content.statu==0">不上课</div>
                <div class="hasClass" v-if="content.statu!=0">
                  <p>{{content.subjectName}}</p>
                  <p v-if="content.teacherName">（{{content.teacherName}}）</p>
                </div>
              </div>
            </el-row>
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
        weekData: ['节/周', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'],
        tableData: [],
        loading: false
      }
    },
    created: function () {
      this.loadData();
    },
    methods: {
      loadData(){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Schedule/admin?type=allClass', 'get', '', function (res) {
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
  .scheduleAdministratorAll {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .scheduleAdministratorAll .breadcrumb {
    font-size: 18px;
  }

  .scheduleAdministratorAll .breadcrumb > span {
    color: #999999;
    padding-right: 2rem;
    cursor: pointer;
  }

  .scheduleAdministratorAll .breadcrumb > span + span {
    border-left: 2px solid #d2d2d2;
    padding: 0 2rem;
  }

  .scheduleAdministratorAll .breadcrumb .breadcrumb_active {
    color: #4da1ff;
  }

  .scheduleAdministratorAll .el-table th, .scheduleAdministratorAll .el-table td {
    text-align: center;
  }

  .scheduleAdministratorAll .hasClass {
    font-weight: bold;
  }

  .scheduleAdministratorAll .notHasClass {
    color: #999999;
  }

  .scheduleAdministratorAll .teacherSchedule {
    margin-top: 2.5rem;
  }

  .scheduleAdministratorAll .el-table .cell {
    padding-left: 0;
    padding-right: 0;
  }

  .scheduleAdministratorAll .el-table .table-cell {
    height: 5rem;
    border-bottom: 1px solid #dfe6ec;
  }

  .scheduleAdministratorAll .el-table--enable-row-hover .el-table__body tr:hover > td {
    background-color: #fff;
  }
</style>
