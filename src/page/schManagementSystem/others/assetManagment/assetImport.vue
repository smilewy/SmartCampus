<template>
  <div class="g-container">
    <header class="g-header">
      <div class="g-textHeader g-flexStartRow">
        <h2>批量导入</h2>
      </div>
      <div class="g-upload-section g-flexStartRow">
        <el-form ref="studentImportForm" label-position="left" :rules="importFormRules" :model="dataHeader"
                 label-width="85px">
          <el-form-item label="文件路径:" prop="fileName">
            <div class="fileName" v-text="dataHeader.fileName"></div>
          </el-form-item>
          <div class="button_row">
            <button class="fileButtonShow headerButton">
              <img src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_choice.png"/>
              选择文件
            </button>
            <input type="file" @change="chooseFile" ref="uploadIpt" class="chooseFile" title="选择文件"/>
          </div>
          <div class="button_row">
            <button class="headerButton" type="button" @click="downloadModelClick">
              <img src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_download.png"/>
              下载模版
            </button>
          </div>
          <div class="button_row">
            <button type="button" class="headerButton" @click="scanFile">
              <img src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_eye.png"/>
              预览
            </button>
          </div>
          <div class="button_row">
            <button type="button" class="headerButton" @click="uploadFile">
              <img src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_upload.png"/>
              上传
            </button>
          </div>
        </el-form>
      </div>
    </header>
    <section class="g-section">
      <div class="gs-header g-liOneRow">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="exportClick" class="filt buttonChild" title="导出">
              <img class="filt_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"/>
              <img class="filt_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"/>
            </el-button>
          </el-button-group>
          <span style="margin-left: 20px; color: #ccc ;">* 高亮输入框为必填项</span>
        </div>
        <!--        <div class="gs-refresh g-fuzzyInput">
                  <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入需搜索的值" @change="fuzzyClick"></el-input>
                </div>-->
      </div>
      <div class="gs-table alertsList">
        <el-table class="g-NotHover"
                  v-loading="loading" 
                  element-loading-text="拼命加载中" 
                  element-loading-spinner="el-icon-loading"  
                  ref="studentMsgTable" 
                  :data="headerButtonData.studentBasicMsg" 
                  style="width:100%"
                  @sort-change="sortChange" 
                  @selection-change="handleStudentTable">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <el-table-column label="序号" type="index">
          </el-table-column>
          <el-table-column label="资产名称">
            <template slot-scope="prop">
              <input type="text" class="tableInput require" v-model="prop.row.assetsName" @input="checkLength(prop.row.assetsName, prop.$index, 'assetsName')"/>
            </template>
          </el-table-column>
          <el-table-column label="分类代码">
            <template slot-scope="prop">
              <input type="text" class="tableInput require" v-model="prop.row.assetsTypeId" @input="checkLength(prop.row.assetsTypeId, prop.$index, 'assetsTypeId')"/>
            </template>
          </el-table-column>
          <el-table-column label="规格">
            <template slot-scope="prop">
              <input type="text" class="tableInput" v-model="prop.row.spec" @input="checkLength(prop.row.spec, prop.$index, 'spec')"/>
            </template>
          </el-table-column>
          <el-table-column label="品牌型号">
            <template slot-scope="prop">
              <input type="text" class="tableInput" v-model="prop.row.brandModel" @input="checkLength(prop.row.brandModel, prop.$index, 'brandModel')"/>
            </template>
          </el-table-column>
          <el-table-column label="供应商">
            <template slot-scope="prop">
              <input type="text" class="tableInput" v-model="prop.row.supplier" @input="checkLength(prop.row.supplier, prop.$index, 'supplier')"/>
            </template>
          </el-table-column>
          <el-table-column label="单位">
            <template slot-scope="prop">
              <input type="text" class="tableInput" v-model="prop.row.unit" @input="checkLength(prop.row.unit, prop.$index, 'unit')"/>
            </template>
          </el-table-column>
          <el-table-column label="单价(元)">
            <template slot-scope="prop">
              <input type="text" class="tableInput require" v-model="prop.row.onePrice" @input="checkLength(prop.row.onePrice, prop.$index, 'onePrice')"/>
            </template>
          </el-table-column>
          <el-table-column label="数量">
            <template slot-scope="prop">
              <input type="text" class="tableInput require" v-model="prop.row.num" @input="checkLength(prop.row.num, prop.$index, 'num')"/>
            </template>
          </el-table-column>
          <el-table-column label="存放地址">
            <template slot-scope="prop">
              <input type="text" class="tableInput" v-model="prop.row.storageLocation" @input="checkLength(prop.row.storageLocation, prop.$index, 'storageLocation')"/>
            </template>
          </el-table-column>
          <el-table-column label="入库时间" width="150">
            <template slot-scope="prop">
              <!-- <input type="text" class="tableInput" v-model="prop.row.inStorageTime"/> -->
              <el-date-picker class="tableInput" :editable="false" v-model="prop.row.inStorageTime" type="datetime" size="mini" placeholder="请选择入库时间"></el-date-picker>
            </template>
          </el-table-column>
          <el-table-column label="备注">
            <template slot-scope="prop">
              <input type="text" class="tableInput" v-model="prop.row.remarks" @input="checkLength(prop.row.remarks, prop.$index, 'remarks')"/>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>
    <!--    <footer class="g-footer">
          <el-row class="pageAlerts">
            <el-pagination
              @current-change="handleCurrentChange"
              :current-page.sync="currentPage"
              :page-size="pageALl"
              layout="prev, pager, next, jumper"
              :total="pageALl">
            </el-pagination>
          </el-row>
        </footer>-->
  </div>
