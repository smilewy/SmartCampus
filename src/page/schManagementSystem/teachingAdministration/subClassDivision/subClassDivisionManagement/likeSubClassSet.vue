<template>
  <div class="likeSubClassSet">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-col :span="14">
        <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
          src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
          alt=""><span class="returnTxt">返回流程图</span></el-button>
        <h3>拟分班级设置</h3>
      </el-col>
      <el-col :span="10" class="operationBtns">
        <el-button type="primary" @click="addSubject('quicklySet')">快速设置</el-button>
        <el-button type="primary" @click="saveMsg">保存</el-button>
      </el-col>
    </el-row>
    <el-row>
      <el-form :inline="true" class="formGrade">
        <el-form-item label="年级：">
          <el-select v-model="selectParam.gradeId" placeholder="请选择" @change="chooseGrade">
            <el-option :label="grade.znName" :value="grade.id" v-for="grade in gradeList" :key="grade.id"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="温馨提示：分班完成后，新班级将覆盖所选年级的现有班级。">
        </el-form-item>
      </el-form>
    </el-row>
    <el-row class="likeSubClassSet_row">参与分班人数：<span v-for="(major,ix) in majorList" :key="ix">{{major.name}}{{major.total}}（<span
      v-for="(maj,idx) in major.major" :key="idx">{{maj.name}}{{maj.number}}，</span>）</span>
      班级容纳人数：{{totalNum}}
    </el-row>
    <el-row>未参与分班人数：{{notNum}}</el-row>
    <el-row class="subClassDivision_row d_line"></el-row>
    <el-row class="alertsBtn">
      <el-button class="filt" title="添加" @click="addSubject('add')">
        <img class="filt_unactive"
             src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add.png"
             alt="">
        <img class="filt_active"
             src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png"
             alt="">
      </el-button>
    </el-row>
    <el-row class="alertsList">
      <el-table
        ref="table"
        :data="tableData"
        style="width: 100%"
        v-loading="loading"
        element-loading-text="拼命加载中"
      >
        <el-table-column type="expand">
          <template slot-scope="props">
            <el-table
              :data="props.row.activeMajorList"
              border
              style="width: 100%"
              class="childTable"
            >
              <el-table-column
                prop="name"
                label="专业名称">
              </el-table-column>
              <el-table-column
                label="容纳人数">
                <template slot-scope="scope">
                  <el-input-number v-model="scope.row.number" :min="0" @input="getRowNumber"></el-input-number>
                </template>
              </el-table-column>
            </el-table>
          </template>
        </el-table-column>
        <el-table-column
          type="selection"
          width="55">
        </el-table-column>
        <el-table-column
          label="班级名称">
          <template slot-scope="scope">
            <input type="text" class="classNameOrigin" maxlength="10" v-model="scope.row.className"/>
          </template>
        </el-table-column>
        <el-table-column
          label="科类志愿">
          <template slot-scope="scope">
            <el-select v-model="scope.row.branchId" placeholder="请选择" @change="changeBranch(scope.$index)">
              <el-option :label="branch.name" :value="branch.branchId" v-for="branch in branchList"
                         :key="branch.branchId"></el-option>
            </el-select>
          </template>
        </el-table-column>
        <el-table-column
          label="班级级别">
          <template slot-scope="scope">
            <el-select v-model="scope.row.levelId" placeholder="请选择" @change="changeClassLevel(scope.$index)">
              <el-option :label="classLevel.level" :value="classLevel.levelId" v-for="classLevel in classLevelList"
                         :key="classLevel.levelId"></el-option>
            </el-select>
          </template>
        </el-table-column>
        <el-table-column
          prop="number"
          label="总容纳人数">
        </el-table-column>
        <el-table-column
          label="操作">
          <template slot-scope="scope">
            <span class="edit" @click="deleteData(scope.$index)">删除</span>
          </template>
        </el-table-column>
      </el-table>
    </el-row>
    <el-dialog
      title="添加信息"
      :visible.sync="dialogVisible"
      :before-close="handleCloseDialog"
      :modal="false">
      <el-row class="formMsg">
        <el-form ref="form" :model="form" :rules="formRules" label-width="120px">
          <el-form-item label="班级名称：" prop="className">
            <el-input placeholder="请输入班级名称，如1班" v-model="form.className"></el-input>
          </el-form-item>
          <el-form-item label="科类志愿：" prop="branchId">
            <el-select v-model="form.branchId" placeholder="请选择">
              <el-option :label="branch.name" :value="branch.branchId" v-for="branch in branchList"
                         :key="branch.branchId"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="班级级别：" prop="levelId">
            <el-select v-model="form.levelId" placeholder="请选择" @change="changeNewLevel">
              <el-option :label="classLevel.level" :value="classLevel.levelId" v-for="classLevel in classLevelList"
                         :key="classLevel.levelId"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg('add')">确定</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
    <el-dialog
      title="快速设置"
      :visible.sync="testDialogVisible"
      :before-close="handleCloseDialog"
      :modal="false">
      <el-row class="formMsg">
        <el-form ref="formSet" :model="testForm" :rules="testFormRules" label-width="120px">
          <el-form-item label="班级人数：" prop="accommodate">
            <el-input placeholder="请输入班级容纳人数" v-model="testForm.accommodate"></el-input>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="saveMsg('set')">确定</el-button>
        <el-button @click="testDialogVisible = false">取消</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      var reg = /^[1-9]\d*$/;
      var checkNumber = (rule, value, callback) => {
        if (!reg.test(value)) {
          callback(new Error('请输入数字值,且大于0'));
        } else {
          callback();
        }
      };
      return {
        gradeList: [],
        majorList: [],
        totalNum: 0,
        notNum: 0,
        tableData: [],
        classLevelList: [],
        branchList: [],
        dialogVisible: false,
        testDialogVisible: false,
        selectParam: {
          planId: '',
          gradeId: ''
        },
        form: {
          className: '',
          branchId: '',
          branch: '',
          level: '',
          levelId: '',
          number: 0,
          activeMajorList: [],
          majorNumber: []
        },
        testForm: {
          accommodate: ''
        },
        formRules: {
          className: [
            {required: true, validator: checkNumber, trigger: 'blur'}
          ],
          branchId: [
            {required: true, message: '请选择科类志愿', trigger: 'change'}
          ],
          levelId: [
            {required: true, message: '请选择班级级别', trigger: 'change'}
          ]
        },
        testFormRules: {
          accommodate: [
            {required: true, validator: checkNumber, trigger: 'blur'}
          ]
        },
        loading: false
      }
    },
    created: function () {
      var self = this, data1, data2, data3 = {
        func: 'getLevel'
      };
      self.selectParam.planId = self.$route.params.planId;
      data1 = {
        func: 'getGrade',
        param: {
          planId: self.selectParam.planId
        }
      };
      data2 = {
        func: 'getWish',
        param: {
          planId: self.selectParam.planId
        }
      };
      //得到年级
      req.ajaxSend('/school/DivideBranch/common', 'post', data1, function (res) {
        self.gradeList = res.data;
      });
      //得到科类
      req.ajaxSend('/school/DivideBranch/common', 'post', data2, function (res) {
        for (let obj of res.data) {
          for (let mbj of obj.majors) {
            mbj.number = 0;
          }
        }
        self.branchList = res.data;
      });
      //得到班级类型
      req.ajaxSend('/school/DivideBranch/common', 'post', data3, function (res) {
        self.classLevelList = res;
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      },
      chooseGrade(){  //选择年级切换数据
        this.loadData(this.selectParam);
      },
      changeBranch(idx){
        for (let obj of this.tableData[idx].majorNumber) {
          if (obj.branchId == this.tableData[idx].branchId) {
            this.tableData[idx].branch = obj.name;
            this.tableData[idx].activeMajorList = obj.majors;
          }
        }

      },
      changeClassLevel(idx){
        for (let obj of this.classLevelList) {
          if (obj.levelId == this.tableData[idx].levelId) {
            this.tableData[idx].level = obj.level;
          }
        }
      },
      changeNewLevel(){
        for (let obj of this.classLevelList) {
          if (obj.levelId == this.form.levelId) {
            this.form.level = obj.level;
          }
        }
      },
      getRowNumber(){ //设置容纳人数 input
        for (let obj of this.tableData) {
          obj.number = 0;
          for (let mbj of obj.activeMajorList) {
            obj.number = Number.parseInt(obj.number) + Number.parseInt(mbj.number);
          }
        }
        this.totalNum = 0;
        this.countTotal(this.tableData);
      },
      addSubject(type){   //打开弹框
        if (!this.selectParam.gradeId) {
          this.vmMsgWarning('请选择年级！');
          return false;
        }
        if (type == 'add') {  //添加
          this.form.number = 0;
          this.form.majorNumber = this.branchList;
          this.dialogVisible = true;
        } else if (type == 'quicklySet') {   //快速设置
          this.testForm.subject = '';
          this.testDialogVisible = true;
        }
      },
      handleCloseDialog(done){   //关闭弹框
        done();
      },
      deleteData(idx){    //删除
        this.tableData.splice(idx, 1);
      },
      saveMsg(type){   //保存信息
        var self = this, data;
        if (type == 'add') {   //添加行
          self.$refs['form'].validate((valid) => {
            if (valid) {
              let d = {};
              self.dialogVisible = false;
              self.form.activeMajorList = [];
              for (let obj of self.branchList) {
                if (obj.branchId == self.form.branchId) {
                  self.form.branch = obj.name;
                  for (let mbj of obj.majors) {
                    let d = {};
                    $.extend(d, mbj);
                    self.form.activeMajorList.push(d);
                  }
                }
              }
              $.extend(d, self.form);
              self.tableData.push(d);
            } else {
              return false;
            }
          });
        } else if (type == 'set') {   //设置
          data = {
            planId: self.selectParam.planId,
            type: 'set',
            grade: '',
            number: self.testForm.accommodate,
            gradeId: self.selectParam.gradeId
          };
          for (let obj of self.gradeList) {
            if (obj.id == self.selectParam.gradeId) {
              data.grade = obj.znName;
            }
          }
          self.$refs['formSet'].validate((valid) => {
            if (valid) {
              self.$confirm(`“班级级别”及“专业容纳人数”需要手工微调，是否继续?（班级人数：${self.testForm.accommodate}人/班）`, '提示', {
                confirmButtonText: '继续',
                cancelButtonText: '取消',
                type: 'warning'
              }).then(() => {
                req.ajaxSend('/school/DivideBranch/classSetting', 'post', data, function (res) {
                  if (res.status == 1) {
                    self.vmMsgSuccess('快速设置成功!');
                    self.testDialogVisible = false;
                    for (let obj of res.data) {
                      for (let mbj of obj.majorNumber) {
                        if (obj.branchId == mbj.branchId) {
                          obj.activeMajorList = mbj.majors;
                        }
                      }
                    }
                    self.tableData = res.data;
                    self.totalNum = 0;
                    self.countTotal(res.data);
                  } else {
                    self.vmMsgError(res.msg);
                  }
                });
              }).catch(() => {
              });
            } else {
              return false;
            }
          });
        } else {    //批量保存
          data = {
            planId: self.selectParam.planId,
            type: 'save',
            grade: '',
            class: []
          };
          for (let obj of self.gradeList) {
            if (obj.id == self.selectParam.gradeId) {
              data.grade = obj.znName;
            }
          }
          for (let obj of self.tableData) {
            let d = {
              id: obj.id || '',
              name: obj.className,
              grade: data.grade,
              gradeId: self.selectParam.gradeId,
              majorId: [],
              branchId: obj.branchId,
              branch: obj.branch,
              level: obj.level,
              levelId: obj.levelId,
              number: obj.number,
              majorNumber: []
            };
            for (let mbj of obj.activeMajorList) {
              let m = {
                wishId: mbj.wishId,
                major: mbj.name,
                number: mbj.number
              };
              d.majorId.push(mbj.wishId);
              d.majorNumber.push(m);
            }
            data.class.push(d);
          }
          req.ajaxSend('/school/DivideBranch/classSetting', 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('保存成功！');
              self.loadData(self.selectParam);
            } else {
              self.vmMsgError(res.msg);
            }
          })
        }
      },
      countTotal(data){    //计算班级容纳人数
        for (let obj of data) {
          this.totalNum += Number.parseInt(obj.number);
        }
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/DivideBranch/classSetting', 'post', data, function (res) {
          self.loading = false;
          for (let obj of res.data) {
            for (let mbj of obj.majorNumber) {
              if (obj.branchId == mbj.branchId) {
                obj.activeMajorList = mbj.majors;
              }
            }
          }
          self.tableData = res.data;
          self.majorList = res.stu;
          self.notNum = res.not;
          self.totalNum = 0;
          self.countTotal(res.data);
        })
      }
    }
  }
</script>
<style>
  .likeSubClassSet .el-table__expanded-cell {
    padding: 0;
  }

  .likeSubClassSet .el-table.childTable {

  }

  .likeSubClassSet .alertsList .el-table.childTable th {
    background-color: #deeefe;
    height: 3rem;
  }

  .likeSubClassSet .alertsList .el-table.childTable td {
    height: 3.5rem;
    font-size: .875rem;
  }

  .likeSubClassSet .childTable .el-table__footer-wrapper thead div, .likeSubClassSet .childTable .el-table__header-wrapper thead div {
    background-color: #deeefe;
    color: #666666;
  }

  .likeSubClassSet .el-table.childTable td {
    background-color: #f0f0f0;
  }

  .likeSubClassSet .alertsList input.classNameOrigin {
    height: 20px;
    text-align: center;
    border: none;
  }

  .likeSubClassSet .formGrade .el-form-item {
    margin-bottom: 0;
    margin-right: 2rem;
  }

  .likeSubClassSet .el-input-number .el-input__inner {
    text-align: center;
  }

  .likeSubClassSet .likeSubClassSet_row {
    margin: 1rem 0;
  }
</style>
