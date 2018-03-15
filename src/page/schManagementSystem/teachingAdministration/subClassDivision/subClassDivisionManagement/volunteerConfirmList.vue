<template>
  <div class="volunteerConfirmList">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>志愿确认签名表</h3>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-button class="delete" title="打印">
        <img class="delete_unactive"
             src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
             alt="">
        <img class="delete_active"
             src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
             alt="">
      </el-button>
    </el-row>
    <el-row
      v-loading="loading"
      element-loading-text="拼命加载中">
      <el-row class="volunteerConfirmList_row" v-for="(data,idx) in tableData" :key="idx">
        <el-row class="listTitle">
          <p>分科分班志愿核对确认表</p>
          <p>{{data.name}}</p>
        </el-row>
        <el-row :gutter="40" type="flex" justify="center">
          <el-col :span="10">
            <el-table
              :data="data.tableDataL"
              style="width: 100%"
              border
            >
              <el-table-column
                prop="serialNumber"
                label="班级序号">
              </el-table-column>
              <el-table-column
                prop="name"
                label="姓名">
              </el-table-column>
              <el-table-column
                prop="wish"
                label="志愿">
              </el-table-column>
              <el-table-column
                label="确认签字">
                <template slot-scope="scope">
                  <span v-if="scope.row.confirm!='未确认'">{{scope.row.confirm}}</span>
                  <span v-if="scope.row.confirm=='未确认'"></span>
                </template>
              </el-table-column>
            </el-table>
            <el-row class="total" v-if="data.tableDataR.length==0">合计：未填报{{data.count}}</el-row>
          </el-col>
          <el-col :span="10" v-if="data.tableDataR.length!=0">
            <el-table
              :data="data.tableDataR"
              style="width: 100%"
              border
            >
              <el-table-column
                prop="serialNumber"
                label="班级序号">
              </el-table-column>
              <el-table-column
                prop="name"
                label="姓名">
              </el-table-column>
              <el-table-column
                prop="wish"
                label="志愿">
              </el-table-column>
              <el-table-column
                label="确认签字">
                <template slot-scope="scope">
                  <span v-if="scope.row.confirm!='未确认'">{{scope.row.confirm}}</span>
                  <span v-if="scope.row.confirm=='未确认'"></span>
                </template>
              </el-table-column>
            </el-table>
            <el-row class="total">合计：未填报{{data.count}}</el-row>
          </el-col>
        </el-row>
      </el-row>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        tableData: [],
        tableDataL: [],
        tableDataR: [],
        loading: false
      }
    },
    created: function () {
      var self = this, data = {
        planId: self.$route.params.planId
      };
      self.loading = true;
      req.ajaxSend('/school/DivideBranch/wishConfirm', 'post', data, function (res) {
        self.loading = false;
        for (let obj of res.data) {
          let aLen = obj.stu.length, len = Number.parseInt(aLen / 2);
          self.tableDataL = obj.stu.slice(0, len + 1);
          self.tableDataR = obj.stu.slice(len + 1, aLen);
          self.tableData.push({
            name: obj.name,
            tableDataL: self.tableDataL,
            tableDataR: self.tableDataR,
            count: obj.not
          })
        }
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      }
    }
  }
</script>
<style>
  .volunteerConfirmList .volunteerConfirmList_row {
    margin-bottom: 3.5rem;
  }

  .volunteerConfirmList .alertsBtn {
    margin: 1.25rem 0;
  }

  .volunteerConfirmList .total {
    text-align: center;
    height: 2.5rem;
    line-height: 2.5rem;
    font-size: .875rem;
    border-left: 1px solid #dfe6ec;
    border-right: 1px solid #dfe6ec;
    border-bottom: 1px solid #dfe6ec;
  }

  .volunteerConfirmList .el-table th {
    background-color: #deeefe;
    height: 2.5rem;
  }

  .volunteerConfirmList .el-table td {
    height: 2.5rem;
    font-size: .875rem;
  }

  .volunteerConfirmList .el-table__footer-wrapper thead div, .volunteerConfirmList .el-table__header-wrapper thead div {
    background-color: #deeefe;
    color: #282828;
  }

  .volunteerConfirmList .listTitle {
    text-align: center;
    margin-bottom: 1.5rem;
  }

  .volunteerConfirmList .listTitle > p:first-child {
    font-size: 1.5rem;
  }

  .volunteerConfirmList .listTitle > p:last-child {
    font-size: 1.25rem;
    margin-top: 1rem;
  }
</style>
