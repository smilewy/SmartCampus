<template>
  <div class="dormitoryBulkImport">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回</span></el-button>
      <h3>批量导入</h3>
    </el-row>
    <el-row type="flex" align="middle" class="dormitoryBulkImport_row">
      <el-col :span="18" class="fileMsg">
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
      </el-col>
      <el-col :span="6" class="bulkImport_btn">
        <el-button type="primary" @click="operationData('upload')">
          <img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_upload.png"
               alt="">
          <span>上传</span>
        </el-button>
      </el-col>
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
          prop="number"
          label="栋号"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="name"
          label="宿舍楼名称"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="floor"
          label="楼层"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="dormNumber"
          label="宿舍号"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="dormName"
          label="宿舍名称"
          sortable="custom">
        </el-table-column>
        <el-table-column
          prop="dormType"
          label="宿舍类型"
          sortable="custom">
          <template slot-scope="scope">
            <span v-if="scope.row.dormType==1">女生宿舍</span>
            <span v-if="scope.row.dormType==2">男生宿舍</span>
            <span v-if="scope.row.dormType==3">混合宿舍</span>
            <span v-if="scope.row.dormType==4">其他</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="capacity"
          label="容纳人数"
          sortable="custom">
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
        filePath: {},
        tableData: [],
        selectParam: {
          page: 1,
          count: 50,
          key: '',
          according: '',
          order: ''
        },
        totalNum: 0,
        loading: false
      }
    },
    created: function () {
      this.loadDataList(this.selectParam);
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
      operationData(type){
        var self = this;
        var formData = new FormData();
        let sAy = [], hdData;
        hdData = {
          number: '栋号',
          name: '宿舍楼名称',
          floor: '楼层',
          dormNumber: '宿舍号',
          dormType: '宿舍类型',
          capacity: '容纳人数'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if (name == 'dormType') {
              switch (obj[name]) {
                case '1':
                  d[name] = '女生宿舍';
                  break;
                case '2':
                  d[name] = '男生宿舍';
                  break;
                case '3':
                  d[name] = '混合宿舍';
                  break;
                case '4':
                  d[name] = '其他';
                  break;
              }
            } else {
              d[name] = obj[name] || '';
            }
          }
          sAy.push(d)
        }
        if (type == 'downloadTemplate') {
          req.downloadFile('.dormitoryBulkImport', '/school/StudentDorm/dormManage?type=download', 'post');
        } else if (type == 'upload') {
          formData.append('import', $('.file_input').prop('files')[0]);
          formData.append('type', 'import');
          if (!self.filePath.name) {
            self.vmMsgWarning('请先下载模板并选择文件再上传！');
            return false;
          }
          req.ajaxFile('/school/StudentDorm/dormManage', 'post', formData, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('上传成功！');
              self.selectParam.page = 1;
              self.selectParam.order = '';
              self.selectParam.according = '';
              self.loadDataList(self.selectParam);
            } else {
              self.vmMsgError(res.msg);
            }
          })
        } else if (type == 'out') {
          req.downloadFile('.dormitoryBulkImport', '/school/StudentDorm/dormManage?export=ensure', 'post');
        } else if (type == 'copy') {
          req.copyTableData('.dormitoryBulkImport', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      goSearch() {  //查询
        this.selectParam.page = 1;
        this.selectParam.according = '';
        this.selectParam.order = '';
        this.loadDataList(this.selectParam);
      },
      sort(column){
        this.selectParam.order = column.order || '';
        this.selectParam.according = column.prop || '';
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
        req.ajaxSend('/school/StudentDorm/dormManage', 'post', data, function (res) {
          self.tableData = res.data;
          self.totalNum = res.total;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .dormitoryBulkImport {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .dormitoryBulkImport .return_btn.el-button--primary {
    background-color: #ff8686;
    border-color: #ff8686;
    border-radius: 20px;
    padding: 10px 25px;
  }

  .dormitoryBulkImport .return_btn.el-button--primary .returnTxt {
    margin-left: 10px;
  }

  .dormitoryBulkImport .return_btn + h3 {
    font-size: 1.25rem;
    display: inline-block;
    margin-left: 2rem;
  }

  .dormitoryBulkImport .dormitoryBulkImport_row {
    margin: 2rem 0 1.25rem 0;
  }

  .dormitoryBulkImport .g-fuzzyInput {
    float: right;
  }

  .dormitoryBulkImport .alertsBtn {
    margin: 1.25rem 0;
  }

  .dormitoryBulkImport .alertsList {
    text-align: center;
  }

  .dormitoryBulkImport .el-table th {
    text-align: center;
  }

  .dormitoryBulkImport .bulkImport_btn {
    text-align: right;
  }

  .dormitoryBulkImport .bulkImport_btn .el-button {
    padding: 0;
    height: 30px;
    border-radius: 15px;
    width: 100px;
    font-size: .875rem;
  }

  .dormitoryBulkImport .fileMsg .el-input {
    width: 15.625rem;
    margin-right: 10px;
  }

  .dormitoryBulkImport .fileMsg .el-button {
    padding: 0;
    width: 7.5rem;
    font-size: .875rem;
    height: 30px;
    background-color: #099f9b;
    border-color: #099f9b;
  }

  .dormitoryBulkImport .fileMsg .el-input__inner {
    height: 30px;
    font-size: .875rem;
  }

  .dormitoryBulkImport .uploadFile {
    display: inline-block;
    position: relative;
    margin-right: 10px;
  }

  .dormitoryBulkImport .uploadFile .file_input {
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
