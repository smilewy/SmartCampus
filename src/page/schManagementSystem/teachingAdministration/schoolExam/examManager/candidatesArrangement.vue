<template>
  <div class="testNumberSetting candidatesArrangement">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>考生安排</h3>
    </el-row>
    <el-row class="examManager_row" type="flex" align="middle" justify="space-between">
      <el-col :span="18">
        <el-form :inline="true" class="formInline">
          <el-form-item label="学生排位方式：">
            <el-select v-model="rankConditions.mode" placeholder="请选择">
              <el-option
                v-for="item in testNumbers"
                :key="item.value"
                :label="item.label"
                :value="item.label">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="排位总分：" v-if="rankConditions.mode=='按总分降序排序'">
            <el-select v-model="rankConditions.id" placeholder="请选择">
              <el-option
                v-for="item in examList"
                :key="item.id"
                :label="item.examination"
                :value="item.id">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="generated">
              <img class="rank_img"
                   src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_rank.png"
                   alt="">
              <span>生成排位</span>
            </el-button>
          </el-form-item>
        </el-form>
      </el-col>
      <el-col :span="6" class="rankConditions_isStraight">
        <span>借读生连续排位：</span>
        <el-switch
          v-model="isResSchool"
          active-color="#13b5b1"
          inactive-color="#ff4949"
          active-text="是"
          inactive-text="否">
        </el-switch>
      </el-col>
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
            placeholder="请输入查询字段"
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
          type="index"
          width="120"
          label="班级序号">
        </el-table-column>
        <el-table-column
          prop="className"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="name"
          label="姓名" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="room"
          label="考场" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="seatnumber"
          label="考场座位号" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="branch"
          label="科类" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="number"
          label="考号" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="isResSchool"
          label="是否借读">
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
  export default{
    data(){
      return {
        testNumbers: [{
          value: '0',
          label: '随机排序'
        }, {
          value: '1',
          label: '按座位号顺序'
        }, {
          value: '2',
          label: '按总分降序排序'
        }],
        isResSchool: true,
        rankConditions: {
          mode: '随机排序',
          examinationid: '',
          id: '',
          isResSchool: 1
        },
        tableData: [],
        examList: [],
        totalNum: 0,
        selectParam: {
          page: 1,
          limit: 50,
          field: '',
          examinationid: '',
          order: '',
          screen: ''
        },
        loading: true
      }
    },
    created: function () {
      this.selectParam.examinationid = this.$route.params.examinationid;
      this.rankConditions.examinationid = this.selectParam.examinationid;
      this.loadData(this.selectParam);
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/examManagerHome');
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.field = '';
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.field = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      generated(){
        var self = this;
        self.rankConditions.isResSchool = self.isResSchool ? 1 : 0;
        self.rankConditions.id = self.rankConditions.mode == '按总分降序排序' ? self.rankConditions.id : '';
        req.ajaxSend('/school/Examination/exmanagement/type/arrange/typename/arrangeinsert', 'post', self.rankConditions, function (res) {
          if (res.return) {
            self.vmMsgSuccess('生成成功!');
            self.selectParam.page = 1;
            self.selectParam.field = '';
            self.selectParam.order = '';
            self.loadData(self.selectParam);
          } else {
            self.vmMsgError(res.msg);
          }
        })
      },
      operationData(type){
        let sAy = [], hdData = {
          className: '班级',
          name: '姓名',
          room: '考场',
          seatnumber: '考场座位号',
          branch: '科类',
          number: '考号',
          isResSchool: '是否借读'
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
          req.copyTableData('.candidatesArrangement', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Examination/exmanagement/type/arrange/typename/arrangefind', 'post', data, function (res) {
          self.loading = false;
          self.tableData = res.data||[];
          self.examList = res.examlist;
          self.totalNum = Number.parseInt(res.page.count);
        })
      }
    }
  }
</script>
<style>
  .candidatesArrangement .examManager_row .el-button--primary {
    background-color: #13b5b1;
    border-color: #13b5b1;
    height: 36px;
    padding: 0 15px;
  }

  .candidatesArrangement .rankConditions_isStraight {
    text-align: right;
  }

  .candidatesArrangement .rank_img {
    width: 14px;
  }
</style>
