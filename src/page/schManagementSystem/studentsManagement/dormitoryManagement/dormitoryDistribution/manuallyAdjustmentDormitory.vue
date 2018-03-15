<template>
  <div class="manuallyAdjustmentDormitory">
    <el-row type="flex" align="middle">
      <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
        src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
        alt=""><span class="returnTxt">返回流程图</span></el-button>
      <h3>手动调整</h3>
    </el-row>
    <el-row type="flex" align="middle" class="manuallyAdjustmentDormitory_rows">
      <span>宿舍类型：</span>
      <el-select v-model="selectParam.dormType" placeholder="请选择" class="dormitory" @change="setDormNumber">
        <el-option
          v-for="item in typeList"
          :key="item.value"
          :label="item.name"
          :value="item.value">
        </el-option>
      </el-select>
      <span class="l_gap">宿舍楼栋号：</span>
      <el-select v-model="selectParam.number" placeholder="请选择" class="dormitory">
        <el-option
          v-for="(item,ix) in levelList"
          :key="ix"
          :label="item.name"
          :value="item.name">
        </el-option>
      </el-select>
      <el-button type="primary" icon="el-icon-search" class="l_gap" @click="goSearch">查询</el-button>
    </el-row>
    <el-row class="d_line manuallyAdjustmentDormitory_row"></el-row>
    <el-row :gutter="20" class="manuallyAdjustmentDormitory_row">
      <el-col :span="10">
        <el-row class="pNum">当前人数/容纳人数</el-row>
        <el-row>
          <el-row class="subjects">
            <div class="subject" :class="{'disable':dormitory.checked}" v-for="(dormitory,ix) in dormitoryList"
                 :key="ix" @click.prevent="showStudent(ix)">
              <h5>{{dormitory.stu.length}} / {{dormitory.capacity}}</h5>
              <p>{{dormitory.name}}（{{dormitory.floor}}）</p>
              <p>{{dormitory.dormNumber}}（{{dormitory.dormType}}）</p>
              <span class="joinBtn" :class="{'disable':dormitory.checked&&multipleSelection.length!=0}"
                    @click.stop="addDorm(ix)">加入宿舍</span>
            </div>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="14">
        <el-row type="flex" align="middle" class="listHeader">宿舍学生名单（九年级，九一班/男）</el-row>
        <el-row class="studentsList">
          <el-table
            :data="tableData"
            style="width: 100%"
            border
            max-height="600"
          >
            <el-table-column
              prop="grade"
              min-width="120"
              label="年级">
            </el-table-column>
            <el-table-column
              prop="class"
              min-width="120"
              label="班级">
            </el-table-column>
            <el-table-column
              prop="stuName"
              min-width="120"
              label="姓名">
            </el-table-column>
            <el-table-column
              prop="sex"
              min-width="120"
              label="性别">
            </el-table-column>
            <el-table-column
              min-width="150"
              label="是否指定到宿舍">
              <template slot-scope="scope">
                <span v-if="scope.row.isAssign=='0'">否</span>
                <span v-if="scope.row.isAssign=='1'">是</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="remark"
              min-width="120"
              label="备注">
            </el-table-column>
          </el-table>
        </el-row>
        <el-row type="flex" align="middle" class="listHeader">待调整学生名单</el-row>
        <el-row class="studentsList">
          <el-table
            :data="tableData2"
            style="width: 100%"
            border
            @selection-change="handleSelectionStudent"
            max-height="600"
            v-loading="loading"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              type="selection"
              width="55">
            </el-table-column>
            <el-table-column
              prop="grade"
              min-width="120"
              label="年级">
            </el-table-column>
            <el-table-column
              prop="class"
              min-width="120"
              label="班级">
            </el-table-column>
            <el-table-column
              prop="stuName"
              min-width="120"
              label="姓名">
            </el-table-column>
            <el-table-column
              prop="sex"
              min-width="120"
              label="性别">
            </el-table-column>
            <el-table-column
              min-width="150"
              label="是否指定到宿舍">
              <template slot-scope="scope">
                <span v-if="scope.row.isAssign=='0'">否</span>
                <span v-if="scope.row.isAssign=='1'">是</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="name"
              min-width="120"
              label="宿舍楼名称">
            </el-table-column>
            <el-table-column
              prop="number"
              min-width="120"
              label="栋号">
            </el-table-column>
            <el-table-column
              prop="dormNumber"
              min-width="120"
              label="宿舍号">
            </el-table-column>
            <el-table-column
              prop="remark"
              min-width="120"
              label="备注">
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
        tableData2: [],
        multipleSelection: [],
        dormitoryList: [],
        levelList: [],
        typeList: [],
        selectParam: {
          planId: '',
          dormType: '',
          number: ''
        },
        selectWriteStu: {
          func: 'getSelStu',
          param: {
            planId: '',
            dormId: ''
          }
        },
        loading:false
      }
    },
    created: function () {
      var self = this, data = {
        func: 'getSelDorm',
        param: {
          planId: self.$route.params.planId
        },
      };
      self.selectParam.planId = self.$route.params.planId;
      self.selectWriteStu.param.planId = self.selectParam.planId;
      req.ajaxSend('/school/StudentDorm/common', 'post', data, function (res) {
        self.typeList = res.data;
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.go(-1);
      },
      setDormNumber(){
        this.selectParam.number = '';
        for (let obj of this.typeList) {
          if (obj.value == this.selectParam.dormType) {
            this.levelList = obj.child;
          }
        }
      },
      goSearch() {  //查询
        var self = this;
        if (!self.selectParam.dormType) {
          self.vmMsgWarning('请选择宿舍类型！');
          return false;
        }
        if (!self.selectParam.number) {
          self.vmMsgWarning('请选择宿舍楼栋号！');
          return false;
        }
        req.ajaxSend('/school/StudentDorm/manualAdjust', 'post', self.selectParam, function (res) {
          for (let obj of res.data) {
            obj.checked = false;
          }
          self.dormitoryList = res.data;
        })
      },
      showStudent(ix){
        var self = this;
        self.selectWriteStu.param.dormId = self.dormitoryList[ix].dormId;
        for (let obj of this.dormitoryList) {
          obj.checked = false;
        }
        this.dormitoryList[ix].checked = true;
        this.tableData = this.dormitoryList[ix].stu;
        //查询待选学生
        self.loading=true;
        req.ajaxSend('/school/StudentDorm/common', 'post', self.selectWriteStu, function (res) {
          self.tableData2 = res.data;
          self.loading=false;
        })
      },
      addDorm(ix){
        var self = this, data = {
          planId: self.selectParam.planId,
          type: 'add',
          dormId: self.dormitoryList[ix].dormId,
          assignId: []
        };
        if (!self.dormitoryList[ix].checked || self.multipleSelection.length == 0) {
          return false;
        }
        if (self.multipleSelection.length + self.dormitoryList[ix].stu.length > Number.parseInt(self.dormitoryList[ix].capacity)) {
          self.vmMsgWarning('加入宿舍的总人数不能超过该宿舍的容纳人数！');
          return false;
        }
        for (let obj of self.multipleSelection) {
          data.assignId.push(obj.assignId);
        }
        req.ajaxSend('/school/StudentDorm/manualAdjust', 'post', data, function (res) {
          if (res.status == 1) {
            self.vmMsgSuccess('加入成功！');
            req.ajaxSend('/school/StudentDorm/common', 'post', self.selectWriteStu, function (res) {
              self.tableData2 = res.data;
            })
          } else {
            self.vmMsgError(res.msg);
          }
        })
      },
      handleSelectionStudent(val) {
        this.multipleSelection = val;
      }
    }
  }
