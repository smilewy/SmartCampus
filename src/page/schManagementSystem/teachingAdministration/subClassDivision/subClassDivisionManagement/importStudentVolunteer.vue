<template>
  <div class="importStudentVolunteer">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回上一步</span></el-button>
      <h3>批量导入志愿</h3>
    </el-row>
    <el-row type="flex" align="middle" class="subClassDivision_row fileMsg">
      <span>文件路径：</span>
      <el-input readonly v-model="filePath.name" placeholder=""></el-input>
      <div class="uploadFile">
        <el-button type="primary">
          <img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_choice.png"
               alt="">
          <span>选择文件</span>
        </el-button>
        <input type="file" accept=".xlsx,.xlsm,.xls" class="file_input" @change="sendFile">
      </div>
      <el-button type="primary" @click="operationData('downloadTemplate')">
        <img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_download.png"
             alt="">
        <span>下载模板</span>
      </el-button>
    </el-row>
    <el-row class="bulkImport_btn">
      <el-button type="primary" @click="operationData('upload')">
        <img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_upload.png"
             alt="">
        <span>上传</span>
      </el-button>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-col :span="18">
        <el-button class="filt" title="复制" @click="operationData('copy')">
          <img class="filt_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"
               alt="">
          <img class="filt_active"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"
               alt="">
        </el-button>
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
          prop="preGrade"
          label="年级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="preClass"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="name"
          label="姓名" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="branch"
          label="科类" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="major"
          label="专业" sortable="custom">
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
          planId: '',
          page: 1,
          count: 50,
          key: '',
          according: '',
          order: ''
        },
        totalNum: 0,
        filePath: {
          name: ''
        },
        loading: false
      }
    },
    created: function () {
      this.selectParam.planId = this.$route.params.planId;
      this.loadData(this.selectParam);
    },
    methods: {
      returnFlowchart(){
        this.$router.go(-1);
      },
      sendFile(){   //上传文件
        var file = $('.file_input').prop('files')[0], suffix, sAy, len;
        if (!file) {
          return false;
        }
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
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.order = '';
        this.selectParam.according = '';
        this.loadData(this.selectParam);
      },
      sort(column){
        this.selectParam.order = column.order || '';
        this.selectParam.according = column.prop || '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      operationData(type){
        var self = this, formData = new FormData();
        let sAy = [], hdData;
        hdData = {
          preGrade: '年级',
          preClass: '班级',
          name: '姓名',
          branch: '科类',
          major: '专业'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'downloadTemplate') {
          req.downloadFile('.importStudentVolunteer', '/school/DivideBranch/wishAdjust?planId=' + self.selectParam.planId + '&type=download', 'post');
        } else if (type == 'upload') {
          formData.append('planId', self.selectParam.planId);
          formData.append('type', 'import');
          formData.append('import', $('.file_input').prop('files')[0]);
          if (!self.filePath.name) {
            self.vmMsgWarning('请先下载模板并选择文件再上传！');
            return false;
          }
          req.ajaxFile('/school/DivideBranch/wishAdjust', 'post', formData, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('上传成功！');
              self.selectParam.page = 1;
              self.selectParam.order = '';
              self.selectParam.according = '';
              self.loadData(self.selectParam);
            } else {
              self.vmMsgError(res.msg);
            }
          })
        } else if (type == 'copy') {
          req.copyTableData('.importStudentVolunteer', sAy);
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/DivideBranch/wishAdjust', 'post', data, function (res) {
          self.tableData = res.data;
          self.totalNum = res.total;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .importStudentVolunteer .alertsBtn {
    margin: 1.25rem 0;
  }

  .importStudentVolunteer .fileMsg .el-input {
    width: 15.625rem;
    margin-right: 10px;
  }

  .importStudentVolunteer .fileMsg .el-button {
    padding: 0;
    width: 7.5rem;
    font-size: .875rem;
    height: 30px;
    background-color: #099f9b;
    border-color: #099f9b;
  }

  .importStudentVolunteer .fileMsg .el-input__inner {
    height: 30px;
    font-size: .875rem;
  }

  .importStudentVolunteer .uploadFile {
    display: inline-block;
    position: relative;
    margin-right: 10px;
  }

  .importStudentVolunteer .uploadFile .file_input {
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

  .importStudentVolunteer .bulkImport_btn {
    text-align: right;
    margin: 1.25rem 0;
  }

  .importStudentVolunteer .bulkImport_btn .el-button {
    padding: 0;
    height: 30px;
    border-radius: 15px;
    width: 100px;
    font-size: .875rem;
  }
</style>
