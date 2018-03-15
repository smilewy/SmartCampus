<template>
  <div class="bulkImport">
    <h3>批量导入</h3>
    <el-row type="flex" align="middle" class="fileMsg">
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
        <el-button class="delete" title="导出" @click="operationData('out')">
          <img class="delete_unactive"
               src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" alt="">
          <img class="delete_active"
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
      </el-col>
      <el-col :span="6">
        <div class="g-fuzzyInput">
          <el-input
            placeholder="请输入名字/科目"
            suffix-icon="el-icon-search"
            v-model="selectParam.value"
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
          label="姓名"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="sex"
          label="性别"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="jobNumber"
          label="工号"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="teachingSubjects"
          label="任教科目"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="politics"
          label="政治面貌"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="phone"
          label="手机号码">
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="pageAlerts" v-if="tableData.length!=0">
      <el-pagination
        @current-change="handleCurrentChange"
        :current-page.sync="selectParam.page"
        :page-size="selectParam.pageSize"
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
        filePath: {},
        tableData: [],
        selectParam: {
          page: 1,
          pageSize: 50,
          sort: '',
          sortType: '',
          value: ''
        },
        totalNum: 0,
        loading: false
      }
    },
    created: function () {
      this.loadDataList(this.selectParam);
    },
    methods: {
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
      operationData(type){
        var self = this, formData = new FormData();
        let sAy = [], hdData;
        hdData = {
          name: '姓名',
          sex: '性别',
          jobNumber: '工号',
          teachingSubjects: '任教科目',
          politics: '政治面貌',
          phone: '手机号',
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
          req.downloadFile('.bulkImport', '/school/user/userGl?type=download&downType=teacher', 'post');
        } else if (type == 'upload') {
          formData.append('userFile', $('.file_input').prop('files')[0]);
          if (!self.filePath.name) {
            self.vmMsgWarning('请先下载模板并选择文件再上传！');
            return false;
          }
          req.ajaxFile('/school/user/userGl?type=export&roleNameEn=js', 'post', formData, function (res) {
            if (res.statu == 1) {
              self.vmMsgSuccess('上传成功！');
              self.selectParam.page = 1;
              self.selectParam.sort = '';
              self.selectParam.sortType = '';
              self.loadDataList(self.selectParam);
            } else if (res.statu == 202) {
              self.$confirm(res.message, '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
              }).then(() => {
                let formData1 = new FormData();
                formData1.append('userFile', $('.file_input').prop('files')[0]);
                formData1.append('token', res.token);
                req.ajaxFile('/school/user/allUse?type=uploadGoOn', 'post', formData1, function (res) {
                  if (res.statu == 1) {
                    self.vmMsgSuccess('上传成功!');
                    self.selectParam.page = 1;
                    self.selectParam.sort = '';
                    self.selectParam.sortType = '';
                    self.loadDataList(self.selectParam);
                  } else {
                    self.vmMsgError(res.message);
                  }
                });
              }).catch(() => {
              });
            } else {
              self.vmMsgError(res.message);
            }
          })
        } else if (type == 'out') {
          req.downloadFile('.bulkImport', '/school/user/exportTeacherOrZg?type=teacher', 'post');
        } else if (type == 'copy') {
          req.copyTableData('.bulkImport', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.sort = '';
        this.selectParam.sortType = '';
        this.loadDataList(this.selectParam);
      },
      sort(column){
        this.selectParam.sort = column.order || '';
        this.selectParam.sortType = column.prop || '';
        this.loadDataList(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadDataList(this.selectParam);
      },
      handleClose(done) {   //关闭弹框
        done();
      },
      loadDataList(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/user/userGl?type=teacherList', 'get', data, function (res) {
          self.loading = false;
          self.tableData = res.data;
          self.totalNum = res.count;
        })
      }
    }
  }
</script>
<style>
  .bulkImport {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .bulkImport h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .bulkImport .g-fuzzyInput {
    float: right;
  }

  .bulkImport .alertsBtn {
    margin: 1.25rem 0;
  }

  .bulkImport .alertsList {
    text-align: center;
  }

  .bulkImport .el-table th {
    text-align: center;
  }

  .fileMsg {
    margin-top: 1.875rem;
  }

  .bulkImport_btn {
    text-align: right;
    margin: 1.25rem 0;
  }

  .bulkImport_btn .el-button {
    padding: 0;
    height: 30px;
    border-radius: 15px;
    width: 100px;
    font-size: .875rem;
  }

  .fileMsg .el-input {
    width: 15.625rem;
    margin-right: 10px;
  }

  .fileMsg .el-button {
    padding: 0;
    width: 7.5rem;
    font-size: .875rem;
    height: 30px;
    background-color: #099f9b;
    border-color: #099f9b;
  }

  .fileMsg .el-input__inner {
    height: 30px;
    font-size: .875rem;
  }

  .bulkImport .uploadFile {
    display: inline-block;
    position: relative;
    margin-right: 10px;
  }

  .bulkImport .uploadFile .file_input {
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
</style>
