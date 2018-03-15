<template>
  <div class="personnelDetail">
    <el-row class="d_line personnelDetail_row"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button-group>
          <el-button class="filt" title="导出" @click="operationData('out')">
            <img class="filt_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
                 alt="">
            <img class="filt_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
                 alt="">
          </el-button>
          <el-button class="delete" title="打印" @click="operationData('print')">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                 alt="">
          </el-button>
        </el-button-group>
      </el-col>
      <el-col :span="6">
        <div class="g-fuzzyInput">
          <el-input
            placeholder="请输入关键字"
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
        @sort-change="sort"
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
          label="宿舍楼名称"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="number"
          min-width="120"
          label="栋号"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="floor"
          min-width="120"
          label="楼层"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="dormNumber"
          min-width="150"
          label="宿舍号"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="dormName"
          min-width="150"
          label="宿舍名称"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="dormType"
          min-width="120"
          label="宿舍类型"
          sortable="custom">
          <template slot-scope="scope">
            <span v-if="scope.row.dormType==1">女生宿舍</span>
            <span v-if="scope.row.dormType==2">男生宿舍</span>
            <span v-if="scope.row.dormType==3">混合宿舍</span>
            <span v-if="scope.row.dormType==4">其他</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="capacity"
          min-width="120"
          label="容纳人数"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="real"
          min-width="120"
          label="实住人数"
          sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="180"
          label="实住人员名单"
          sortable="custom">
          <template slot-scope="scope">
            <span v-for="(stu,ix) in scope.row.stuName" :key="ix">{{stu}} </span>
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
          option: 2,
          page: 1,
          count: 50,
          according: '',
          order: '',
          key: ''
        },
        totalNum: 0,
        loading: false
      }
    },
    created: function () {
      this.loadData(this.selectParam);
    },
    methods: {
      sort(column){
        this.selectParam.order = column.order || '';
        this.selectParam.according = column.prop || '';
        this.loadData(this.selectParam);
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.according = '';
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val){
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      operationData(type){
        let sAy = [], hdData;
        hdData = {
          name: '宿舍楼名称',
          number: '栋号',
          floor: '楼层',
          dormName: '宿舍名称',
          dormType: '宿舍类型',
          capacity: '容纳人数',
          real: '实住人数',
          stuName: '实住人员名单'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if (name == 'dormType') {
              switch (obj[name]) {
                case '1':
                  d[name] = '女生宿舍';
                  break;
                case '2':
                  d[name] = '男生宿舍';
                  break;
                case '3':
                  d[name] = '混合宿舍';
                  break;
                case '4':
                  d[name] = '其他';
                  break;
              }
            }
            if (name == 'stuName') {
              let st = '';
              for (let n in obj[name]) {
                st += n + ' ';
              }
              d[name] = st;
            } else {
              d[name] = obj[name] || '';
            }
          }
          sAy.push(d)
        }
        if (type == 'out') {
          req.downloadFile('.personnelDetail', '/school/StudentDorm/stuManage?option=2&export=ensure', 'post');
        } else {
          req.lodop(sAy);
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/StudentDorm/stuManage', 'post', data, function (res) {
          self.tableData = res.data;
          self.totalNum = res.total;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .personnelDetail .personnelDetail_row {
    margin-top: 2rem;
  }

  .personnelDetail .alertsBtn {
    margin: 1.25rem 0;
  }

  .personnelDetail .g-fuzzyInput {
    float: right;
  }

  .personnelDetail .el-table th, .personnelDetail .el-table td {
    text-align: center;
  }
</style>
