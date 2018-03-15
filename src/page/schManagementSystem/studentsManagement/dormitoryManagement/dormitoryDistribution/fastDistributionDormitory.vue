<template>
  <div class="distributionDormitoryMsg">
    <el-row type="flex" align="middle">
      <el-col :span="16">
        <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
          src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
          alt=""><span class="returnTxt">返回流程图</span></el-button>
        <h3>快速分配宿舍</h3>
      </el-col>
      <el-col :span="8" class="save">
        <el-button type="primary" @click="save">保存</el-button>
      </el-col>
    </el-row>
    <el-row class="distributionPersonnel_head">
      <span>成绩规则：</span>
      <el-select class="ruleWith" v-model="selectParam.scoreOrder" placeholder="请选择" @change="isSelectExam">
        <el-option label="按总分升序" value="asc"></el-option>
        <el-option label="按总分降序" value="desc"></el-option>
        <el-option label="随机" value="random"></el-option>
      </el-select>
      <span class="d_gap">考试：</span>
      <el-select class="ruleWith" v-model="selectParam.examId" :disabled="selectParam.scoreOrder=='random'"
                 placeholder="请选择">
        <el-option
          v-for="item in testList"
          :key="item.examId"
          :label="item.examination"
          :value="item.examId">
        </el-option>
      </el-select>
      <span class="d_gap">班级规则：</span>
      <el-select class="ruleWith" v-model="selectParam.classOrder" placeholder="请选择">
        <el-option label="按班级升序" value="asc"></el-option>
        <el-option label="按班级降序" value="desc"></el-option>
        <el-option label="随机" value="random"></el-option>
      </el-select>
      <el-select class="ruleWith d_gap_sm" v-model="selectParam.rule" placeholder="请选择">
        <el-option label="班级不交叉" value="notCross"></el-option>
        <el-option label="班级连续" value="continue"></el-option>
        <el-option label="班级成员统一另外分" value="remnant"></el-option>
      </el-select>
      <el-button type="primary" class="d_gap distributionBtn" @click="distributeDorm">分配宿舍</el-button>
      <span class="distributionRule d_gap_sm" @click="dialogVisible=true">查看宿舍分配规则</span>
    </el-row>
    <el-row class="d_line distributionPersonnel_row"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-button class="filt" title="导出" @click="operationData('export')">
        <img class="filt_unactive"
             src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
             alt="">
        <img class="filt_active"
             src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
             alt="">
      </el-button>
      <el-button-group class="secBtn-group">
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
          type="index"
          width="80"
          label="序号">
        </el-table-column>
        <el-table-column
          prop="stuName"
          min-width="120"
          label="姓名">
        </el-table-column>
        <el-table-column
          prop="sex"
          min-width="100"
          label="性别">
        </el-table-column>
        <el-table-column
          prop="grade"
          min-width="120"
          label="年级">
        </el-table-column>
        <el-table-column
          prop="class"
          min-width="120"
          label="班级">
        </el-table-column>
        <el-table-column
          prop="phone"
          min-width="120"
          label="手机号">
        </el-table-column>
        <el-table-column
          prop="name"
          min-width="150"
          label="宿舍楼名称">
        </el-table-column>
        <el-table-column
          prop="number"
          min-width="120"
          label="栋号">
        </el-table-column>
        <el-table-column
          prop="floor"
          min-width="120"
          label="楼层">
        </el-table-column>
        <el-table-column
          prop="dormNumber"
          min-width="120"
          label="宿舍号">
        </el-table-column>
        <el-table-column
          prop="teaName"
          min-width="120"
          label="生活老师">
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableData.length!=0">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="pageData.page"
        :page-size="pageData.count"
        layout="prev, pager, next, jumper"
        :total="totalNum">
      </el-pagination>
    </el-row>
    <el-dialog
      title="宿舍分配规则"
      :modal="false"
      :visible.sync="dialogVisible"
      :before-close="handleClose">
      <p>
        1、宿舍分配默认男女必须分开。<br><br>
        2、请先选择成绩规则、班级规则。若按成绩分，请从成绩规则里的“按总分降序”、“按总分升序”及“考试”里的某次考试选择出来。若按班级分，请先去本系统的“分科分班”模块中完成分班，再从班级规则里选择一项。若综合成绩和班级分配宿舍，请选择所需的成绩规则和班级规则。<br><br>
        3、班级规则选择按班级升序或按班级降序后：<br>
        （1）班级不交叉：假如宿舍分配后，每个宿舍可容纳6人，而1班剩余5人，则此5人单独住一个宿舍；2班剩余1人，则此1人单独住一个宿舍，以此类推。<br><br>
        （2）连续不间断分配：在宿舍分配时，假如1班剩余5人，则与2班刚开始参与分配宿舍的第1人共同分配至一个宿舍；2班分完后剩余1人，则与3班刚开始参与分配宿舍的第1人至第5人共同分配至一个宿舍，以此类推。<br><br>
        （3）班级剩余人员统一另分： 假如1班剩余5人，2班剩余1人，3班剩余4人，4班剩余2人...在宿舍分配结束后，再来统一分配各班的剩余人员的宿舍。<br><br>
        4、若无固定的宿舍分配方式，成绩规则可选择“随机”，班级规则也选择“随机”。
      </p>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
  </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        tableData: [],
        tableAllData: [],
        pageData: {
          page: 1,
          count: 10
        },
        totalNum: 0,
        selectParam: {
          planId: '',
          type: 'quickAssign',
          scoreOrder: '',
          examId: '',
          classOrder: '',
          rule: ''
        },
        testList: [],
        dialogVisible: false,
        loading: false
      }
    },
    created: function () {
      var self = this, data = {
        func: 'getExam'
      }, data1 = {
        planId: self.$route.params.planId
      };
      self.selectParam.planId = self.$route.params.planId;
      //请求考试列表
      req.ajaxSend('/school/StudentDorm/common', 'post', data, function (res) {
        self.testList = res.data;
      });
      //请求学生列表
      self.loading = true;
      req.ajaxSend('/school/StudentDorm/quickAssign', 'post', data1, function (res) {
        self.loading = false;
        self.tableAllData = res.data;
        self.loadData();
        if (res.number > 0) {
          self.vmMsgWarning('参与宿舍分配的学生 < 宿舍容纳人数');
        }
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.go(-1);
      },
      operationData(type){
        let sAy = [], hdData;
        hdData = {
          stuName: '姓名',
          sex: '性别',
          grade: '年级',
          class: '班级',
          phone: '手机号',
          name: '宿舍楼名称',
          number: '栋号',
          floor: '楼层',
          dormNumber: '宿舍号',
          teaName: '生活老师'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'export') {
          req.downloadFile('.distributionDormitoryMsg', '/school/StudentDorm/quickAssign?export=ensure&planId=' + this.selectParam.planId, 'post');
        } else if (type == 'copy') {
          req.copyTableData('.distributionDormitoryMsg', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      isSelectExam(){
        if (this.selectParam.scoreOrder == 'random') {
          this.selectParam.examId = '';
        }
      },
      distributeDorm(){  //分配宿舍
        var self = this;
        if (!self.selectParam.scoreOrder) {
          self.vmMsgWarning('请选择成绩规则！');
          return false;
        }
        if (!self.selectParam.classOrder) {
          self.vmMsgWarning('请选择班级规则！');
          return false;
        }
        self.loading = true;
        req.ajaxSend('/school/StudentDorm/quickAssign', 'post', self.selectParam, function (res) {
          self.loading = false;
          self.tableAllData = res;
          self.pageData.page = 1;
          self.loadData();
          if (res.number > 0) {
            self.vmMsgWarning('参与宿舍分配的学生 < 宿舍容纳人数');
          }
        })
      },
      handleCurrentChange(val) {
        this.pageData.page = val;
        this.loadData();
      },
      save(){
        var self = this, data = {
          planId: self.selectParam.planId,
          type: 'save',
          assign: []
        };
        for (let obj of self.tableData) {
          let d = {
            id: obj.id,
            dormId: obj.dormId,
            isAssign: obj.isAssign
          };
          data.assign.push(d);
        }
        req.ajaxSend('/school/StudentDorm/quickAssign', 'post', data, function (res) {
          if (res.status == 1) {
            self.vmMsgSuccess('保存成功！');
          } else {
            self.vmMsgError(res.msg);
          }
        })
      },
      handleClose(done) {
        done();
      },
      loadData(){
        this.totalNum = this.tableAllData.length;
        this.tableData = this.tableAllData.slice((this.pageData.page - 1) * this.pageData.count, (this.pageData.page) * this.pageData.count);
      }
    }
  }
</script>
<style>
  .distributionDormitoryMsg .distributionPersonnel_head {
    margin-top: 2rem;
  }

  .distributionDormitoryMsg .d_gap {
    margin-left: 2rem;
  }

  .distributionDormitoryMsg .d_gap_sm {
    margin-left: 1rem;
  }

  .distributionDormitoryMsg .distributionPersonnel_row {
    margin: 1.25rem 0;
  }

  .distributionDormitoryMsg .save .el-button {
    padding: 10px 2.5rem;
    border-radius: 20px;
    float: right;
  }

  .distributionDormitoryMsg .distributionRule {
    color: #4da1ff;
    font-size: .875rem;
    cursor: pointer;
    text-decoration: underline;
  }

  .distributionDormitoryMsg .ruleWith {
    width: 9.375rem;
  }

  .distributionDormitoryMsg .distributionBtn {
    padding: 10px 2rem;
    border-radius: 20px;
  }

  .distributionDormitoryMsg .el-dialog--small {
    width: 600px;
  }

  .distributionDormitoryMsg .el-dialog__footer {
    -webkit-box-shadow: 0 -5px 20px -5px #d2d2d2;
    -moz-box-shadow: 0 -5px 20px -5px #d2d2d2;
    box-shadow: 0 -5px 20px -5px #d2d2d2;
  }
</style>
