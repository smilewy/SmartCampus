<template>
  <div class="dormitoryDistribution">
    <el-row type="flex" align="middle">
      <el-col :span="12">
        <h3>宿舍分配</h3>
      </el-col>
      <el-col :span="12" class="createDistribution">
        <el-button type="primary" @click="operation('create')">创建分配方案</el-button>
      </el-col>
    </el-row>
    <el-row class="d_line dormitoryDistribution_row"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button class="delete" title="打印" @click="operationData">
          <img class="delete_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
               alt="">
          <img class="delete_active"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
               alt="">
        </el-button>
      </el-col>
      <el-col :span="6">
        <div class="g-fuzzyInput">
          <el-input
            placeholder="请输入方案名称和状态"
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
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          type="index"
          label="序号"
          width="100">
        </el-table-column>
        <el-table-column
          prop="name"
          min-width="150"
          label="方案名称">
        </el-table-column>
        <el-table-column
          prop="createTime"
          min-width="150"
          label="创建时间">
          <template slot-scope="scope">
            {{scope.row.createTime|formatDate}}
          </template>
        </el-table-column>
        <el-table-column
          prop="dormNumber"
          min-width="150"
          label="分配宿舍数">
        </el-table-column>
        <el-table-column
          prop="stuNumber"
          min-width="100"
          label="人数">
        </el-table-column>
        <el-table-column
          prop="currentStatus"
          min-width="150"
          label="方案状态">
        </el-table-column>
        <el-table-column
          width="350"
          label="操作">
          <template slot-scope="scope">
            <span class="operation edit" @click="operation('process',scope.$index)">宿舍分配</span>
            <span class="operation edit" @click="operation('edit',scope.$index)">编辑</span>
            <span class="operation delete" @click="operation('delete',scope.$index)">删除</span>
            <span class="operation delete" @click="operation('clear',scope.$index)">清空人员</span>
          </template>
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
          key: '',
          page: 1,
          count: 50
        },
        totalNum: 0,
        loading: false
      }
    },
    created: function () {
      this.loadData(this.selectParam);
    },
    methods: {
      goSearch() {  //查询
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val){
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      operation(type, idx){   //操作单条数据
        var self = this, data;
        if (type == 'create') {
          self.$router.push({name: 'createDistribution'});
        } else if (type == 'edit') {
          self.$router.push({name: 'editDistribution', params: {id: self.tableData[idx].id}});
        } else if (type == 'process') {
          self.$router.push({name: 'distributionProcess', params: {id: self.tableData[idx].id}});
        } else if (type == 'delete') {
          data = {
            type: 'del',
            planId: self.tableData[idx].id
          };
          self.$confirm('确定删除该条记录?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/StudentDorm/dormPlan', 'post', data, function (res) {
              if (res.status == 1) {
                self.vmMsgSuccess('删除成功!');
                self.loadData(self.selectParam);
              } else {
                self.vmMsgError(res.msg);
              }
            })
          }).catch(() => {
          });
        } else if (type == 'clear') {
          data = {
            type: 'clean',
            planId: self.tableData[idx].id
          };
          self.$confirm('确定清空该宿舍人员?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/StudentDorm/dormPlan', 'post', data, function (res) {
              if (res.status == 1) {
                self.vmMsgSuccess('清空成功!');
                self.loadData(self.selectParam);
              } else {
                self.vmMsgError(res.msg);
              }
            })
          }).catch(() => {
          });
        }
      },
      operationData(type) {
        let sAy = [], hdData;
        hdData = {
          name: '方案名称',
          createTime: '创建时间',
          dormNumber: '分配宿舍数',
          stuNumber: '人数',
          currentStatus: '方案状态'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        req.lodop(sAy);
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/StudentDorm/dormPlan', 'post', data, function (res) {
          self.loading = false;
          self.tableData = res.data;
          self.totalNum = res.total;
        })
      }
    }
  }
</script>
<style>
  .dormitoryDistribution .dormitoryDistribution_row {
    margin-top: 2rem;
  }

  .dormitoryDistribution .createDistribution .el-button {
    border-radius: 20px;
    float: right;
  }

  .dormitoryDistribution .operation {
    padding: 0 16px;
    cursor: pointer;
  }

  .dormitoryDistribution .operation + .operation {
    border-left: 2px solid #d2d2d2;
  }

  .dormitoryDistribution .operation.edit {
    color: #4da1ff;
  }

  .dormitoryDistribution .operation.delete {
    color: #ff5b5a;
  }
</style>
