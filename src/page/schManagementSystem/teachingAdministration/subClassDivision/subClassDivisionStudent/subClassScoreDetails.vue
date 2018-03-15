<template>
  <div class="subClassScoreDetails">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <h3>分班成绩查询</h3>
      <span class="breadcrumb"><router-link
        :to="{name:'subClassScoreQuery',params:{planId:selectParam.planId}}"
        tag="span">成绩汇总</router-link><span>|</span><span class="breadcrumb_active">成绩明细</span></span>
    </el-row>
    <el-row>
      <el-form :inline="true" :model="selectParam" class="searchConditions">
        <el-form-item label="分班方案：">
          <el-select @change="getProgramme"
                     v-model="selectParam.planId" placeholder="请选择">
            <el-option
              v-for="item in planList"
              :key="item.id"
              :label="item.name"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-button-group>
        <el-button class="filt" title="复制" @click="operationData('copy')">
          <img class="filt_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
               alt="">
          <img class="filt_active"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
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
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        style="width: 100%"
        border
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          prop="exam"
          min-width="100"
          label="考试">
        </el-table-column>
        <el-table-column
          min-width="100"
          prop="subject"
          label="科目">
        </el-table-column>
        <el-table-column
          prop="score"
          min-width="100"
          label="分数">
        </el-table-column>
        <el-table-column
          prop="rank"
          min-width="100"
          label="排名">
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
        planList: [],
        tableData: [],
        selectParam: {
          planId: '',
          whether: 2
        },
        loading: false
      }
    },
    created: function () {
      var self = this, data = {
        func: 'getBelong'
      };
      req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
        self.planList = res.data;
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      },
      getProgramme(){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/DivideBranch/personScore', 'post', self.selectParam, function (res) {
          self.tableData = res.data;
          self.loading = false;
        })
      },
      operationData(type) {
        let sAy = [], hdData;
        hdData = {
          exam: '考试',
          subject: '科目',
          score: '分数',
          rank: '排名'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if (name == 'exam' || name == 'subject') {
              d[name] = obj[name] || '';
            } else {
              d[name] = obj[name];
            }
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.subClassScoreDetails', sAy);
        } else {
          req.lodop(sAy);
        }
      }
    }
  }
</script>
<style>
  .subClassScoreDetails {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .subClassScoreDetails h3 {
    font-size: 1.25rem;
  }

  .subClassScoreDetails .subClassDivision_title {
    margin-bottom: 2rem;
  }

  .subClassScoreDetails .alertsBtn {
    margin: 2rem 0 1.25rem;
  }

  .subClassScoreDetails .el-table td, .subClassScoreDetails .el-table th {
    text-align: center;
  }

  .subClassScoreDetails .el-table--border td {
    border-right: 0;
  }

  .subClassScoreDetails .invokeMsg .el-dialog__footer {
    text-align: right;
  }

  .subClassScoreDetails .breadcrumb {
    font-size: 18px;
  }

  .subClassScoreDetails .breadcrumb > span {
    margin-left: 2rem;
    color: #999999;
    cursor: pointer;
  }

  .subClassScoreDetails .breadcrumb .breadcrumb_active {
    color: #4da1ff;
  }

  .subClassScoreDetails .el-table--border td {
    border-right: 0;
  }

  .subClassScoreDetails .el-select.subject {
    width: 8.25rem;
  }

  .subClassScoreDetails .el-select.major {
    width: 10.75rem;
  }

  .subClassScoreDetails .searchConditions .el-button {
    border-radius: 20px;
    padding: 10px 30px;
  }

  .subClassScoreDetails .el-form--inline .el-form-item {
    margin-right: 2rem;
  }
</style>
