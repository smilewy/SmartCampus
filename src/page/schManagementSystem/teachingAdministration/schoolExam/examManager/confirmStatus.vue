<template>
  <div class="confirmStatus">
    <el-row>
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <span class="breadcrumb"><span class="breadcrumb_active">各班确认情况</span><router-link
        :to="{name:'referenceConfirm',params:{examinationid:selectParam.examinationid}}" tag="span">学生确认参考</router-link></span>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
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
      </el-col>
      <el-col :span="6">
        <div class="g-fuzzyInput">
          <el-input
            placeholder="请输入关键字"
            suffix-icon="el-icon-search"
            v-model="selectParam.screen"
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
          prop="branch"
          min-width="120"
          label="科类" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="className"
          min-width="120"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="all"
          min-width="120"
          label="班级人数" sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="150"
          prop="participate"
          label="参考人数" sortable="custom">
        </el-table-column>
        <el-table-column
          min-width="150"
          prop="unparticipate"
          label="不参考人数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="reported"
          min-width="150"
          label="上报人数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="unreported"
          min-width="150"
          label="不上报人数" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="teacher"
          min-width="150"
          label="班主任" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="confirm"
          min-width="160"
          label="班主任是否确认" sortable="custom">
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableData.length!=0">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="selectParam.page"
        :page-size="selectParam.limit"
        layout="prev, pager, next, jumper"
        :total="totalNum">
      </el-pagination>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        tableData: [],
        selectParam: {
          page: 1,
          limit: 50,
          field: '',
          examinationid: '',
          screen: '',
          order: ''
        },
        totalNum: 0,
        loading: false
      }
    },
    created: function () {
      this.selectParam.examinationid = this.$route.params.examinationid;
      this.loadData(this.selectParam);
    },
    methods: {
      returnFlowchart() {
        this.$router.push('/examManagerHome');
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.field = '';
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      sort(column) {
        this.selectParam.field = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      operationData(type){
        let sAy = [], hdData = {
          branch: '科类',
          className: '班级',
          all: '班级人数',
          participate: '参考人数',
          unparticipate: '不参考人数',
          reported: '上报人数',
          unreported: '不上报人数',
          teacher: '班主任',
          confirm: '班主任是否确认'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.confirmStatus', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      loadData(param) {
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Examination/exmanagement/type/confirm/typename/attendselect', 'post', param, function (res) {
          self.loading = false;
          self.tableData = res.data;
          self.totalNum = Number.parseInt(res.page.count);
        })
      }
    }
  }
</script>
<style>
</style>
