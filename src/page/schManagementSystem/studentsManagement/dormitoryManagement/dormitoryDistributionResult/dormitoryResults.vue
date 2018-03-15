<template>
  <div class="dormitoryResults">
    <el-row type="flex" align="middle">
      <h3>宿舍分配结果</h3>
    </el-row>
    <el-row class="dormitoryResults_row">
      <!--<el-form :inline="true" class="formInline">
        <el-form-item label="宿舍分配方案：">
          <el-select v-model="reportType" placeholder="请选择" class="reportType" @change="selectData">
            <el-option
              v-for="item in options"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>-->
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          label="栋号"
          prop="buildNumber"
          width="100">
        </el-table-column>
        <el-table-column
          label="宿舍楼名称"
          prop="buildName"
          min-width="150">
        </el-table-column>
        <el-table-column
          label="楼层"
          prop="floor"
          min-width="100">
        </el-table-column>
        <el-table-column
          prop="dormNumber"
          label="宿舍号"
          min-width="100">
        </el-table-column>
        <el-table-column
          prop="dormName"
          label="宿舍名"
          min-width="150">
        </el-table-column>
        <el-table-column
          prop="dormType"
          label="宿舍类型"
          min-width="120">
        </el-table-column>
        <el-table-column
          prop="teaName"
          label="生活老师"
          min-width="150">
        </el-table-column>
        <el-table-column
          prop="grade"
          label="年级"
          min-width="100">
        </el-table-column>
        <el-table-column
          prop="class"
          label="班级"
          min-width="100">
        </el-table-column>
        <el-table-column
          prop="number"
          label="学号"
          min-width="150">
        </el-table-column>
        <el-table-column
          prop="name"
          label="姓名"
          min-width="150">
        </el-table-column>
      </el-table>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        tableData: [],
        options: [{
          value: 1,
          label: '粘贴总名单'
        }, {
          value: 2,
          label: '宿舍分配总名单'
        }, {
          value: 3,
          label: '各宿舍学生名单'
        }],
        reportType: '',
        loading: false
      }
    },
    created: function () {
      var self = this;
      self.loading = true;
      req.ajaxSend('/school/StudentDorm/dormResult', 'post', '', function (res) {
        self.tableData = res.data;
        self.loading = false;
      })
    },
    methods: {
      selectData() {
        var self = this, data = {
          planId: self.$route.params.planId,
          option: self.reportType
        };
        req.ajaxSend('/school/StudentDorm/reportForm', 'post', data, function (res) {
          self.tableData = res;
        })
      }
    }
  }
</script>
<style>
  .dormitoryResults {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .dormitoryResults h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
    display: inline-block;
  }

  .dormitoryResults .alertsList {
    margin: 1.25rem 0;
  }

  .dormitoryResults .el-table th, .dormitoryResults .el-table td {
    text-align: center;
  }

  .dormitoryResults .dormitoryResults_row {
    margin-top: 2rem;
  }

  .dormitoryResults .typeStyleOne .el-table th {
    background-color: #deeefe;
    height: 2.5rem;
  }

  .dormitoryResults .typeStyleOne .el-table td {
    height: 2.5rem;
    font-size: .875rem;
  }

  .dormitoryResults .typeStyleOne .el-table__footer-wrapper thead div, .dormitoryResults .typeStyleOne .el-table__header-wrapper thead div {
    background-color: #deeefe;
    color: #282828;
  }

  .dormitoryResults .reportType {
    width: 15.625rem;
  }
</style>
