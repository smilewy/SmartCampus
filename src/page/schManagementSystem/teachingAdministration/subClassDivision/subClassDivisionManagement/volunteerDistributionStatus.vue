<template>
  <div class="volunteerDistributionStatus">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <span class="breadcrumb"><router-link
        :to="{name:'studentVolunteerCount',params:{planId:selectParam.planId}}"
        tag="span">志愿填报情况</router-link><span>|</span><span class="breadcrumb_active">志愿分布情况</span></span>
    </el-row>
    <el-row class="subClassDivision_row">
      <el-form :inline="true" class="formInline">
        <el-form-item label="统计类型：">
          <el-select v-model="selectParam.genre" placeholder="请选择" class="countType">
            <el-option
              v-for="item in options"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="subClassDivision_row">
      <span>排名分段：</span>
      <span>前 <el-input class="rankData" v-model="rankData.rank1"></el-input> 名</span>
      <span class="fillLeft">前 <el-input class="rankData" v-model="rankData.rank2"></el-input> 名</span>
      <span class="fillLeft">前 <el-input class="rankData" v-model="rankData.rank3"></el-input> 名</span>
      <span class="fillLeft">前 <el-input class="rankData" v-model="rankData.rank4"></el-input> 名</span>
      <span class="fillLeft">前 <el-input class="rankData" v-model="rankData.rank5"></el-input> 名</span>
      <span class="fillLeft">前 <el-input class="rankData" v-model="rankData.rank6"></el-input> 名</span>
      <el-button type="primary" class="scoreQuery_btn fillLeft" @click="search">
        <img src="../../../../../assets/img/schManagementSystem/teachingAdministration/studentScores/icon_search.png"
             alt="">
        <span>查询</span>
      </el-button>
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
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column
          prop="grade"
          min-width="120"
          label="年级" sortable v-if="s_genre!='branch'">
        </el-table-column>
        <el-table-column
          prop="class"
          min-width="120"
          label="班级" sortable v-if="s_genre!='branch'">
        </el-table-column>
        <el-table-column
          prop="branch"
          min-width="150"
          label="科类" sortable>
        </el-table-column>
        <el-table-column
          prop="major"
          min-width="150"
          label="专业" sortable>
        </el-table-column>
        <el-table-column
          :prop="headData.id"
          min-width="150"
          :label="headData.name" sortable v-for="(headData,ix) in tableHeadData" :key="ix" v-if="headData.id">
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
        options: [{
          value: 'class',
          label: '各班填报志愿成绩分布情况统计表'
        }, {
          value: 'branch',
          label: '各科类填报志愿成绩分布情况统计表'
        }],
        s_genre: 'class',
        tableData: [],
        tableHeadData: {},
        rankData: {
          rank1: '',
          rank2: '',
          rank3: '',
          rank4: '',
          rank5: '',
          rank6: '',
        },
        selectParam: {
          planId: '',
          sort: 2,
          genre: '',
          segment: []
        },
        totalNum: 0,
        loading: false
      }
    },
    created: function () {
      this.selectParam.planId = this.$route.params.planId;
    },
    methods: {
      returnFlowchart() {
        this.$router.push('/subClassDivisionHome');
      },
      search() {
        this.selectParam.segment = [];
        for (let name in this.rankData) {
          this.selectParam.segment.push(this.rankData[name]);
        }
        if (!this.selectParam.genre) {
          this.vmMsgWarning('请选择统计类型！');
          return false;
        }
        this.s_genre = this.selectParam.genre;
        this.loadData(this.selectParam);
      },
      operationData(type) {
        let sAy = [], hdData;
        hdData = {
          grade: '年级',
          class: '班级',
          branch: '科类',
          major: '专业'
        };
        for (let obj of this.tableHeadData) {
          hdData[obj.id] = obj.name;
        }
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.volunteerDistributionStatus', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      loadData(data) {
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/DivideBranch/wishStatistics', 'post', data, function (res) {
          self.tableData = res.data || [];
          self.tableHeadData = res.seg;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .volunteerDistributionStatus .el-button.scoreQuery_btn {
    padding: 0;
    height: 30px;
    border-radius: 15px;
    width: 100px;
    font-size: .875rem;
  }

  .volunteerDistributionStatus .countType {
    width: 16.875rem;
  }

  .volunteerDistributionStatus .alertsBtn {
    margin: 1.5rem 0;
  }

  .volunteerDistributionStatus .fillLeft {
    margin-left: 2.5rem;
  }

  .volunteerDistributionStatus .rankData {
    width: 3.75rem;
  }

  .volunteerDistributionStatus .rankData .el-input__inner {
    height: 30px;
  }

  .volunteerDistributionStatus .formInline .el-form-item {
    margin-right: 2.5rem;
    margin-bottom: 0;
  }
</style>
