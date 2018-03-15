<template>
  <div class="manuallyAdjustment">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>手动调整</h3>
    </el-row>
    <el-row type="flex" align="middle">
      <span>调整方式：</span>
      <span class="adjustment_btn">
          <span :class="{adjustment_btn_active:adjustmentType=='student'}" @click="changeType('student')">指定学生换班</span>
          <span :class="{adjustment_btn_active:adjustmentType=='adjacent'}"
                @click="changeType('adjacent')">相邻分数学生换班</span>
        </span>
      <span>
          注：1、相邻分数学生互换，不能跨科类操作。2、跨科类指定学生换班不会影响学生志愿专业
        </span>
    </el-row>
    <el-row class="manuallyAdjustment_row d_line"></el-row>
    <el-row :gutter="20" class="manuallyAdjustment_row">
      <el-col :span="10">
        <el-row class="pNum">当前人数/容纳人数</el-row>
        <el-row class="manuallyAdjustment_rows" v-for="(classData,ix) in classList" :key="ix">
          <el-row class="exam_subTitle">{{classData.name}}</el-row>
          <el-row class="subjects">
            <div class="subject" :class="{'disable':data.checked}" v-for="(data,idx) in classData.class"
                 :key="data.classId" @click.prevent="selectStudent(ix,idx)">
              <h5>{{data.realNumber}} / {{data.number}}</h5>
              <p>{{data.grade}}{{data.class}}{{data.level}}</p>
              <span class="joinBtn" :class="{'disable':data.checked&&multipleSelection.length!=0}"
                    @click.stop="joinClass(ix,idx)">加入班级</span>
            </div>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="14">
        <el-row type="flex" align="middle" justify="center" class="listHeader">
          <el-col :span="7">参与分班学生名单</el-col>
          <el-col :span="16">
            <div class="g-fuzzyInput">
              <el-input
                placeholder="请输入姓名"
                suffix-icon="el-icon-search"
                v-model="selectParam.param.key"
                @change="goSearch">
              </el-input>
            </div>
          </el-col>
        </el-row>
        <el-row class="studentsList">
          <el-table
            :data="tableData"
            style="width: 100%"
            max-height="650"
            border
            @selection-change="handleSelectionChange"
            v-loading="loading"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              type="selection"
              width="55">
            </el-table-column>
            <el-table-column
              prop="name"
              min-width="120"
              label="姓名">
            </el-table-column>
            <el-table-column
              prop="sex"
              min-width="120"
              label="性别">
            </el-table-column>
            <el-table-column
              prop="branch"
              min-width="120"
              label="科类">
            </el-table-column>
            <el-table-column
              prop="major"
              min-width="120"
              label="志愿专业">
            </el-table-column>
            <el-table-column
              prop="proGrade"
              min-width="120"
              label="拟分年级">
            </el-table-column>
            <el-table-column
              prop="proClass"
              min-width="120"
              label="拟分班级">
            </el-table-column>
            <el-table-column
              min-width="150"
              prop="assign"
              label="指定班级">
              <template slot-scope="props">
                <span v-if="props.row.assign==1">是</span>
                <span v-if="props.row.assign==0">否</span>
              </template>
            </el-table-column>
            <el-table-column
              min-width="150"
              prop="score"
              label="合成总分">
            </el-table-column>
          </el-table>
        </el-row>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        tableData: [],
        classList: [],
        selectParam: {
          func: 'classStu',
          param: {
            planId: '',
            classId: '',
            gradeId: '',
            key: ''
          }
        },
        multipleSelection: [],
        adjustmentType: 'student',
        loading: false
      }
    },
    created: function () {
      var self = this, data;
      self.selectParam.param.planId = self.$route.params.planId;
      data = {
        planId: self.selectParam.param.planId
      };
      //查询班级列表
      req.ajaxSend('/school/DivideBranch/manualAdjust', 'post', data, function (res) {
        self.classList = res.data;
        for (let obj of self.classList) {
          for (let mbj of obj.class) {
            mbj.checked = false;
          }
        }
      });
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/subClassDivisionHome');
      },
      changeType(type){  //切换调整方式
        this.adjustmentType = type;
      },
      selectStudent(ix1, ix2){
        for (let obj of this.classList) {
          for (let mbj of obj.class) {
            mbj.checked = false;
          }
        }
        this.classList[ix1].class[ix2].checked = true;
        this.selectParam.param.classId = this.classList[ix1].class[ix2].classId;
        this.selectParam.param.gradeId = this.classList[ix1].class[ix2].gradeId;
        this.loadData(this.selectParam);
      },
      joinClass(ix1, ix2){
        var self = this, len = self.multipleSelection.length, data, url, nameStr = '';
        if (!self.classList[ix1].class[ix2].checked || len == 0) {
          return false;
        }
        if (self.adjustmentType == 'student') {  //指定学生到班
          url = '/school/DivideBranch/assignStudent';
          data = {
            planId: self.selectParam.param.planId,
            ids: [],
            proClass: {
              classId: self.classList[ix1].class[ix2].classId,
              class: self.classList[ix1].class[ix2].class,
              grade: self.classList[ix1].class[ix2].grade
            }
          };
          for (let [key, obj] of self.multipleSelection.entries()) {
            let d = {
              id: obj.id
            };
            data.ids.push(d);
            nameStr += key === len - 1 ? obj.name : obj.name + '、';
          }
        } else {   //相邻分数学生互换
          url = '/school/DivideBranch/equalChange';
          data = {
            planId: self.selectParam.param.planId,
            ids: [],
            proClass: {
              classId: self.classList[ix1].class[ix2].classId,
              class: self.classList[ix1].class[ix2].class,
              grade: self.classList[ix1].class[ix2].grade
            },
            preClass: {}
          };
          for (let [key, obj] of self.multipleSelection.entries()) {
            if (obj.branch != self.classList[ix1].name) {
              self.vmMsgWarning('相邻分数学生互换，不能跨科类操作！');
              return false;
            }
            let d = {
              id: obj.id,
              serialNumber: obj.proSerialNumber,
              rank: obj.rank
            };
            data.preClass[obj.id] = {
              classId: obj.bcId,
              class: obj.proClass,
              grade: obj.proGrade
            };
            data.ids.push(d);
            nameStr += key === len - 1 ? obj.name : obj.name + '、';
          }
        }
        self.$confirm(`替换学生${nameStr}，是否确定替换？`, '提示', {
          confirmButtonText: '替换',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend(url, 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('调整成功！');
              self.loadData(self.selectParam);
            } else {
              self.vmMsgError(res.msg);
            }
          })
        }).catch(() => {
        });
      },
      goSearch() {  //查询
        if (!this.selectParam.param.classId) {
          this.vmMsgWarning('请先选择班级！');
          return false;
        }
        this.loadData(this.selectParam);
      },
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      loadData(data){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
          self.tableData = res.data;
          self.loading = false;
        })
      }
    }
  }
