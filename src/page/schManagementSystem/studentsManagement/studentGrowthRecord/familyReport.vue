<template>
  <div class="familyReport">
    <el-row type="flex" align="middle">
      <el-col :span="14">
        <h3>家庭报告书</h3>
      </el-col>
      <el-col :span="10">
        <el-row type="flex" justify="end" class="reportOperation">
          <el-button @click="preview('data')">预览</el-button>
          <el-button type="primary" @click="save">保存</el-button>
        </el-row>
      </el-col>
    </el-row>
    <el-row :gutter="20" class="familyReport_row">
      <el-col :span="4">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>选择班级：</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入关键字进行过滤"
                v-model="filterText">
                <template slot="prepend">
                  <i class="el-icon-search"></i>
                </template>
              </el-input>
            </el-row>
          </el-row>
          <el-row class="d_line"></el-row>
          <el-row class="treeList_body"
                  v-loading="loading"
                  element-loading-text="拼命加载中">
            <el-tree
              :data="treeData"
              node-key="id"
              ref="tree"
              :highlight-current="true"
              :filter-node-method="filterNode"
              :props="defaultProps"
              @node-click="choosePerson">
            </el-tree>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="20">
        <el-row class="treeList">
          <el-row type="flex" align="middle" class="secHeader">
            <h5>家庭报告书模板</h5>
          </el-row>
          <el-row class="familyReport_content">
            <el-form ref="familyReportForm" :model="form" label-width="90px">
              <el-form-item label="固定内容：" class="editorContent">
                <el-input type="textarea" resize="none" v-model="form.comment"></el-input>
              </el-form-item>
              <el-form-item label="成绩：">
                <el-select v-model="showParam.examinationid" placeholder="请选择考试" style="width: 31.25rem">
                  <el-option :label="exam.examination" :value="exam.examinationid" v-for="exam in examList"
                             :key="exam.examinationid"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item>
                <el-checkbox v-model="showParam.final">加入最后一次期末评语</el-checkbox>
                <el-checkbox v-model="showParam.jzShow">显示家长栏意见</el-checkbox>
              </el-form-item>
            </el-form>
          </el-row>
        </el-row>
      </el-col>
    </el-row>
    <el-dialog
      title="家庭报告书-预览数据"
      :modal="false"
      :visible.sync="dialogVisible"
      :before-close="handleClose">
      <el-row class="reportTemplate">
        <el-table
          :data="tableData"
          max-height="400"
          style="width: 100%"
          v-loading="loading1"
          element-loading-text="拼命加载中">
          <el-table-column
            min-width="120"
            prop="className"
            label="班级">
          </el-table-column>
          <el-table-column
            min-width="120"
            prop="name"
            label="姓名">
          </el-table-column>
          <el-table-column
            min-width="120"
            :label="headData.subjectName" v-for="headData in tableHeadData" :key="headData.id">
            <template slot-scope="scope">
              {{scope.row[headData.id]}}
            </template>
          </el-table-column>
          <el-table-column
            prop="sore"
            label="总分"
            min-width="120">
          </el-table-column>
          <el-table-column
            min-width="200"
            label="评语">
            <template slot-scope="scope">
              <textarea name="" v-model="scope.row.content"></textarea>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="preview('report')">预览</el-button>
    <el-button @click="dialogVisible = false">关闭</el-button>
  </span>
    </el-dialog>
    <el-row class="familyReportPrint" v-show="familyReportPrint">
      <el-row type="flex" align="middle" class="familyReportPrint_head">
        <el-col :span="12" class="familyReportPrint_headL">家庭报告书</el-col>
        <el-col :span="12" class="familyReportPrint_headR">
          <el-button @click="familyReportOperation('print')">打印</el-button>
          <el-button @click="familyReportOperation('out')">导出word</el-button>
          <span class="familyReportPrint_headClose"><i class="el-icon-circle-close"
                                                       @click="familyReportPrint=false"></i></span>
        </el-col>
      </el-row>
      <el-row class="familyReportPrint_body">
        <el-row class="familyReportPrint_content"
                v-loading="loading2"
                element-loading-text="拼命加载中">
          <div v-html="familyData" :id="'pagecontent'+ix" v-for="(familyData,ix) in familyReports" :key="ix"
               class="familyData"></div>
        </el-row>
      </el-row>
    </el-row>
  </div>