</template>
<script>
  import {
    fileTypeCheck,
    handlerAjaxData
  } from '@/assets/js/common'
  import req from '@/assets/js/common'
  import {
    assetImportUpload,//上传
    assetImportScan,//预览
    assetImportLoad,//页面加载数据
    assetImportSearch,//模糊查询
    assetImportSort,//排序
  } from '@/api/http'

  export default {
    data() {
      return {
        /*ajax data*/
        headerButtonData: {
          gradeloadData: [],
          classesLoadData: [],
          msgTypeLoadData: [],
          studentBasicMsg: [],
        },
        /*form表单双向绑定数据*/
        dataHeader: {
          /*显示div中文件信息值*/
          fileName: '',
        },
        /*type='file'input框的值*/
        fileInputValue: '',
        /*导出数据*/
        /*fuzzyFilter*/
        fuzzyInput: '',
        /*footer*/
        pageALl: 1,
        currentPage: 1,
        /*表单验证规则*/
        importFormRules: {
          fileName: [
            {required: true, message: '请选择文件!'}
          ],
        },
        loading: false
      }
    },
    computed: {},
    methods: {
      checkLength( value, index, key ){
        // 限制输入字符长度不超过20
        if(value.length > 20 ){
          this.headerButtonData.studentBasicMsg[index][key] = value.substr(0, 20);
          this.vmMsgWarning( '输入的字符长度不能超过20！' );
        }
      },

      /*返回*/
      goBackParent() {
        this.$router.push('/newStudentmanagement');
      },
      /*table*/
      handleStudentTable(section) {
        /*section为选择项行信息组成的数组*/
      },
      sortChange(column) {
        /*table排序回调*/
        assetImportSort({sort: column.prop, sortData: column.order}).then(data => {
          this.headerButtonData.studentBasicMsg = handlerAjaxData(data);
        });
      },
      /*修改数据更新*/
      /*footer*/
      handleCurrentChange(val) {
        this.currentPage = val;
      },
      /*模糊查询*/
      fuzzyClick() {
        /*点击search按钮*/
        assetImportSearch({valueData: this.fuzzyInput}).then(data => {
          this.headerButtonData.studentBasicMsg = handlerAjaxData(data);
        });
      },
      /*选择文件change事件*/
      chooseFile(event) {
        /*this.fileName=this.extractFilename($(event.currentTarget).val());*/
        if (!fileTypeCheck(this.extractFilename($(event.currentTarget).val()), ['.xls', '.xlsx'])) {
          this.vmMsgError( '文件不符合,请选择excel文件(*.xls或*.xlsx)！' );
          /*          this.$alert('文件不符合,请选择excel文件(*.xls或*.xlsx)!','提示',{
                      confirmButtonText:'确定',
                      type:'error',
                      callback: action => {
                        console.log(action);
                      }
                    });*/
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
      /*导出数据*/
      /*send ajax*/
      getLoadData() {
        // assetImportLoad().then(data => {
        //   this.headerButtonData.studentBasicMsg = handlerAjaxData(data);
        // })
        let storageSession = sessionStorage["uploadStudentMsg"];
        let temp = storageSession && JSON.parse(storageSession);
        if(temp) { this.headerButtonData.studentBasicMsg = temp; }
      },
      downloadModelClick() {
        req.downloadFile('.g-container', '/school/Assets/batchImport?type=downloadModel', 'post');
      },
      exportClick() {
        req.downloadFile('.g-container', '/school/Assets/batchImport?type=export', 'post');
      },
      /*预览*/
      scanFile() {
        this.loading = true;
        if (this.dataHeader.fileName) {
          let formData = new FormData();
          formData.append('userFile', $('.chooseFile')[0].files[0]);
          assetImportScan(formData).then(data => {
            this.loading = false;
            if (data.statu) {
              this.headerButtonData.studentBasicMsg = handlerAjaxData(data);
              sessionStorage["uploadStudentMsg"] = JSON.stringify(this.headerButtonData.studentBasicMsg);
            } else {
              this.vmMsgError( data.message );
            }
          });
        }
        else {
          this.vmMsgWarning( '请先选择文件！' );
        }
      },
      /*上传*/
      uploadFile() {
        if (this.headerButtonData.studentBasicMsg.length > 0) {
            function getTip( rowIndex, tipName ) {
                return "请填写第" + rowIndex + "行的" + tipName + "!";
            }

           let temp = this.headerButtonData.studentBasicMsg.find( o => !o.assetsName || !o.assetsTypeId || !o.num || !o.onePrice );
            if(temp){
                let rowNum = this.headerButtonData.studentBasicMsg.indexOf(temp) + 1;
              if( !temp.assetsName ){ this.vmMsgError(getTip( rowNum, '资产名称' )); }
              if( !temp.assetsTypeId ){ this.vmMsgError(getTip( rowNum, '分类代码' )); }
              if( !temp.num ){ this.vmMsgError(getTip( rowNum, '数量' )); }
              if( !temp.onePrice ){ this.vmMsgError(getTip( rowNum, '单价' )); }
              return;
            }
          assetImportUpload({data: this.headerButtonData.studentBasicMsg}).then(data => {
            if (data.statu) {
              this.vmMsgSuccess( '上传成功！' );
              this.headerButtonData.studentBasicMsg = [];
              sessionStorage["uploadStudentMsg"] = null;
              this.dataHeader.fileName='';
              this.$refs.uploadIpt.value = '';
            } else {
              this.vmMsgError( data.message );
            }
          });
        } else {
          this.vmMsgWarning( '没有上传数据！' );
        }
      },
    },
    created() {
      this.getLoadData();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/test';
  @import '../../../../style/style';
</style>


