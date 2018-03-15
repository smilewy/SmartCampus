<template>
  <div class="subClassDivisionRecords">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <h3>分班分科记录</h3>
    </el-row>
    <el-row class="subClassDivisionRecords_row">
      <div class="g-fuzzyInput">
        <el-input
          placeholder="请输入关键字"
          suffix-icon="el-icon-search"
          v-model="selectParam.key"
          @change="goSearch">
        </el-input>
      </div>
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        @sort-change="sort"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          prop="name"
          min-width="120"
          label="方案名称">
        </el-table-column>
        <el-table-column
          min-width="300"
          label="学生填报志愿">
          <template slot-scope="scope">
            {{scope.row.fillStart|formatDate}} 至 {{scope.row.fillEnd|formatDate}}
          </template>
        </el-table-column>
        <el-table-column
          min-width="300"
          label="班主任调志愿">
          <template slot-scope="scope">
            {{scope.row.changeStart|formatDate}} 至 {{scope.row.changeEnd|formatDate}}
          </template>
        </el-table-column>
        <el-table-column
          min-width="200"
          label="报志愿进度">
          <template slot-scope="scope">
            <div class="progress_bar">
              <span class="progress_bar_active"
                    :style="{width: scope.row.rate+'%'}"></span>
              <span class="progress_bar_text">{{Number.parseInt(scope.row.fillNumber)-Number.parseInt(scope.row.notFill)}}/{{Number.parseInt(scope.row.fillNumber)}}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          min-width="150"
          prop="createTime"
          label="创建时间" sortable="custom">
          <template slot-scope="scope">
            {{scope.row.createTime| formatDate}}
          </template>
        </el-table-column>
        <el-table-column
          min-width="80"
          fixed="right"
          label="操作">
          <template slot-scope="scope">
            <span class="delete" @click="deleteData(scope.$index)">删除</span>
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
          page: 1,
          count: 50,
          key: '',
          order: ''
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
        this.selectParam.page = 1;
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      deleteData(idx){
        var self = this, data = {
          type: 'del',
          planId: self.tableData[idx].id
        };
        self.$confirm('确定删除记录?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/DivideBranch/planLog', 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('删除成功！');
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
        req.ajaxSend('/school/DivideBranch/planLog', 'post', data, function (res) {
          self.tableData = res.data||[];
          self.totalNum = res.total;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .subClassDivisionRecords {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .subClassDivisionRecords h3 {
    font-size: 1.25rem;
  }

  .subClassDivisionRecords .subClassDivisionRecords_row {
    margin: 1.25rem 0;
  }

  .subClassDivisionRecords .g-fuzzyInput {
    float: right;
  }

  .subClassDivisionRecords .delete {
    color: #ff5b5b;
    cursor: pointer;
  }

  .subClassDivisionRecords .el-table td, .subClassDivisionRecords .el-table th {
    text-align: center;
  }

  .subClassDivisionRecords .progress_bar {
    background-color: #f0f0f0;
    position: relative;
    height: 22px;
  }

  .subClassDivisionRecords .progress_bar .progress_bar_active {
    display: block;
    position: absolute;
    left: 0;
    top: 0;
    background-color: #13b5b1;
    height: 100%;
    z-index: 1;
  }

  .subClassDivisionRecords .progress_bar .progress_bar_text {
    display: block;
    width: 100%;
    position: absolute;
    z-index: 2;
  }
</style>
