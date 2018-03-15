<template>
  <div class="testScribing">
    <el-row>
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <span class="breadcrumb"><span class="breadcrumb_active">考试划线</span><router-link
        :to="{name:'percentageSet',params:{examinationid:selectParam.examinationid}}" tag="span">分数率设置</router-link>
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
          :prop="scoreName.score"
          :label="scoreName['name'+(idx+1)]" sortable="custom" v-for="(scoreName,idx) in scoreNameList" :key="idx"
          v-if="scoreName['name'+(idx+1)]">
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
      <el-button @click="operationData('clear')">清空数据</el-button>
      <el-button type="primary" class="c_color" @click="editMsg(1)">名称设置</el-button>
      <el-button type="primary" class="c_color" @click="editMsg(2)">快速划线</el-button>
    </el-row>
    <el-dialog
      title="修改信息"
      :visible.sync="dialogVisible"
      :before-close="handleClose"
      :modal="false">
      <el-row class="formMsg">
        <el-form ref="form" :rules="editMsgRules" :model="form" label-width="80px">
          <el-form-item label="科类：">
            <span>{{form.branch}}</span>
          </el-form-item>
          <el-form-item label="科目：">
            <span>{{form.subject}}</span>
          </el-form-item>
          <el-form-item :label="form.name1+'：'" prop="score1" v-if="form.name1">
            <el-input v-model="form.score1"></el-input>
          </el-form-item>
          <el-form-item :label="form.name2+'：'" prop="score2" v-if="form.name2">
            <el-input v-model="form.score2"></el-input>
          </el-form-item>
          <el-form-item :label="form.name3+'：'" prop="score3" v-if="form.name3">
            <el-input v-model="form.score3"></el-input>
          </el-form-item>
          <el-form-item :label="form.name4+'：'" prop="score4" v-if="form.name4">
            <el-input v-model="form.score4"></el-input>
          </el-form-item>
          <el-form-item :label="form.name5+'：'" prop="score5" v-if="form.name5">
            <el-input v-model="form.score5"></el-input>
          </el-form-item>
          <el-form-item :label="form.name6+'：'" prop="score6" v-if="form.name6">
            <el-input v-model.number="form.score6"></el-input>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg(0)">保存</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      title="名称设置"
      :visible.sync="nameDialogVisible"
      :before-close="handleClose"
      :modal="false">
      <el-row class="formMsg">
        <el-form ref="lineNameForm" :rules="lineNameRules" :model="tableName" label-width="120px">
          <el-form-item label="分数线1名称：" prop="name1">
            <el-input v-model="tableName.name1"></el-input>
          </el-form-item>
          <el-form-item label="分数线2名称：" prop="name2">
            <el-input v-model="tableName.name2"></el-input>
          </el-form-item>
          <el-form-item label="分数线3名称：" prop="name3">
            <el-input v-model="tableName.name3"></el-input>
          </el-form-item>
          <el-form-item label="分数线4名称：" prop="name4">
            <el-input v-model="tableName.name4"></el-input>
          </el-form-item>
          <el-form-item label="分数线5名称：" prop="name5">
            <el-input v-model="tableName.name5"></el-input>
          </el-form-item>
          <el-form-item label="分数线6名称：" prop="name6">
            <el-input v-model="tableName.name6"></el-input>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg(1)">保存</el-button>
        <el-button @click="nameDialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      title="快速划线"
      :visible.sync="scribeDialogVisible"
      :before-close="handleClose"
      :modal="false">
      <el-row class="formMsg scribeLine">
        <p class="tips">提示：人数不叠加</p>
        <el-row class="formSubject">
          <span class="sub" :class="{'subject_active':scribeParam.branchid==subject.branchid}"
                v-for="(subject,idx) in branchList"
                @click="chooseSubject(idx)">{{subject.branchname}}</span>
        </el-row>
        <el-form ref="scribeForm" :rules="editMsgRules" :model="scribeParam" label-width="120px">
          <el-form-item :label="scribeParam.name1+'：'" prop="score1" v-if="scribeParam.name1">
            <el-input v-model="scribeParam.score1"></el-input>
            <span class="unit">人</span>
          </el-form-item>
          <el-form-item :label="scribeParam.name2+'：'" prop="score2" v-if="scribeParam.name2">
            <el-input v-model="scribeParam.score2"></el-input>
            <span class="unit">人</span>
          </el-form-item>
          <el-form-item :label="scribeParam.name3+'：'" prop="score3" v-if="scribeParam.name3">
            <el-input v-model="scribeParam.score3"></el-input>
            <span class="unit">人</span>
          </el-form-item>
          <el-form-item :label="scribeParam.name4+'：'" prop="score4" v-if="scribeParam.name4">
            <el-input v-model="scribeParam.score4"></el-input>
            <span class="unit">人</span>
          </el-form-item>
          <el-form-item :label="scribeParam.name5+'：'" prop="score5" v-if="scribeParam.name5">
            <el-input v-model="scribeParam.score5"></el-input>
            <span class="unit">人</span>
          </el-form-item>
          <el-form-item :label="scribeParam.name6+'：'" prop="score6" v-if="scribeParam.name6">
            <el-input v-model="scribeParam.score6"></el-input>
            <span class="unit">人</span>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg(2)">保存</el-button>
        <el-button @click="scribeDialogVisible = false">取消</el-button>
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
        scoreNameList: [],
        branchList: [],
        selectParam: {
          examinationid: '',
          field: '',
          order: ''
        },
        dialogVisible: false,
        form: {},
        nameDialogVisible: false,
        tableName: {
          examinationid: '',
          name1: '',
          name2: '',
          name3: '',
          name4: '',
          name5: '',
          name6: '',
        },
        scribeDialogVisible: false,
        scribeParam: {
          examinationid: '',
          branchid: '',
          score1: '',
          score2: '',
          score3: '',
          score4: '',
          score5: '',
          score6: '',
          name1: '',
          name2: '',
          name3: '',
          name4: '',
          name5: '',
          name6: ''
        },
        lineNameRules: {
          name1: [
            {message: '请输入分数线1名称', trigger: 'blur'},
            {min: 1, max: 4, message: '长度在 1 到 4 个字符', trigger: 'blur'}
          ],
          name2: [
            {message: '请输入分数线2名称', trigger: 'blur'},
            {min: 1, max: 4, message: '长度在 1 到 4 个字符', trigger: 'blur'}
          ],
          name3: [
            {message: '请输入分数线1名称', trigger: 'blur'},
            {min: 1, max: 4, message: '长度在 1 到 4 个字符', trigger: 'blur'}
          ],
          name4: [
            {message: '请输入分数线4名称', trigger: 'blur'},
            {min: 1, max: 4, message: '长度在 1 到 4 个字符', trigger: 'blur'}
          ],
          name5: [
            {message: '请输入分数线5名称', trigger: 'blur'},
            {min: 1, max: 4, message: '长度在 1 到 4 个字符', trigger: 'blur'}
          ],
          name6: [
            {message: '请输入分数线6名称', trigger: 'blur'},
            {min: 1, max: 4, message: '长度在 1 到 4 个字符', trigger: 'blur'}
          ]
        },
        editMsgRules: {
          score1: [
            {validator: checkNumber, trigger: 'blur'}
          ],
          score2: [
            {validator: checkNumber, trigger: 'blur'}
          ],
          score3: [
            {validator: checkNumber, trigger: 'blur'}
          ],
          score4: [
            {validator: checkNumber, trigger: 'blur'}
          ],
          score5: [
            {validator: checkNumber, trigger: 'blur'}
          ],
          score6: [
            {validator: checkNumber, trigger: 'blur'}
          ]
        },
        loading: false
      }
    },
    created: function () {
      this.selectParam.examinationid = this.$route.params.examinationid;
      this.tableName.examinationid = this.selectParam.examinationid;
      this.scribeParam.examinationid = this.selectParam.examinationid;
      this.loadData(this.selectParam);
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/examManagerHome');
      },
      operationData(type){
        var self = this, tData = {
          examinationid: self.selectParam.examinationid
        };
        if (type == 'clear') {
          self.$confirm('是否清空数据?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/Examination/exmanagement/type/score/typename/scoredel', 'post', tData, function (res) {
              if (res.return) {
                self.vmMsgSuccess('清空成功！');
                self.loadData(self.selectParam);
              } else {
                self.vmMsgError('清空失败！');
              }
            })
          }).catch(() => {
          });
        } else {
          req.downloadFile('.testScribing', '/school/Examination/exmanagement/type/score/typename/scoreexport?examinationid=' + self.selectParam.examinationid, 'post');
        }
      },
      sort(column){
        this.selectParam.field = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      editMsg(val, idx){
        var self = this, tData;
        if (val == 0) {   //编辑信息
          self.dialogVisible = true;
          tData = {
            examinationid: self.selectParam.examinationid
          };
          $.extend(self.form, self.tableData[idx]);
          for (let [ix, obj] of this.scoreNameList.entries()) {
            let nme = 'name' + (ix + 1);
            self.form[nme] = obj[nme];
          }
        } else if (val == 1) {  //名称设置
          self.nameDialogVisible = true;
          for (let [ix, obj] of this.scoreNameList.entries()) {
            let nme = 'name' + (ix + 1);
            self.tableName[nme] = obj[nme];
          }
        } else {  //快速划线
          self.scribeDialogVisible = true;
          self.scribeParam.branchid = self.branchList[0].branchid;
          if (this.scoreNameList) {
            for (let [ix, obj] of this.scoreNameList.entries()) {
              let nme = 'name' + (ix + 1), score = 'score' + (ix + 1);
              self.scribeParam[nme] = obj[nme];
              self.scribeParam[score] = '';
            }
          }
        }
      },
      handleClose(done) {   //关闭弹框
        done();
      },
      chooseSubject(val){
        this.scribeParam.branchid = this.branchList[val].branchid;
      },
      saveMsg(val){
        var self = this, dData = {};
        if (val == 0) {  //编辑信息保存
          dData.examinationid = self.selectParam.examinationid;
          dData.score1 = self.form.score1;
          dData.score2 = self.form.score2;
          dData.score3 = self.form.score3;
          dData.score4 = self.form.score4;
          dData.score5 = self.form.score5;
          dData.score6 = self.form.score6;
          dData.id = self.form.id;
          this.$refs['form'].validate((valid) => {
            if (valid) {
              req.ajaxSend('/school/Examination/exmanagement/type/score/typename/scoreupdate', 'post', dData, function (res) {
                if (res.return) {
                  self.vmMsgSuccess('设置成功！');
                  self.dialogVisible = false;
                  self.loadData(self.selectParam);
                } else {
                  self.vmMsgError('设置失败！');
                }
              });
            } else {
              return false;
            }
          });
        } else if (val == 1) {   //名称设置保存
          this.$refs['lineNameForm'].validate((valid) => {
            if (valid) {
              req.ajaxSend('/school/Examination/exmanagement/type/score/typename/scorename', 'post', self.tableName, function (res) {
                if (res.return) {
                  self.vmMsgSuccess('设置成功！');
                  self.nameDialogVisible = false;
                  self.loadData(self.selectParam);
                } else {
                  self.vmMsgError('设置失败！');
                }
              });
            } else {
              return false;
            }
          });
        } else {   //快速划线保存
          this.$refs['scribeForm'].validate((valid) => {
            if (valid) {
              req.ajaxSend('/school/Examination/exmanagement/type/score/typename/scoreupdate', 'post', self.scribeParam, function (res) {
                if (res.return) {
                  self.vmMsgSuccess('设置成功！');
                  self.scribeDialogVisible = false;
                  self.loadData(self.selectParam);
                } else {
                  self.vmMsgError('设置失败！');
                }
              });
            } else {
              return false;
            }
          });
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Examination/exmanagement/type/score/typename/scorefind', 'post', data, function (res) {
          self.tableData = res.data;
          self.scoreNameList = res.scorenamelist;
          self.branchList = res.branchlist;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .testScribing .formMsg {
    width: 80%;
    margin: auto;
  }

  .testScribing .formSubject {
    text-align: center;
    margin-bottom: 20px;
    font-size: 18px;
  }

  .testScribing .formSubject .sub {
    cursor: pointer;
    padding: 0 20px;
  }

  .testScribing .formSubject .sub + .sub {
    border-left: 2px solid #d2d2d2;
  }

  .testScribing .formSubject .subject_active {
    color: #4da1ff;
  }

  .testScribing .scribeLine .el-input {
    width: 80%;
  }

  .testScribing .unit {
    margin-left: 10px;
  }

  .testScribing .edit {
    color: #ff5b5a;
    cursor: pointer;
  }

  .testScribing .tips {
    color: #999999;
  }
</style>