</template>
<script>
  //导出word
  import '../../../../../static/jQueryExportWord/jquery.wordexport'

  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        treeData: [],
        defaultProps: {
          children: 'data',
          label: 'classname'
        },
        filterText: '',
        form: {
          comment: '',
          gradeId: '',
          classId: ''
        },
        showParam: {
          final: false,
          jzShow: false,
          examinationid: ''
        },
        examList: [],
        dialogVisible: false,
        familyReportPrint: false,
        tableData: [],
        tableHeadData: [],
        familyReports: [],
        loading: false,
        loading1: false,
        loading2: false
      }
    },
    watch: {
      filterText(val) {
        this.$refs.tree.filter(val);
      }
    },
    created: function () {
      var self = this;
      //得到待选学生
      self.loading = true;
      req.ajaxSend('/school/Studentsgrowthrecord/familyRecord?type=getGradeClassStudent', 'get', '', function (res) {
        self.treeData = res.data;
        self.loading = false;
      });
    },
    methods: {
      filterNode(value, data) {
        if (!value) return true;
        if (data.classname) {
          data.classname = data.classname.toString();
          return data.classname.indexOf(value) !== -1;
        }
      },
      choosePerson(node) {
        if (node.classId) {
          var self = this, data = {
            gradeId: node.gradeId,
            classId: node.classId
          };
          self.form.classId = node.classId;
          self.form.gradeId = node.gradeId;
          self.showParam.examinationid = '';
          req.ajaxSend('/school/Studentsgrowthrecord/familyRecord?type=getTestList', 'get', data, function (res) {
            self.examList = res.data;
          })
        }
      },
      preview(type) {
        var self = this, data;
        if (type == 'data') {   //预览数据
          data = {
            examinationid: self.showParam.examinationid,
            classId: self.form.classId,
            gradeId: self.form.gradeId
          };
          if (!self.showParam.examinationid) {
            self.vmMsgWarning('请填选择考试！');
            return false;
          }
          self.dialogVisible = true;
          self.loading1 = true;
          req.ajaxSend('/school/Studentsgrowthrecord/familyRecord?type=prew', 'get', data, function (res) {
            self.loading1 = false;
            if (!self.showParam.final) {
              for (let obj of res.data.data) {
                obj.content = '';
              }
            }
            self.tableData = res.data.data;
            self.tableHeadData = res.data.tr;
          })
        } else {   //预览报告
          if (self.tableData.length == 0) {
            return false;
          }
          self.dialogVisible = false;
          self.familyReportPrint = true;
          data = {
            contentGd: self.form.comment,
            examinationid: self.showParam.examinationid,
            classId: self.form.classId,
            gradeId: self.form.gradeId,
            jzshow: self.showParam.jzShow ? 1 : 0,
            list: []
          };
          for (let obj of self.tableData) {
            let d = {
              userId: obj.userId,
              contentgogo: obj.content
            };
            data.list.push(d);
          }
          self.loading2 = true;
          req.ajaxSend('/school/Studentsgrowthrecord/familyRecord?type=contentPrew', 'get', data, function (res) {
            self.familyReports = res.data;
            self.loading2 = false;
          })
        }
      },
      handleClose(done) {
        done();
      },
      familyReportOperation(type) {
        if (type == 'out') {
          for (let [ix, obj] of this.familyReports.entries()) {
            $("#pagecontent" + ix).wordExport();
          }
        } else {

        }
      },
      save() {
        var self = this;
        if (!self.form.comment) {
          self.vmMsgWarning('请填写固定评语！');
          return false;
        }
        if (!self.form.classId) {
          self.vmMsgWarning('请填选择班级！');
          return false;
        }
        req.ajaxSend('/school/Studentsgrowthrecord/familyRecord?type=create', 'post', self.form, function (res) {
          if (res.statu == 1) {
            self.vmMsgSuccess('保存成功！');
          } else {
            self.vmMsgError(res.message);
          }
        })
      }
    }
  }
</script>
<style>
  .familyReport {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .familyReport h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .familyReport .familyReport_row {
    margin-top: 3rem;
  }

  .familyReport .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
  }

  .familyReport .treeList_body {
    padding: .875rem;
    height: 44rem;
    overflow: auto;
  }

  .familyReport .treeList_title {
    padding: .875rem .875rem 1.5rem;
  }

  .familyReport .reportOperation .el-button {
    padding: .625rem 2rem;
  }

  .familyReport h5 {
    font-size: 1rem;
  }

  .familyReport .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .familyReport .treeList .el-tree {
    border: none;
  }

  .familyReport .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .familyReport .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  .familyReport .secHeader {
    height: 3.125rem;
    padding: 0 1rem;
    border-bottom: 1px solid #d2d2d2;
  }

  .familyReport .familyReport_content {
    padding: 3rem 3rem 1rem 1rem;
  }

  .familyReport .editorContent textarea {
    height: 20rem;
  }

  .familyReport .reportTemplate input, .familyReport .reportTemplate textarea {
    width: 100%;
    border: none;
    text-align: center;
    font-family: inherit;
    font-size: .875rem;
  }

  .familyReport .reportTemplate textarea {
    resize: none;
  }

  .familyReport .el-table th, .familyReport .el-table td {
    text-align: center;
  }

  .familyReport .familyReportPrint {
    position: fixed;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    background-color: #f0f0f0;
    z-index: 66;
  }

  .familyReport .familyReportPrint .familyReportPrint_head {
    height: 3.75rem;
    background-color: #4da1ff;
    color: #fff;
    padding: 0 2rem;
  }

  .familyReport .familyReportPrint_headL {
    font-size: 20px;
  }

  .familyReport .familyReportPrint_headR {
    text-align: right;
  }

  .familyReport .familyReportPrint_headR .el-button {
    padding: 10px 0;
    width: 6.875rem;
  }

  .familyReport .familyReportPrint_headClose {
    font-size: 24px;
    margin-left: 4rem;
    cursor: pointer;
  }

  .familyReport .familyReportPrint_body {
    height: 100%;
    overflow: auto;
  }

  .familyReport .familyReportPrint_content {
    background-color: #fff;
    width: 610px;
    margin: auto;
    padding: 0 20px 100px 20px;
  }
</style>
