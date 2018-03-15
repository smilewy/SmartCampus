<template>
  <div class="quicklySplitclass">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-col :span="14">
        <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
          src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
          alt=""><span class="returnTxt">返回流程图</span></el-button>
        <h3>快速分班</h3>
      </el-col>
      <el-col :span="10" class="operationBtn">
        <el-button type="primary" @click="save('save')">保存</el-button>
      </el-col>
    </el-row>
    <el-row class="subClassDivision_row">
      <el-form ref="selectParamForm" :model="selectParam" :inline="true" :rules="selectParamRules" class="formInline">
        <el-form-item label="分班方式：" prop="way">
          <el-select v-model="selectParam.way" placeholder="请选择" class="splitClassType" @change="isShowPriority">
            <el-option label="按成绩" value="score"></el-option>
            <el-option label="随机" value="random"></el-option>
            <el-option label="蛇形" value="snake"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="班级序号：" prop="number">
          <el-select v-model="selectParam.number" placeholder="请选择" class="classNumber">
            <el-option label="按成绩" value="score"></el-option>
            <el-option label="随机" value="random"></el-option>
            <el-option label="按姓名拼音" value="name"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="性别：" prop="sex">
          <el-select :disabled="showSex" v-model="selectParam.sex" placeholder="请选择" class="sex">
            <el-option label="平均" value="ave"></el-option>
            <el-option label="随机" value="random"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="成绩相同：" prop="equal">
          <el-select v-model="selectParam.equal" placeholder="请选择" class="personNumber">
            <el-option label="增加班级人数" value="add"></el-option>
            <el-option label="随机" value="random"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" class="scoreQuery_btn" @click="save('create')">生成分班</el-button>
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="subClassDivision_row">
      <el-form :inline="true" class="formInline">
        <el-form-item label="成绩优先级(数字越小,优先级越高)">
        </el-form-item>
        <el-form-item :label="classLevel.level+'：'" v-for="classLevel in selectParam.priority"
                      :key="classLevel.levelId">
          <el-input-number :disabled="showPriority" v-model="classLevel.right" :min="0"></el-input-number>
        </el-form-item>
      </el-form>
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
    </el-row>
    <el-row class="alertsList">
      <el-table
        :data="tableData"
        max-height="600"
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
          min-width="150"
          label="姓名">
        </el-table-column>
        <el-table-column
          prop="sex"
          min-width="150"
          label="性别">
        </el-table-column>
        <el-table-column
          prop="preGrade"
          min-width="150"
          label="当前年级">
        </el-table-column>
        <el-table-column
          prop="preClass"
          min-width="150"
          label="当前班级">
        </el-table-column>
        <el-table-column
          prop="preSerialNumber"
          min-width="150"
          label="当前班级序号">
        </el-table-column>
        <el-table-column
          prop="proGrade"
          min-width="150"
          label="拟分年级">
        </el-table-column>
        <el-table-column
          prop="proClass"
          min-width="150"
          label="拟分班级">
        </el-table-column>
        <el-table-column
          prop="proSerialNumber"
          min-width="150"
          label="拟分班级序号">
        </el-table-column>
        <el-table-column
          min-width="150"
          label="指定到班">
          <template slot-scope="scope">
            <span v-if="scope.row.assign=='0'">否</span>
            <span v-if="scope.row.assign=='1'">是</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        tableData: [],
        selectParam: {
          planId: '',
          type: 'divide',
          way: '',
          number: '',
          sex: '',
          equal: 'add',
          priority: []
        },
        showPriority: true,
        showSex: false,
        selectParamRules: {
          way: [
            {required: true, message: '请选择分班方式', trigger: 'change'}
          ],
          number: [
            {required: true, message: '请选择班级序号', trigger: 'change'}
          ],
          sex: [
            {required: true, message: '请选择性别', trigger: 'change'}
          ],
          equal: [
            {required: true, message: '请选择成绩', trigger: 'change'}
          ]
        },
        loading: false
      }
    },
    created: function () {
      var self = this, data;
      self.selectParam.planId = self.$route.params.planId;
      data = {
        func: 'classLevel',
        param: {
          planId: self.selectParam.planId
        },
      };
      //得到班级级别
      req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
        for (let obj of res.data) {
          obj.right = 0;
          self.selectParam.priority.push(obj);
        }
      });
      //得到列表
      self.loadData();
    },
    methods: {
      returnFlowchart() {
        this.$router.push('/subClassDivisionHome');
      },
      isShowPriority() {
        this.showPriority = !(this.selectParam.way == 'score');
        this.showSex = this.selectParam.way == 'snake';
        if (this.selectParam.way == 'snake') {
          this.selectParam.sex = '平均';
        }
      },
      operationData(type) {
        let sAy = [], hdData;
        hdData = {
          name: '姓名',
          sex: '性别',
          preGrade: '当前年级',
          preClass: '当前班级',
          preSerialNumber: '当前班级序号',
          proGrade: '拟分年级',
          proClass: '拟分班级',
          proSerialNumber: '拟分班级序号',
          assign: '指定到班'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if (name == 'assign') {
              d[name] = obj[name] == '0' ? '否' : '是';
            } else {
              d[name] = obj[name] || '';
            }
          }
          sAy.push(d)
        }
        if (type == 'out') {
          req.downloadFile('.quicklySplitclass', '/school/DivideBranch/quickBranch?planId=' + this.selectParam.planId + '&type=download', 'post');
        } else if (type == 'copy') {
          req.copyTableData('.quicklySplitclass', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      save(type) {
        var self = this, data = {};
        if (type == 'create') {
          self.$refs['selectParamForm'].validate((valid) => {
            if (valid) {
              for (let name in self.selectParam) {
                data[name] = self.selectParam[name];
              }
              if (self.showPriority) {
                data.priority = [];
              }
              req.ajaxSend('/school/DivideBranch/quickBranch', 'post', data, function (res) {
                if (res.status == 1) {
                  self.vmMsgSuccess('生成成功！');
                  self.tableData = res.data;
                } else {
                  self.vmMsgError(res.msg);
                }
              })
            } else {
              return false;
            }
          });
        } else {
          if (self.tableData.length == 0) {
            self.vmMsgWarning('请先生成数据！');
            return false;
          }
          data = {
            planId: self.selectParam.planId,
            type: 'save',
            stuLists: []
          };
          for (let obj of self.tableData) {
            let m = {
              id: obj.id,
              proGrade: obj.proGrade,
              proClass: obj.proClass,
              proSerialNumber: obj.proSerialNumber,
              bcId: obj.bcId,
              assign: obj.assign
            };
            data.stuLists.push(m);
          }
          req.ajaxSend('/school/DivideBranch/quickBranch', 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('保存成功!');
              self.loadData();
            } else {
              self.vmMsgError(res.msg);
            }
          })
        }
      },
      loadData() {
        var self = this, data = {
          planId: self.selectParam.planId
        };
        self.loading = true;
        req.ajaxSend('/school/DivideBranch/quickBranch', 'post', data, function (res) {
          self.tableData = res.data;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .quicklySplitclass .el-button.scoreQuery_btn {
    padding: 0 30px;
    height: 30px;
    border-radius: 15px;
    font-size: .875rem;
  }

  .quicklySplitclass .splitClassType {
    width: 6.25rem;
  }

  .quicklySplitclass .classNumber, .quicklySplitclass .sex, .quicklySplitclass .personNumber {
    width: 9.375rem;
  }

  .quicklySplitclass .alertsBtn {
    margin: 1.5rem 0;
  }

  .quicklySplitclass .formInline .el-form-item {
    margin-right: 2.5rem;
    margin-bottom: 0;
  }
</style>
