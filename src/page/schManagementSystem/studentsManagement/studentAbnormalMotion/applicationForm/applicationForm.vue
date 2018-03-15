<template>
  <div class="applicationForm">
    <h3>异动申请表</h3>
    <el-row class="applicationForm_row">
      <el-button :disabled="!btnState" type="primary" class="uploadTemplate" @click="showDialog">
        <img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_upload.png"
             alt="">
        <span>上传模板</span>
      </el-button>
      <span class="warmHint">温馨提示：您可以上传Word、Excel模板表格，方便您下次打印和下载使用。</span>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" justify="end" class="alertsBtn">
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
      <el-col :span="8">
        <div class="g-fuzzyInput">
          <el-input
            placeholder="请输入关键字"
            suffix-icon="el-icon-search"
            v-model="selectParam.find"
            @change="goSearch">
          </el-input>
        </div>
      </el-col>
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
          width="100"
          label="序号">
        </el-table-column>
        <el-table-column
          prop="name"
          label="表格名称">
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="operation edit" @click="operation('load',scope.$index)">下载</span>
            <span class="operation edit" @click="operation('preview',scope.$index)">预览</span>
            <span class="operation" :class="{'edit':scope.row.rename!='0'&&btnState}"
                  @click="operation('rename',scope.$index)">重命名</span>
            <span class="operation" :class="{'delete':btnState}" @click="operation(delete'',scope.$index)">删除</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="上传申请表模板"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row>
        <el-form ref="templateForm" :model="fileData" :rules="templateFormRule" label-width="120px">
          <el-form-item label="申请表名称：" prop="name">
            <el-col :span="17">
              <el-input v-model="fileData.name" readonly></el-input>
            </el-col>
            <el-col :span="6" :offset="1">
              <div class="uploadFile">
                <el-button>选择文件</el-button>
                <input type="file" accept=".xlsx,.xlsm,.xls.doc,.docx" class="file_input" @change="sendFile">
              </div>
            </el-col>
          </el-form-item>
          <el-form-item label="修改名称：">
            <el-switch active-text="是" inactive-text="否" active-color="#09baa7" v-model="fileData.rename"></el-switch>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="save(0)">保存</el-button>
    <el-button @click="dialogVisible = false">取消</el-button>
  </span>
    </el-dialog>
    <el-dialog
      title="修改名称"
      :visible.sync="nameDialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row type="flex" justify="center">
        <el-col :span="18">
          <el-form ref="nameForm" :model="nameForm" :rules="nameFormRule" label-width="100px">
            <el-form-item label="表格名称：" prop="name">
              <el-input v-model="nameForm.name">
                <template slot="append">.{{nameForm.suffix}}</template>
              </el-input>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="save(1)">保存</el-button>
    <el-button @click="nameDialogVisible = false">取消</el-button>
  </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        btnState: true,
        tableData: [],
        selectParam: {
          find: ''
        },
        dialogVisible: false,
        fileData: {
          name: '',
          file: '',
          rename: true
        },
        nameDialogVisible: false,
        nameForm: {
          id: '',
          name: '',
          suffix: ''
        },
        nameFormRule: {
          name: [
            {required: true, message: '请输入表格名称', trigger: 'blur'},
            {min: 1, max: 20, message: '长度在 1 到 20 个字符', trigger: 'blur'}
          ]
        },
        templateFormRule: {
          name: [
            {required: true, message: '请选择文件', trigger: 'blur'}
          ]
        },
        loading: false
      }
    },
    created: function () {
      this.goSearch();
    },
    methods: {
      goSearch() {  //查询
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Transaction/shenqingbiao/type/find', 'post', self.selectParam, function (res) {
          self.loading = false;
          self.btnState = res.state;
          self.tableData = res.data;
        })
      },
      sendFile() {   //上传文件
        var suffix, file = $('.file_input').prop('files')[0], sAy, len;
        if (!file) {
          return false;
        }
        $('.file_input').val('');
        sAy = file.name.split('.');
        len = sAy.length - 1;
        suffix = sAy[len];
        switch (suffix) {
          case 'docx':
          case 'doc':
          case 'xlsx':
          case 'xlsm':
          case 'xls':
            this.fileData.name = file.name;
            this.fileData.file = file;
            break;
          default:
            this.vmMsgWarning('只能上传word、xlsx、xlsm、xls格式文件！');
            return false;
        }
      },
      showDialog() {
        this.dialogVisible = true;
      },
      handleClose(done) {
        done();
      },
      operation(type, idx) {
        var self = this, data;
        if (type == 'load') {
          req.downloadFile('.applicationForm', '/school/Transaction/shenqingbiao/type/export?id=' + self.tableData[idx].id, 'post');
        } else if (type == 'preview') {
          window.open(self.tableData[idx].address);
        } else if (type == 'rename') {
          if (self.tableData[idx].rename == '0' || !self.btnState) {
            return false;
          }
          self.nameDialogVisible = true;
          self.nameForm.id = self.tableData[idx].id;
          self.nameForm.name = self.tableData[idx].name;
          self.nameForm.suffix = self.tableData[idx].suffix;
        } else {
          data = {
            id: self.tableData[idx].id
          };
          if (!self.btnState) {
            return false;
          }
          self.$confirm('是否删除该模板？', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/Transaction/shenqingbiao/type/del', 'post', data, function (res) {
              if (res.return) {
                self.vmMsgSuccess('删除成功！');
                self.tableData.splice(idx, 1);
              } else {
                self.vmMsgError('删除失败！');
              }
            })
          }).catch(() => {
          });
        }
      },
      operationData(type) {
        let sAy = [], hdData;
        hdData = {
          name: '表格名称'
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
          req.copyTableData('.applicationForm', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      save(type) {
        var self = this;
        if (type == 0) {
          var formData = new FormData();
          formData.append('photo', self.fileData.file);
          formData.append('rename', self.fileData.rename ? 1 : 0);
          self.$refs['templateForm'].validate((valid) => {
            if (valid) {
              req.ajaxFile('/school/Transaction/shenqingbiao/type/upload', 'post', formData, function (res) {
                if (res.return) {
                  self.vmMsgSuccess('上传成功！');
                  self.dialogVisible = false;
                  self.goSearch();
                } else {
                  self.vmMsgError('上传失败！');
                }
              })
            } else {
              return false;
            }
          })
        } else {
          self.$refs['nameForm'].validate((valid) => {
            if (valid) {
              let data = {
                id: self.nameForm.id,
                name: self.nameForm.name
              };
              req.ajaxSend('/school/Transaction/shenqingbiao/type/update', 'post', data, function (res) {
                if (res.return) {
                  self.vmMsgSuccess('重命名成功！');
                  self.nameDialogVisible = false;
                  self.goSearch();
                } else {
                  self.vmMsgError('重命名失败！');
                }
              });
            } else {
              return false;
            }
          });
        }
      }
    }
  }
</script>
<style>
  .applicationForm {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .applicationForm .applicationForm_row {
    margin-top: 2rem;
  }

  .applicationForm .alertsBtn {
    margin: 1.25rem 0;
  }

  .applicationForm .alertsList .el-table th, .applicationForm .alertsList .el-table td {
    text-align: center;
  }

  .applicationForm h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
    display: inline-block;
  }

  .applicationForm .warmHint {
    color: #999999;
    margin-left: 1.25rem;
    font-size: 14px;
  }

  .applicationForm .uploadTemplate {
    padding: 8px 20px;
  }

  .applicationForm .d_line {
    margin: 1.25rem 0;
  }

  .applicationForm .g-fuzzyInput {
    float: right;
  }

  .applicationForm .operation {
    padding: 0 1.125rem;
  }

  .applicationForm .operation.edit, .applicationForm .operation.delete {
    cursor: pointer;
  }

  .applicationForm .operation + .operation {
    border-left: 1px solid #d2d2d2;
  }

  .applicationForm .edit {
    color: #4da1ff;
  }

  .applicationForm .delete {
    color: #ff6773;
  }

  .applicationForm .uploadFile {
    position: relative;
  }

  .applicationForm .uploadFile .el-button {
    border-radius: 20px;
    width: 100%;
  }

  .applicationForm .file_input {
    width: 100%;
    height: 36px;
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

  .applicationForm .el-dialog--small {
    width: 600px;
  }
</style>
