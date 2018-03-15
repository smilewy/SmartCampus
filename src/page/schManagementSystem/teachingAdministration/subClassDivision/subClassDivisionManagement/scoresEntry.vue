<template>
  <div class="scoresEntry">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回上一步</span></el-button>
      <h3>成绩录入</h3>
    </el-row>
    <el-row type="flex" align="middle" class="subClassDivision_row">
      <el-col :span="18" class="fileMsg">
        <el-row type="flex" align="middle">
          <span>设置最大分数值：</span>
          <el-input v-model.number="maxScore" placeholder="" class="scores"></el-input>
          <span class="l_span">文件路径：</span>
          <el-input readonly v-model="filePath.name" placeholder="" class="fileName"></el-input>
          <div class="uploadFile">
            <el-button type="primary">
              <img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_choice.png"
                   alt="">
              <span>选择文件</span>
            </el-button>
            <input type="file" accept=".xlsx,.xlsm,.xls" class="file_input" @change="sendFile">
          </div>
          <el-button type="primary" @click="operationData('out')">
            <img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_download.png"
                 alt="">
            <span>下载模板</span>
          </el-button>
        </el-row>
      </el-col>
      <el-col :span="6" class="saveFile">
        <el-button type="primary" @click="save">保存</el-button>
      </el-col>
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
          type="index"
          label="序号"
          width="100">
        </el-table-column>
        <el-table-column
          prop="preGrade"
          label="年级">
        </el-table-column>
        <el-table-column
          prop="preClass"
          label="班级">
        </el-table-column>
        <el-table-column
          prop="name"
          label="姓名">
        </el-table-column>
        <el-table-column
          prop="sex"
          label="性别">
        </el-table-column>
        <el-table-column
          prop="subScore"
          label="全卷（分）">
          <template slot-scope="scope">
            <input class="scoreInput" type="number" v-model="scope.row.subScore">
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableAllData.length!=0">
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

  export default {
    data() {
      return {
        tableData: [],
        tableAllData: [],
        filePath: {
          name: ''
        },
        maxScore: '',
        selectParam: {
          page: 1,
          count: 50,
          planId: '',
          examId: '',
          type: 'record',
          subId: ''
        },
        totalNum: 0,
        loading: false
      }
    },
    created: function () {
      var self = this, data = this.$route.params;
      self.selectParam.planId = data.planId;
      self.selectParam.examId = data.examId;
      self.selectParam.subId = data.subId;
      self.loading = true;
      req.ajaxSend('/school/DivideBranch/scoreManage', 'post', self.selectParam, function (res) {
        self.loading = false;
        self.tableAllData = res.data || [];
        self.maxScore = res.maxPoint;
        self.loadData();
      })
    },
    methods: {
      returnFlowchart() {
        this.$router.go(-1);
      },
      sendFile() {   //上传文件
        var self = this, file = $('.file_input').prop('files')[0], suffix, sAy, len, formData = new FormData();
        if (!file) {
          return false;
        }
        $('.file_input').val('');
        sAy = file.name.split('.');
        len = sAy.length - 1;
        suffix = sAy[len];
        if (suffix != 'xlsx' && suffix != 'xlsm' && suffix != 'xls') {
          this.vmMsgWarning('只能上传xls、xlsx、xlsm格式文件！');
          return false;
        }
        this.filePath = {
          name: file.name,
          file: file
        };
        formData.append('planId', this.selectParam.planId);
        formData.append('examId', this.selectParam.examId);
        formData.append('type', 'import');
        formData.append('import', file);
        self.loading = true;
        req.ajaxFile('/school/DivideBranch/scoreManage', 'post', formData, function (res) {
          self.loading = false;
          if (res.status == 1) {
            self.vmMsgSuccess('文件导入成功!');
            self.tableAllData = res.data || [];
            self.maxScore = res.maxPoint;
            self.loadData();
          } else {
            self.vmMsgError(res.msg);
          }
        })
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData();
      },
      operationData(type) {
        let sAy = [], hdData;
        hdData = {
          preGrade: '年级',
          preClass: '班级',
          name: '姓名',
          sex: '性别',
          subScore: '全卷（分）'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if (name != 'subScore') {
              d[name] = obj[name] || '';
            } else {
              d[name] = obj[name];
            }
          }
          sAy.push(d)
        }
        if (type == 'out') {
          var url = '/school/DivideBranch/scoreManage?planId=' + this.selectParam.planId + '&examId=' + this.selectParam.examId + '&type=download';
          req.downloadFile('.scoresEntry', url, 'post');
        } else if (type == 'copy') {
          req.copyTableData('.scoresEntry', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      save() {
        var self = this, data = {
          planId: self.selectParam.planId,
          examId: self.selectParam.examId,
          type: 'save',
          subId: self.selectParam.subId,
          subScore: {},
          maxPoint: self.maxScore
        };
        if (!self.maxScore || !Number.isInteger(self.maxScore)) {
          self.vmMsgWarning('请设置最大分数值，且为数字！');
          return false;
        }
        for (let obj of self.tableAllData) {
          if (obj.subScore) {
            if (obj.subScore > self.maxScore) {
              self.vmMsgWarning('分数不能超过设置的最大值，请检查输入!');
              return false;
            }
            data.subScore[obj.userId] = obj.subScore;
          }
        }
        req.ajaxSend('/school/DivideBranch/scoreManage', 'post', data, function (res) {
          if (res.status == 1) {
            self.vmMsgSuccess('保存成功!');
          } else {
            self.vmMsgError(res.msg);
          }
        })
      },
      loadData() {
        this.totalNum = this.tableAllData.length;
        this.tableData = this.tableAllData.slice((this.selectParam.page - 1) * this.selectParam.count, (this.selectParam.page) * this.selectParam.count);
      }
    }
  }
</script>
<style>
  .scoresEntry .alertsBtn {
    margin: 1.25rem 0;
  }

  .scoresEntry .fileMsg .fileName.el-input {
    width: 15.625rem;
    margin-right: 10px;
  }

  .scoresEntry .fileMsg .scores.el-input {
    width: 6.25rem;
    margin-right: 10px;
  }

  .scoresEntry .fileMsg .el-button {
    padding: 0;
    width: 7.5rem;
    font-size: .875rem;
    height: 30px;
    background-color: #099f9b;
    border-color: #099f9b;
  }

  .scoresEntry .fileMsg .el-input__inner {
    height: 30px;
    font-size: .875rem;
  }

  .scoresEntry .uploadFile {
    display: inline-block;
    position: relative;
    margin-right: 10px;
  }

  .scoresEntry .uploadFile .file_input {
    width: 100%;
    height: 30px;
    border-radius: 1.125rem;
    position: absolute;
    right: 0;
    top: 0;
    z-index: 1;
    -moz-opacity: 0;
    -ms-opacity: 0;
    -webkit-opacity: 0;
    opacity: 0; /*css属性——opcity不透明度，取值0-1*/
    filter: alpha(opacity=0); /*兼容IE8及以下--filter属性是IE特有的，它还有很多其它滤镜效果，而filter: alpha(opacity=0); 兼容IE8及以下的IE浏览器(如果你的电脑IE是8以下的版本，使用某些效果是可能会有一个允许ActiveX的提示,注意点一下就ok啦)*/
    cursor: pointer;
  }

  .scoresEntry .l_span {
    margin-left: 2.5rem;
  }

  .scoresEntry .saveFile {
    text-align: right;
  }

  .scoresEntry .saveFile .el-button {
    border-radius: 20px;
    width: 6.25rem;
  }

  .scoresEntry input.scoreInput {
    border: none;
    text-align: center;
    font-family: inherit;
    padding: .2rem 0;
  }
</style>
