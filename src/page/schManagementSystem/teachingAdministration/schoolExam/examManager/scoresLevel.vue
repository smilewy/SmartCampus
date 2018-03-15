<template>
  <div class="scoresLevel">
    <el-row>
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <span class="breadcrumb"><router-link :to="{name:'testScribing',params:{examinationid:selectParam.examinationid}}"
                                            tag="span">考试划线</router-link><router-link
        :to="{name:'percentageSet',params:{examinationid:selectParam.examinationid}}"
        tag="span">分数率设置</router-link><span class="breadcrumb_active">分数等级设置</router-link></span></span>
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
          :prop="tableName.score"
          :label="tableName['name'+(idx+1)]" sortable="custom" v-for="(tableName,idx) in tableNameList" :key="idx"
          v-if="tableName['name'+(idx+1)]">
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
      <el-button type="primary" class="c_color" @click="editMsg(1)">快速设置</el-button>
    </el-row>
    <el-dialog
      title="编辑信息"
      :visible.sync="dialogVisible"
      :before-close="handleClose"
      :modal="false">
      <el-row class="levelLists">
        <el-row type="flex" align="middle" class="score_head">
          <el-col :span="10">等级名称</el-col>
          <el-col :span="14" class="score_item_l">分数占比</el-col>
        </el-row>
        <el-row type="flex" align="middle" class="score_item" v-for="(score,idx) in scoreList" :key="idx"
                v-if="score['name'+(idx+1)]">
          <el-col :span="10">
            {{score['name'+(idx+1)]}}
          </el-col>
          <el-col :span="14" class="score_item_l">
            <el-row type="flex" align="middle">
              <el-col :span="3" class="level">>=</el-col>
              <el-col :span="18" class="level">
                <el-input v-model="score['score'+(idx+1)]"/>
              </el-col>
              <el-col :span="3" class="level">%</el-col>
            </el-row>
          </el-col>
        </el-row>
        <el-row type="flex" align="middle" class="levelStart">
          <span>是否启用等级：</span>
          <el-switch
            v-model="levelStart"
            active-color="#09baa7"
            inactive-color="#ff4949"
            active-text="是"
            on-off="否">
          </el-switch>
        </el-row>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg(0)">保存</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      title="名称设置"
      :visible.sync="percentageDialogVisible"
      :before-close="handleClose"
      :modal="false">
      <el-row class="levelLists">
        <el-table
          :data="levelLists"
          border
          style="width: 100%">
          <el-table-column
            width="240"
            label="等级名称">
            <template slot-scope="scope">
              <el-input :maxlength="4" v-model="scope.row['name'+(scope.$index+1)]"/>
            </template>
          </el-table-column>
          <el-table-column
            label="分数占比">
            <template slot-scope="scope">
              <el-row type="flex" align="middle">
                <el-col :span="3" class="level">>=</el-col>
                <el-col :span="18" class="level">
                  <el-input v-model="scope.row['ratio'+(scope.$index+1)]"/>
                </el-col>
                <el-col :span="3" class="level">%</el-col>
              </el-row>
            </template>
          </el-table-column>
        </el-table>
        <el-row class="tips">
          <el-col :span="3">温馨提示：</el-col>
          <el-col :span="21">
            <p>1、为确保等级显示正常，请按降序设置各等级分数段；</p>
            <p>2、留空即表示不设置该等级</p>
          </el-col>
        </el-row>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg(1)">保存</el-button>
        <el-button @click="percentageDialogVisible = false">关闭</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from "@/assets/js/common";
  export default{
    data(){
      return {
        tableData: [],
        tableNameList: [],
        selectParam: {
          examinationid: '',
          field: '',
          order: ''
        },
        dialogVisible: false,
        form: {
          id: '',
          score1: '',
          score2: '',
          score3: '',
          score4: '',
          score5: '',
          score6: '',
          enable: ''
        },
        scoreList: [{
          name1: '',
          score1: ''
        }, {
          name2: '',
          score2: ''
        }, {
          name3: '',
          score3: ''
        }, {
          name4: '',
          score4: ''
        }, {
          name5: '',
          score5: ''
        }, {
          name6: '',
          score6: ''
        }],
        percentageDialogVisible: false,
        levelLists: [{
          name1: '',
          ratio1: ''
        }, {
          name2: '',
          ratio2: ''
        }, {
          name3: '',
          ratio3: ''
        }, {
          name4: '',
          ratio4: ''
        }, {
          name5: '',
          ratio5: ''
        }, {
          name6: '',
          ratio6: ''
        }],
        levelStart: true,
        loading: false
      }
    },
    created: function () {
      this.selectParam.examinationid = this.$route.params.examinationid;
      this.loadData(this.selectParam);
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/examManagerHome');
      },
      operationData(type){
        var self = this, data = {
          examinationid: self.selectParam.examinationid
        };
        if (type == 'clear') {
          self.$confirm('是否清空数据?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/Examination/exmanagement/type/score/typename/leveldel', 'post', data, function (res) {
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
          req.downloadFile('.scoresLevel', '/school/Examination/exmanagement/type/score/typename/levelexport?examinationid=' + self.selectParam.examinationid, 'post');
        }
      },
      sort(column){
        this.selectParam.field = column.prop || '';
        this.selectParam.order = column.order || '';
        this.loadData(this.selectParam);
      },
      editMsg(val, idx){
        if (val == 0) {  //编辑
          this.dialogVisible = true;
          this.form.id = this.tableData[idx].id;
          this.levelStart = this.tableData[idx].enable == '1';
          for (let [ix, obj] of this.tableNameList.entries()) {
            let name = 'name' + (ix + 1), score = 'score' + (ix + 1);
            this.scoreList[ix][name] = obj[name];
            this.scoreList[ix][score] = this.tableData[idx][score];
          }
        } else {   //快速设置
          this.percentageDialogVisible = true;
          if (this.tableNameList) {
            for (let [idx, obj] of this.tableNameList.entries()) {
              let name = 'name' + (idx + 1), ratio = 'ratio' + (idx + 1);
              this.levelLists[idx][name] = obj[name];
              this.levelLists[idx][ratio] = '';
            }
          }
        }
      },
      handleClose(done) {   //关闭弹框
        done();
      },
      saveMsg(val){
        var self = this, toData = {}, reg = /^([+-]?)\d*\.?\d+$/;
        if (val == 0) {   //编辑保存
          for (let [idx, obj] of self.scoreList.entries()) {
            let score = 'score' + (idx + 1);
            if (obj[score] && !reg.test(obj[score])) {
              self.vmMsgWarning('分数占比只能为数字，请正确填写！');
              return false;
            }
            self.form[score] = obj[score];
          }
          self.form.enable = self.levelStart ? 1 : 0;
          req.ajaxSend('/school/Examination/exmanagement/type/score/typename/levelupdate', 'post', self.form, function (res) {
            if (res.return) {
              self.vmMsgSuccess('修改成功！');
              self.loadData(self.selectParam);
              self.dialogVisible = false;
            } else {
              self.vmMsgError('修改失败！');
            }
          });
        } else {  //快速设置保存
          toData.examinationid = self.selectParam.examinationid;
          for (let [idx, obj] of self.levelLists.entries()) {
            let name = 'name' + (idx + 1), ratio = 'ratio' + (idx + 1);
            if (obj[ratio] && !reg.test(obj[ratio])) {
              self.vmMsgWarning('分数占比只能为数字，请正确填写！');
              return false;
            }
            toData[name] = obj[name];
            toData[ratio] = obj[ratio];
          }
          req.ajaxSend('/school/Examination/exmanagement/type/score/typename/levelinsert', 'post', toData, function (res) {
            if (res.return) {
              self.vmMsgSuccess('设置成功！');
              self.loadData(self.selectParam);
              self.percentageDialogVisible = false;
            } else {
              self.vmMsgError('设置失败！');
            }
          });
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Examination/exmanagement/type/score/typename/levelfind', 'post', data, function (res) {
          self.tableData = res.data;
          self.tableNameList = res.namelist;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .scoresLevel .edit {
    color: #20a0ff;
    cursor: pointer;
  }

  .scoresLevel .level {
    text-align: center;
  }

  .scoresLevel .tips {
    color: #888888;
    margin-top: 20px;
  }

  .scoresLevel .tips p {
    margin-bottom: 14px;
  }

  .levelLists .el-table--enable-row-hover .el-table__body tr:hover > td {
    background-color: #fff;
  }

  .scoresLevel .el-input__inner {
    height: 25px;
    text-align: center;
  }

  .scoresLevel .levelStart {
    margin-top: 20px;
  }

  .scoresLevel .el-dialog__body .el-table th {
    background-color: #deeefe;
  }

  .scoresLevel .el-dialog__body .el-table__footer-wrapper thead div, .scoresLevel .el-dialog__body .el-table__header-wrapper thead div {
    background-color: #deeefe;
  }

  .scoresLevel .score_head {
    background-color: #deeefe;
    font-weight: bold;
  }

  .scoresLevel .score_item, .scoresLevel .score_head {
    height: 40px;
    border-bottom: 1px solid #dfe6ec;
    text-align: center;
  }

  .scoresLevel .score_item_l {
    border-left: 1px solid #dfe6ec;
  }
</style>
