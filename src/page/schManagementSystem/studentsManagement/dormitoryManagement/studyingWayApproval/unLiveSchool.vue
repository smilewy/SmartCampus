<template>
  <div class="unLiveSchool">
    <el-row type="flex" align="middle" class="studyingWayApproval_row">
      <el-button type="primary" class="uploadTemplate" @click="changeState">调整为住校</el-button>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button class="delete" title="导出" @click="operationData('out')">
          <img class="delete_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" alt="">
          <img class="delete_active"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
               alt="">
        </el-button>
      </el-col>
      <el-col :span="8">
        <div class="g-fuzzyInput">
          <el-input
            placeholder="请输入考号/姓名/录取类型"
            suffix-icon="el-icon-search"
            v-model="selectParam.key"
            @change="goSearch">
          </el-input>
        </div>
      </el-col>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        @selection-change="handleSelectionChange"
        @sort-change="sort"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          type="selection"
          width="55">
        </el-table-column>
        <el-table-column
          type="index"
          width="80"
          label="序号">
        </el-table-column>
        <el-table-column
          prop="number"
          min-width="150"
          label="学号" sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="100"
          prop="name"
          label="姓名" sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="100"
          prop="sex"
          label="性别" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="grade"
          min-width="100"
          label="年级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="className"
          min-width="100"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="phone"
          min-width="150"
          label="手机号" sortable="custom">
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableData.length!=0">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="selectParam.page"
        :page-size="selectParam.count"
        layout="prev, pager, next, jumper"
        :total="totalNum">
      </el-pagination>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        tableData: [],
        selectParam: {
          option: 3,
          page: 1,
          count: 50,
          key: ''
        },
        totalNum: 0,
        multipleSelection: [],
        sortObj: {
          id: 'si.id',
          name: 'si.name',
          sex: 'si.sex',
          grade: 'si.grade',
          className: 'si.className',
          phone: 'si.phone',
          number: 'u.number'
        },
        loading: false
      }
    },
    created: function () {
      this.loadData(this.selectParam);
    },
    methods: {
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.according = '';
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      sort(column){
        this.selectParam.according = column.prop ? this.sortObj[column.prop] : '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      operationData(type){
        if (type == 'out') {
          req.downloadFile('.unLiveSchool', '/school/StudentDorm/attendApprove?download=ensure&option=3', 'post');
        }
      },
      changeState(){  //是否通过审批
        var self = this, data = {
          option: 3,
          type: 'inSchool',
          ids: []
        };
        if (self.multipleSelection.length == 0) {
          self.vmMsgWarning('请选择记录！');
          return false;
        }
        for (let obj of self.multipleSelection) {
          data.ids.push(obj.id);
        }
        self.$confirm('确定调整?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/StudentDorm/attendApprove', 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('调整成功！');
              self.loadData(self.selectParam);
            } else {
              self.vmMsgError(res.msg);
            }
          })
        }).catch(() => {
        });
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/StudentDorm/attendApprove', 'post', data, function (res) {
          self.tableData = res.data;
          self.totalNum = res.total;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
</style>