</script>
<style>
  .manuallyAdjustment {
    font-size: 14px;
  }

  .manuallyAdjustment .manuallyAdjustment_row, .manuallyAdjustment .manuallyAdjustment_rows {
    margin: 1rem 0;
  }

  .manuallyAdjustment .manuallyAdjustment_rows + .manuallyAdjustment_rows {
    margin: 4.5rem 0;
  }

  .manuallyAdjustment .adjustment_btn {
    display: inline-block;
    width: 17.5rem;
    height: 30px;
    line-height: 30px;
    border-radius: 4px;
    border: 1px solid #d2d2d2;
    font-size: 0;
  }

  .manuallyAdjustment .adjustment_btn + span {
    margin-left: 2rem;
  }

  .manuallyAdjustment .adjustment_btn > span {
    width: 50%;
    display: inline-block;
    text-align: center;
    font-size: .875rem;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    cursor: pointer;
  }

  .manuallyAdjustment .adjustment_btn > span:first-child {
    border-right: 1px solid #d2d2d2;
  }

  .manuallyAdjustment .adjustment_btn span.adjustment_btn_active {
    color: #fff;
    background-color: #09baa7;
  }

  .manuallyAdjustment .pNum {
    color: #999999;
  }

  .manuallyAdjustment .exam_subTitle {
    display: inline-block;
    width: 6.25rem;
    height: 2rem;
    line-height: 2rem;
    padding: 0;
    border-radius: 0 15px 15px 0;
    -webkit-box-shadow: 0 5px 5px 0 #ddd;
    -moz-box-shadow: 0 5px 5px 0 #ddd;
    box-shadow: 0 5px 5px 0 #ddd;
    background-color: #89bcf5;
    border-color: #89bcf5;
    color: #fff;
    text-align: center;
  }

  .manuallyAdjustment .subject {
    position: relative;
    float: left;
    width: 6.25rem;
    padding: 1.25rem 1.25rem 2.5rem 1.25rem;
    border: 1px solid #d2d2d2;
    border-radius: 4px;
    margin-top: 2rem;
    text-align: center;
    margin-right: 1.25rem;
    cursor: pointer;
  }

  .manuallyAdjustment .subjects > div:last-child {
    margin-right: 0;
  }

  .manuallyAdjustment .subject .joinBtn {
    position: absolute;
    bottom: -1rem;
    left: 50%;
    margin-left: -3.025rem;
    width: 6.25rem;
    height: 2rem;
    line-height: 2rem;
    display: block;
    color: #fff;
    border-radius: 1.5rem;
    font-size: .875rem;
    background-color: #d2d2d2;
    cursor: auto;
  }

  .manuallyAdjustment .subject.disable {
    border: 1px solid #89bcf5;
    -webkit-box-shadow: 0 0 10px 1px #d2d2d2;
    -moz-box-shadow: 0 0 10px 1px #d2d2d2;
    box-shadow: 0 0 10px 1px #d2d2d2;
  }

  .manuallyAdjustment .subject .joinBtn.disable {
    background-color: #4da1ff;
    cursor: pointer;
  }

  .manuallyAdjustment .subject h5 {
    font-size: 1.5rem;
  }

  .manuallyAdjustment .subject p {
    margin: 1.5rem 0;
  }

  .manuallyAdjustment .listHeader {
    height: 3.375rem;
    background-color: #89bcf5;
    color: #fff;
    font-size: .875rem;
  }

  .manuallyAdjustment .studentsList .el-table th {
    background-color: #deeefe;
    height: 3rem;
  }

  .manuallyAdjustment .studentsList .el-table td {
    height: 3rem;
    font-size: .875rem;
  }

  .manuallyAdjustment .studentsList .el-table__footer-wrapper thead div, .manuallyAdjustment .studentsList .el-table__header-wrapper thead div {
    background-color: #deeefe;
  }
</style>
