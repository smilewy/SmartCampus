<template>
  <div class="scoresProve">
    <h3>成绩证明</h3>
    <el-row :gutter="20" class="scoresProve_row">
      <el-col :span="7">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>选择学生：</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入关键字进行过滤"
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
              :filter-node-method="filterNode"
              @node-click="chooseStudent"
              :props="defaultProps">
            </el-tree>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="17">
        <el-row type="flex" align="middle" class="prove_header">
          <span>考试：</span>
          <el-select multiple v-model="selectParam.examinationid" placeholder="请选择">
            <el-option
              v-for="(item,ix) in testList"
              :key="ix"
              :label="item.examination"
              :value="item.examinationid">
            </el-option>
          </el-select>
          <el-button type="primary" icon="el-icon-search" class="select" @click="searchScore">查询</el-button>
        </el-row>
        <el-row class="d_line"></el-row>
        <el-row class="prove_body">
          <el-row type="flex" align="middle" class="alertsBtn">
            <el-button class="delete" title="导出" @click="operationData('out')">
              <img class="delete_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
                   alt="">
              <img class="delete_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
                   alt="">
            </el-button>
            <el-button-group class="secBtn-group">
              <el-button class="filt" title="复制" @click="operationData('copy')">
                <img class="filt_unactive"
                     src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
                     alt="">
                <img class="filt_active"
                     src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
                     alt="">
              </el-button>
              <el-button class="delete" title="打印" @click="operationData('print')">
                <img class="delete_unactive"
                     src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                     alt="">
                <img class="delete_active"
                     src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                     alt="">
              </el-button>
            </el-button-group>
          </el-row>
          <el-row class="alertsList">
            <el-table
              :data="tableData"
              style="width: 100%"
              v-loading="loading1"
              element-loading-text="拼命加载中"
            >
              <el-table-column
                prop="subjectname"
                min-width="100"
                label="考试">
              </el-table-column>
              <el-table-column
                :prop="headData.name"
                min-width="300"
                :label="headData.examination" v-for="(headData,index) in tableHead" :key="index">
              </el-table-column>
            </el-table>
          </el-row>
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
        tableData: [],
        tableHead: [],
        treeData: [],
        defaultProps: {
          children: 'data',
          label: 'name'
        },
        filterText: '',
        testList: [],
        selectParam: {
          userId: '',
          examinationid: []
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
      req.ajaxSend('/school/Educational/achievementPro?type=getGradeClassStudent', 'get', '', function (res) {
        self.treeData = res.data;
        self.loading = false;
      })
    },
    methods: {
      filterNode(value, data) {
        if (!value) return true;
        if (data.name) {
          data.name = data.name.toString();
          return data.name.indexOf(value) !== -1;
        }
      },
      chooseStudent(node, state, child){
        var self = this, data = {
          gradeId: node.gradeId,
          classId: node.classId
        };
        self.selectParam.userId = node.userId;
        if (!node.data) {
          req.ajaxSend('/school/Educational/achievementPro?type=getTestList', 'get', data, function (res) {
            self.testList = res.data;
          })
        }
      },
      searchScore(){
        this.loadData(this.selectParam);
      },
      operationData(type){
        if (this.selectParam.examinationid.length == 0) {
          this.vmMsgWarning('请选择考试！');
          return false;
        }
        let sAy = [], hdData;
        hdData = {
          subjectname: '考试'
        };
        for (let obj of this.tableHead) {
          hdData[obj.name] = obj.examination
        }
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'out') {
          req.downloadFile('.scoresProve', '/school/Educational/achievementPro?type=exportTestGrade&examinationid=' + this.selectParam.examinationid + '&userId=' + this.selectParam.userId, 'post');
        } else if (type == 'copy') {
          req.copyTableData('.scoresProve', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      loadData(data){
        var self = this;
        self.loading1 = true;
        req.ajaxSend('/school/Educational/achievementPro?type=getTestGrade', 'get', data, function (res) {
          self.tableData = res.data.data;
          self.tableHead = res.data.name;
          self.loading1 = false;
        })
      }
    }
  }
</script>
<style>
  .scoresProve {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .scoresProve h3 {
    font-size: 1.25rem;
  }

  .scoresProve .scoresProve_row {
    margin: 2rem 0;
  }

  .scoresProve .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
    -webkit-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    -moz-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    box-shadow: 0 0 1px 1px #d2d2d2 inset;
  }

  .scoresProve .treeList_body {
    padding: .875rem;
    height: 43rem;
    overflow: auto;
  }

  .scoresProve .treeList_title {
    padding: .875rem .875rem 1.5rem;
    height: 8rem;
  }

  .scoresProve .treeList_title h5 {
    font-size: 1rem;
  }

  .scoresProve .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .scoresProve .treeList .el-tree {
    border: none;
  }

  .scoresProve .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .scoresProve .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  .scoresProve .prove_header {
    margin-bottom: 1.25rem;
    font-size: 14px;
  }

  .scoresProve .prove_header .el-select {
    width: 21.25rem;
  }

  .scoresProve .el-button.select {
    padding: 10px 25px;
    border-radius: 20px;
    margin-left: 2rem;
  }

  .scoresProve .el-table td, .scoresProve .el-table th {
    text-align: center;
  }

  .scoresProve .alertsBtn {
    margin: 1.25rem 0;
  }
</style>
