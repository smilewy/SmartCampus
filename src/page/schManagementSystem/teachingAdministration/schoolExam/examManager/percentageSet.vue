<template>
  <div class="percentageSet">
    <el-row>
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <span class="breadcrumb"><router-link :to="{name:'testScribing',params:{examinationid:selectParam.examinationid}}"
                                            tag="span">考试划线</router-link><span class="breadcrumb_active">分数率设置</span>
        <!--<router-link
        tag="span"
        :to="{name:'scoresLevel',params:{examinationid:selectParam.examinationid}}">分数等级设置</router-link>-->
      </span>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row type="flex" align="middle" class="alertsBtn">
      <el-button class="delete" title="导出" @click="operationData('out')">
        <img class="delete_unactive"
             src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
             alt="">
        <img class="delete_active"
             src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
             alt="">
      </el-button>
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
          prop="branch"
          label="科类" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="subject"
          label="科目" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="fullscore"
          label="满分" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="excellent"
          label="优秀（>=）" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="pass"
          label="及格（>=）" sortable="custom">
        </el-table-column>
        <el-table-column
          prop="lowscore"
          label="低分（>=）" sortable="custom">
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="edit" @click="editMsg(0,scope.$index)">编辑</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-row class="testOperation_btn">
      <el-button type="primary" class="c_color" @click="editMsg(1)">快速设置</el-button>
    </el-row>
    <el-dialog
      title="修改信息"
      :visible.sync="dialogVisible"
      :before-close="handleClose"
      :modal="false">
      <el-row class="formMsg">
        <el-form ref="form" :model="form" :rules="formRules" label-width="120px">
          <el-form-item label="科类：">
            <span>{{form.branch}}</span>
          </el-form-item>
          <el-form-item label="科目：">
            <span>{{form.subject}}</span>
          </el-form-item>
          <el-form-item label="满分：">
            <el-input readonly v-model="form.fullscore"></el-input>
          </el-form-item>
          <el-form-item label="优秀（>=）：" prop="excellent">
            <el-input v-model="form.excellent"></el-input>
          </el-form-item>
          <el-form-item label="及格（>=）：" prop="pass">
            <el-input v-model="form.pass"></el-input>
          </el-form-item>
          <el-form-item label="低分（>=）：" prop="lowscore">
            <el-input v-model="form.lowscore"></el-input>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg(0)">保存</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      title="快速设置"
      :visible.sync="percentageDialogVisible"
      :before-close="handleClose"
      :modal="false">
      <el-row class="formMsg scribeLine">
        <p class="tips">提示：设置满分的百分比</p>
        <el-form ref="setForm" :model="percentageParam" :rules="formRules" label-width="120px">
          <el-form-item label="优秀（>=）：" prop="excellent">
            <el-input v-model="percentageParam.excellent"></el-input>
            <span class="unit">%</span>
          </el-form-item>
          <el-form-item label="及格（>=）：" prop="pass">
            <el-input v-model="percentageParam.pass"></el-input>
            <span class="unit">%</span>
          </el-form-item>
          <el-form-item label="低分（>=）：" prop="lowscore">
            <el-input v-model="percentageParam.lowscore"></el-input>
            <span class="unit">%</span>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg(1)">保存</el-button>
        <el-button @click="percentageDialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      var checkNumber = (rule, value, callback) => {
        if (value && value !== '') {
          let reg = /^([+-]?)\d*\.?\d+$/;
          if (!reg.test(value)) {
            callback(new Error('请输入数字'));
          } else {
            callback();
          }
        } else {
          callback();
        }
      };
      return {
        tableData: [],
        selectParam: {
          examinationid: '',
          field: '',
          order: ''
        },
        dialogVisible: false,
        form: {},
        percentageDialogVisible: false,
        percentageParam: {
          examinationid: '',
          excellent: '',
          pass: '',
          lowscore: ''
        },
        formRules: {
          excellent: [
            {validator: checkNumber, trigger: 'blur'}
          ],
          pass: [
            {validator: checkNumber, trigger: 'blur'}
          ],
          lowscore: [
            {validator: checkNumber, trigger: 'blur'}
          ]
        },
        loading: false
      }
    },
    created: function () {
      this.selectParam.examinationid = this.$route.params.examinationid;
      this.percentageParam.examinationid = this.selectParam.examinationid;
      this.loadData(this.selectParam);
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/examManagerHome');
      },
      sort(column){
        this.selectParam.field = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      editMsg(val, idx){
        if (val == 0) {  //编辑
          this.dialogVisible = true;
          $.extend(this.form, this.tableData[idx]);
        } else {  //快速设置
          this.percentageDialogVisible = true;
        }
      },
      handleClose(done) {   //关闭弹框
        done();
      },
      saveMsg(val){
        var self = this;
        if (val == 0) { //编辑保存
          self.$refs['form'].validate((valid) => {
            if (valid) {
              let data = {
                examinationid: self.selectParam.examinationid,
                excellent: self.form.excellent,
                pass: self.form.pass,
                lowscore: self.form.lowscore,
                id: self.form.id
              };
              let st1 = Number(data.excellent) > Number(data.pass);
              let st2 = Number(data.pass) > Number(data.lowscore);
              if (st1 && st2) {
                req.ajaxSend('/school/Examination/exmanagement/type/score/typename/ratioup', 'post', data, function (res) {
                  if (res.return) {
                    self.vmMsgSuccess('修改成功！');
                    self.dialogVisible = false;
                    self.loadData(self.selectParam);
                  } else {
                    self.vmMsgError('修改失败！');
                  }
                })
              } else {
                self.vmMsgWarning('优秀分数必须大于及格分数，及格分数必须大于低分，请检查输入！');
                return false;
              }
            } else {
              return false;
            }
          });
        } else {
          self.$refs['setForm'].validate((valid) => {
            if (valid) {
              let st1 = Number(self.percentageParam.excellent) > Number(self.percentageParam.pass);
              let st2 = Number(self.percentageParam.pass) > Number(self.percentageParam.lowscore);
              if (st1 && st2) {
                req.ajaxSend('/school/Examination/exmanagement/type/score/typename/ratioup', 'post', self.percentageParam, function (res) {
                  if (res.return) {
                    self.vmMsgSuccess('设置成功！');
                    self.percentageDialogVisible = false;
                    self.loadData(self.selectParam);
                  } else {
                    self.vmMsgError('设置失败！');
                  }
                })
              } else {
                self.vmMsgWarning('优秀分数必须大于及格分数，及格分数必须大于低分，请检查输入！');
                return false;
              }
            } else {
              return false;
            }
          });
        }
      },
      operationData(type){
        var self = this, data = {
          examinationid: self.selectParam.examinationid
        };
        req.downloadFile('.percentageSet', '/school/Examination/exmanagement/type/score/typename/ratioexport?examinationid=' + self.selectParam.examinationid, 'post');
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Examination/exmanagement/type/score/typename/ratiofind', 'post', data, function (res) {
          self.tableData = res;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .percentageSet .formMsg {
    width: 80%;
    margin: auto;
  }

  .percentageSet .unit {
    margin-left: 10px;
  }

  .percentageSet .scribeLine .el-input {
    width: 80%;
  }

  .percentageSet .edit {
    color: #ff5b5a;
    cursor: pointer;
  }

  .percentageSet .tips {
    color: #999999;
    margin-bottom: 1.5rem;
  }
</style>
