<template>
  <div class="selfEntry">
    <el-row type="flex" align="middle">
      <el-col :span="12">
        <el-button type="primary" class="returnBtn" @click="returnPrev"><img
          src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
          alt=""><span class="returnTxt">返回上一步</span></el-button>
      </el-col>
      <el-col :span="12">
        <el-button type="primary" class="returnBtn saveBtn" @click="save">保存</el-button>
      </el-col>
    </el-row>
    <el-row type="flex" align="middle" class="examManager_row selfEntry_row">
      <el-form :inline="true" class="formInline">
        <el-form-item label="全卷（单科）满分：">
          <el-input readonly v-model="params.results"/>
        </el-form-item>
        <el-form-item label="计入总分分数：">
          <el-input readonly v-model="params.proportion"/>
        </el-form-item>
        <el-form-item label="排序：">
          <el-select @change="sortTable" v-model="sortField" placeholder="请选择" class="g_class">
            <el-option
              v-for="item in sortFields"
              :key="item.id"
              :label="item.value"
              :value="item.value">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row type="flex" align="middle" class="examManager_row selfEntry_row">
      <el-form :inline="true">
        <el-form-item label="文件路径：">
          <el-input class="spec" readonly v-model="filePath.name"/>
          <div class="uploadFile">
            <el-button type="primary" class="chFile">
              <img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_choice.png"
                   alt="">
              <span>选择文件</span>
            </el-button>
            <input type="file" accept=".xlsx,.xlsm,.xls" class="file_input" @change="getFileName">
          </div>
          <el-button type="primary" class="chFile" @click="operationData('downloadTemplate')">
            <img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_download.png"
                 alt="">
            <span>下载模板</span>
          </el-button>
          <el-button type="primary" class="chFile" @click="operationData('upload')">
            <img src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_upload.png"
                 alt="">
            <span>上传</span>
          </el-button>
        </el-form-item>
      </el-form>
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
            placeholder="请输入查询信息"
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
          width="80"
          label="序号">
        </el-table-column>
        <el-table-column
          prop="className"
          label="班级" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="serialNumber"
          label="班级序号" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="number"
          label="考号" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="room"
          label="考场">
        </el-table-column>
        <el-table-column
          prop="seatnumber"
          label="考场座号">
        </el-table-column>
        <el-table-column
          prop="name"
          label="姓名" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="results"
          label="全卷" sortable="custom">
          <template slot-scope="scope">
            <input type="number" class="scoresInput" v-model="scope.row.results">
          </template>
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
        sortFields: [{
          id: 0,
          value: '班级+班级序号'
        }, {
          id: 1,
          value: '考试+考试座号'
        }],
        sortField: '',
        filePath: {},
        params: {},
        tableData: [],
        totalNum: 0,
        selectParam: {
          examinationid: '',
          page: 1,
          limit: 50,
          field: '',
          branchid: '',
          order: '',
          screen: '',
          subjectid: ''
        },
        loading: false
      }
    },
    created: function () {
      var pathParam = this.$route.params;
      this.selectParam.examinationid = pathParam.examinationid;
      this.selectParam.branchid = pathParam.branchid;
      this.selectParam.subjectid = pathParam.subjectid;
      this.loadData(this.selectParam);
    },
    methods: {
      returnPrev() {
        this.$router.go(-1);
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
      sortTable() {
        this.selectParam.field = this.sortField;
        this.selectParam.order = '';
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      getFileName(node) {
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
      operationData(type) {
        var self = this, formData = new FormData();
        let sAy = [], hdData = {
          className: '班级',
          serialNumber: '班级序号',
          number: '考号',
          room: '考场',
          seatnumber: '考场座号',
          name: '姓名',
          results: '全卷'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if (name == 'headmaster' || name == 'staff') {
              d[name] = obj[name] ? '是' : '否';
            } else {
              d[name] = obj[name] || '';
            }
          }
          sAy.push(d)
        }
        if (type == 'downloadTemplate') {
          req.downloadFile('.selfEntry', '/school/Examination/exmanagement/type/resultin/typename/resultexport', 'post')
        } else if (type == 'upload') {
          formData.append('photo', $('.file_input').prop('files')[0]);
          formData.append('examinationid', self.selectParam.examinationid);
          formData.append('branchid', self.selectParam.branchid);
          formData.append('subjectid', self.selectParam.subjectid);
          formData.append('validate', 0);
          if (!self.filePath.name) {
            self.vmMsgWarning('请先下载模板并选择文件再上传！');
            return false;
          }
          req.ajaxFile('/school/Examination/exmanagement/type/resultin/typename/import', 'post', formData, function (res) {
            if (res.return) {
              if (res.return == 'numberError') {
                let msg = '考号';
                if (res.List.length != 0) {
                  msg += res.List.join(',')
                }
                self.$confirm(msg + '重复，系统只会保留一条数据？', '提示', {
                  confirmButtonText: '继续',
                  cancelButtonText: '取消',
                  type: 'warning'
                }).then(() => {
                  var nFormData = new FormData();
                  nFormData.append('photo', $('.file_input').prop('files')[0]);
                  nFormData.append('examinationid', self.selectParam.examinationid);
                  nFormData.append('branchid', self.selectParam.branchid);
                  nFormData.append('subjectid', self.selectParam.subjectid);
                  nFormData.append('validate', 1);
                  req.ajaxFile('/school/Examination/exmanagement/type/resultin/typename/import', 'post', nFormData, function (res) {
                    if (res.return) {
                      self.vmMsgSuccess('上传成功！');
                      self.selectParam.page = 1;
                      self.selectParam.field = '';
                      self.selectParam.order = '';
                      self.loadData(self.selectParam);
                    } else {
                      self.vmMsgError('保存失败，请检查数据格式!');
                    }
                  })
                }).catch(() => {
                });
              } else {
                self.vmMsgSuccess('上传成功！');
                self.selectParam.page = 1;
                self.selectParam.field = '';
                self.selectParam.order = '';
                self.loadData(self.selectParam);
              }
            } else {
              self.vmMsgError('上传失败，请检查数据格式!');
            }
          })
        } else if (type == 'copy') {
          req.copyTableData('.selfEntry', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      save() {
        var self = this, data = {
          examinationid: self.selectParam.examinationid,
          branchid: self.selectParam.branchid,
          subjectid: self.selectParam.subjectid,
          data: []
        };
        for (let obj of self.tableData) {
          let d = {
            results: obj.results,
            id: obj.id,
            userid: obj.userid
          };
          data.data.push(d);
        }
        req.ajaxSend('/school/Examination/exmanagement/type/resultin/typename/resultupin', 'post', data, function (res) {
          if (res.return) {
            self.vmMsgSuccess('保存成功!');
          } else {
            self.vmMsgError('保存失败!');
          }
        })
      },
      loadData(data) {
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Examination/exmanagement/type/resultin/typename/resultinfind', 'post', data, function (res) {
          self.loading = false;
          self.tableData = res.data || [];
          self.totalNum = Number.parseInt(res.page.count);
          self.params = res.value;
        })
      }
    }
  }
</script>
<style>
  .selfEntry .returnBtn.el-button--primary {
    border-radius: 20px;
  }

  .selfEntry .returnBtn.el-button--primary .returnTxt {
    margin-left: 10px;
  }

  .selfEntry .uploadFile {
    margin-left: .5rem;
  }

  .selfEntry .el-button.chFile {
    background-color: #13b5b1;
    border-color: #13b5b1;
    height: 30px;
    padding: 0 .8rem;
  }

  .selfEntry .el-button.uploadFile {
    border-radius: 20px;
    padding: 8px 25px;
  }

  .selfEntry_row .el-input, .selfEntry_row .el-input__inner {
    width: 8rem;
  }

  .selfEntry .g_class .el-input, .selfEntry .g_class .el-input__inner {
    width: 15rem;
  }

  .selfEntry_row .spec.el-input, .selfEntry_row .spec .el-input__inner {
    width: 10rem;
  }

  .selfEntry_row .uploadFile {
    display: inline-block;
    position: relative;
  }

  .selfEntry_row .file_input {
    width: 100%;
    height: 36px;
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

  .selfEntry .formInline .el-form-item {
    margin-right: 1rem;
    margin-bottom: 0;
  }

  .selfEntry .saveBtn {
    padding: 10px 30px;
    float: right;
  }

  .selfEntry input.scoresInput {
    width: 100%;
    padding: .3rem 0;
    font-family: inherit;
    text-align: center;
    border: none;
  }
</style>
