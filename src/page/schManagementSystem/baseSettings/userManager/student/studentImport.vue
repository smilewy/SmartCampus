<template>
  <div class="g-container studentImport">
    <header class="g-header">
      <div class="gh-header">批量导入</div>
      <div class="g-upload-section g-flexStartRow">
        <el-form ref="studentImportForm" label-position="left" style="width: 100%;" :model="dataHeader"
                 label-width="85px">
          <el-form-item label="年级:">
            <el-select v-model="selectParam.gradeid" placeholder="请选择" @change="selectAllData">
              <el-option
                v-for="item in gradeList"
                :key="item.gradeid"
                :label="item.znGradeName"
                :value="item.gradeid">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="文件路径:" prop="fileName">
            <div class="fileName" v-text="dataHeader.fileName"></div>
          </el-form-item>
          <div class="button_row">
            <button class="fileButtonShow headerButton">
              <img
                src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_choice.png"/>
              选择文件
            </button>
            <input type="file" @change="chooseFile" class="chooseFile" title="选择文件"/>
          </div>
          <div class="button_row">
            <button type="button" class="headerButton" @click="downloadTemplate">
              <img
                src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_download.png"/>
              下载模版
            </button>
          </div>
          <div class="button_row">
            <button type="button" class="headerButton" @click="uploadFile">
              <img
                src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_upload.png"/>
              上传
            </button>
          </div>
        </el-form>
      </div>
    </header>
    <section class="g-section">
      <div class="gs-header">
        <div class="gs-button alertsBtn">
          <el-button @click="buttonClick" data-msg="export" class="filt" title="导出">
            <img class="filt_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"/>
            <img class="filt_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"/>
          </el-button>
          <el-button-group class="elGroupButton_two">
            <el-button data-msg="copy" class="filt" title="复制" @click="operationData('copy')">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png"/>
            </el-button>
            <el-button data-msg="print" class="filt" title="打印" @click="operationData('print')">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"/>
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"/>
            </el-button>
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" placeholder="请输入关键字" v-model="selectParam.valueData" suffix-icon="el-icon-search"
                    @change="fuzzyClick"></el-input>
        </div>
      </div>
      <div class="gs-table alertsList">
        <el-table ref="studentMsgTable" :data="studentBasicMsg.data" style="width:100%"
                  @sort-change="sortChange"
                  v-loading="loading"
                  element-loading-text="拼命加载中">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <el-table-column label="序号" type="index" width="80"></el-table-column>
          <el-table-column label="班级" prop="className" sortable="custom"></el-table-column>
          <el-table-column label="姓名" prop="name" sortable="custom"></el-table-column>
          <el-table-column label="性别" prop="sex" sortable="custom"></el-table-column>
          <el-table-column label="出生日期" prop="birth" sortable="custom"></el-table-column>
          <el-table-column label="手机号" prop="phone" sortable="custom"></el-table-column>
          <el-table-column label="是否住校" prop="isResSchool" sortable="custom"></el-table-column>
          <el-table-column label="户口类型" prop="hklx" sortable="custom"></el-table-column>
          <el-table-column label="身份证号" prop="idCard" sortable="custom"></el-table-column>
          <el-table-column label="录取类别" prop="admCategory" sortable="custom"></el-table-column>
        </el-table>
      </div>
    </section>
    <footer class="g-footer" v-if="studentBasicMsg.data.length!=0">
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="selectParam.page"
          :page-size="selectParam.pageSize"
          layout="prev, pager, next, jumper"
          :page-count="pageAll">
        </el-pagination>
      </el-row>
    </footer>
  </div>
