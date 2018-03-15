<template>
  <div class="subClassScoreQuery">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <h3>分班成绩查询</h3>
      <span class="breadcrumb"><span class="breadcrumb_active">成绩汇总</span><span>|</span><router-link
        :to="{name:'subClassScoreDetails',params:{planId:selectParam.planId}}"
        tag="span">成绩明细</router-link></span>
    </el-row>
    <el-row class="subClassDivisionResults_row">
      <el-form :inline="true">
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
          prop="branch"
          label="科类">
        </el-table-column>
        <el-table-column
          prop="major"
          label="专业">
        </el-table-column>
        <el-table-column
          prop="score"
          label="合成总分">
        </el-table-column>
        <!--<el-table-column
          prop="rank"
          label="科类排名">
        </el-table-column>-->
        <el-table-column
          prop="rank"
          label="专业排名">
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
        tableData: [],
        planList: [],
        selectParam: {
          planId: '',
          whether: 1
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
          branch: '科类',
          major: '专业',
          score: '合成总分',
          rank: '专业排名'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if (name == 'branch' || name == 'major') {
              d[name] = obj[name] || '';
            } else {
              d[name] = obj[name];
            }
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.subClassScoreQuery', sAy);
        } else {
          req.lodop(sAy);
        }
      }
    }
  }
</script>
<style>
  .subClassScoreQuery {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .subClassScoreQuery h3 {
    font-size: 1.25rem;
  }

  .subClassScoreQuery .subClassDivision_title {
    margin-bottom: 2rem;
  }

  .subClassScoreQuery .alertsBtn {
    margin: 2rem 0 1.25rem;
  }

  .subClassScoreQuery .el-table td, .subClassScoreQuery .el-table th {
    text-align: center;
  }

  .subClassScoreQuery .el-table--border td {
    border-right: 0;
  }

  .subClassScoreQuery .invokeMsg .el-dialog__footer {
    text-align: right;
  }

  .subClassScoreQuery .breadcrumb {
    font-size: 18px;
  }

  .subClassScoreQuery .breadcrumb > span {
    margin-left: 2rem;
    color: #999999;
    cursor: pointer;
  }

  .subClassScoreQuery .breadcrumb .breadcrumb_active {
    color: #4da1ff;
  }
</style>