</script>
<style>
  .manuallyAdjustmentDormitory {
    font-size: 14px;
  }

  .manuallyAdjustmentDormitory .manuallyAdjustmentDormitory_row {
    margin: 1.25rem 0;
  }

  .manuallyAdjustmentDormitory_rows {
    margin: 2rem 0;
  }

  .manuallyAdjustmentDormitory .pNum {
    color: #999999;
  }

  .manuallyAdjustmentDormitory .subject {
    position: relative;
    float: left;
    width: 6.25rem;
    padding: 1.25rem 1.25rem 2.5rem 1.25rem;
    border: 1px solid #d2d2d2;
    border-radius: 4px;
    margin-top: 2rem;
    text-align: center;
    margin-right: 1.25rem;
  }

  .manuallyAdjustmentDormitory .subjects > div:last-child {
    margin-right: 0;
  }

  .manuallyAdjustmentDormitory .subject .joinBtn {
    position: absolute;
    bottom: -1rem;
    left: 50%;
    margin-left: -3.025rem;
    width: 6.25rem;
    height: 2rem;
    line-height: 2rem;
    display: block;
    color: #fff;
    background-color: #d2d2d2;
    border-radius: 1.5rem;
    font-size: .875rem;
  }

  .manuallyAdjustmentDormitory .subject.disable {
    border: 1px solid #89bcf5;
    -webkit-box-shadow: 0 0 10px 1px #d2d2d2;
    -moz-box-shadow: 0 0 10px 1px #d2d2d2;
    box-shadow: 0 0 10px 1px #d2d2d2;
  }

  .manuallyAdjustmentDormitory .subject .joinBtn.disable {
    background-color: #4da1ff;
    cursor: pointer;
  }

  .manuallyAdjustmentDormitory .subject h5 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .manuallyAdjustmentDormitory .listHeader {
    height: 3.375rem;
    background-color: #89bcf5;
    color: #fff;
    font-size: .875rem;
    padding-left: 1rem;
  }

  .manuallyAdjustmentDormitory .studentsList {
    margin-bottom: 1rem;
  }

  .manuallyAdjustmentDormitory .studentsList .el-table th {
    background-color: #deeefe;
    height: 3rem;
  }

  .manuallyAdjustmentDormitory .studentsList .el-table td {
    height: 3rem;
    font-size: .875rem;
  }

  .manuallyAdjustmentDormitory .studentsList .el-table__footer-wrapper thead div, .manuallyAdjustmentDormitory .studentsList .el-table__header-wrapper thead div {
    background-color: #deeefe;
  }

  .manuallyAdjustmentDormitory .l_gap {
    margin-left: 2rem;
  }

  .manuallyAdjustmentDormitory .el-button.l_gap {
    border-radius: 20px;
    padding: 10px 1.5rem;
  }

  .manuallyAdjustmentDormitory .dormitory {
    width: 9.375rem;
  }
</style>