</template>
<script>
  import {fileTypeCheck} from '@/assets/js/common'
  import req from '@/assets/js/common'
  import {
    studentImportUpload,//上传
    studentImportLoad,//加载数据
  } from '@/api/http'
  import ElButton from "../../../../../../node_modules/element-ui/packages/button/src/button";

  export default {
    components: {ElButton},
    data() {
      return {
        /*ajax data*/
        studentBasicMsg: {
          data: []
        },
        gradeList: [],
        dataHeader: {
          fileName: ''
        },
        /*type='file'input框的值*/
        fileInputValue: '',
        pageAll: 1,
        selectParam: {
          page: 1,
          pageSize: 50,
          gradeid: '',
          sortData: '',
          sort: '',
          valueData: ''
        },
        loading:false
      }
    },
    created() {
      var self = this;
      //请求年级列表
      req.ajaxSend('/school/User/getGrade?type=getGradeList', 'get', '', function (res) {
        self.gradeList = res;
        if (self.gradeList.length == 0) {
          return false;
        }
        self.selectParam.gradeid = self.gradeList[0].gradeid;
        self.getLoadData();
      })
    },
    methods: {
      selectAllData() {
        this.getLoadData();
      },
      sortChange(column) {
        this.selectParam.sortData = column.prop || '';
        this.selectParam.sort = column.order || '';
        this.getLoadData();
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.getLoadData();
      },
      /*模糊查询*/
      fuzzyClick() {
        this.selectParam.sortData = '';
        this.selectParam.sort = '';
        this.selectParam.page = 1;
        this.getLoadData();
      },
      buttonClick(event) {
        const e = $(event.currentTarget), targetMsg = e.data('msg');
        if (targetMsg == 'export') {
          this.exportAjax();
        }
      },
      operationData(type) {
        let sAy = [], hdData;
        hdData = {
          className: '班级',
          name: '姓名',
          sex: '性别',
          birth: '出生日期',
          phone: '手机号',
          hklx: '户口类型',
          idCard: '身份证号',
          admCategory: '录取类别'
        };
        sAy.push(hdData);
        for (let obj of this.studentBasicMsg.data) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.studentImport', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*批量导入上传*/
      uploadFile() {
        if (!this.selectParam.gradeid) {
          this.vmMsgWarning('请选择年级！');
          return false;
        }
        if (this.dataHeader.fileName) {
          let formData = new FormData();
          formData.append('userFile', $('.chooseFile')[0].files[0]);
          formData.append('gradeid', this.selectParam.gradeid);
          studentImportUpload(formData).then(data => {
            if (data.statu == 1) {
              this.vmMsgSuccess('上传成功!');
              this.getLoadData();
            } else if (data.statu == 202) {
              var self = this;
              self.$confirm(data.message, '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
              }).then(() => {
                let formData1 = new FormData();
                formData1.append('userFile', $('.chooseFile')[0].files[0]);
                formData1.append('gradeid', self.selectParam.gradeid);
                formData1.append('token', data.token);
                req.ajaxFile('/school/user/allUse?type=uploadGoOn', 'post', formData1, function (res) {
                  if (res.statu == 1) {
                    self.vmMsgSuccess('上传成功!');
                    self.getLoadData();
                  } else {
                    self.vmMsgError(res.message);
                  }
                });
              }).catch(() => {
              });
            } else {
              this.vmMsgError(data.message);
            }
          });
        } else {
          this.vmMsgWarning('请选择上传的文件!');
          return;
        }
      },
      /*选择文件change事件*/
      chooseFile(event) {
        if (!fileTypeCheck(this.extractFilename($(event.currentTarget).val()), ['.xls', '.xlsx'])) {
          this.$alert('文件不符合,请选择excel文件(*.xls或*.xlsx)!', '提示', {
            confirmButtonText: '确定',
            type: 'error',
            callback: action => {
            }
          });
        } else {
          this.dataHeader.fileName = this.extractFilename($(event.currentTarget).val());
          this.fileInputValue = $(event.currentTarget).val();
        }
      },
      extractFilename(path) {
        let x;
        x = path.lastIndexOf('\\');
        if (x >= 0) // 基于Windows的路径
          return path.substr(x + 1);
        x = path.lastIndexOf('/');
        if (x >= 0) // 基于Unix的路径
          return path.substr(x + 1);
        return path; // 仅包含文件名
      },
      /*下载模版*/
      downloadTemplate() {
        req.downloadFile('.g-container', '/school/user/userGl?type=download&downType=student', 'post');
      },
      /*导出数据*/
      exportAjax() {
        req.downloadFile('.g-container', '/school/user/exportStudent?type=exportStudent', 'post');
      },
      /*页面加载数据*/
      getLoadData() {
        if (!this.selectParam.gradeid) {
          this.vmMsgWarning('请选择年级！');
          return false;
        }
        this.loading=true;
        studentImportLoad(this.selectParam).then(data => {
          this.loading=false;
          this.pageAll = data.data.maxPage;
          this.studentBasicMsg = data.data;
        });
      },
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/style';
  @import '../../../../../style/userManager/student/studentMessage.less';
  @import '../../../../../style/userManager/student/studentManager.css';
</style>


