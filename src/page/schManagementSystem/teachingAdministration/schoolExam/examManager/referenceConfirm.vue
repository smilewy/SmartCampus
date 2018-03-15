<template>
  <div class="referenceConfirm">
    <el-row type="flex" align="middle">
      <el-col :span="12">
        <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
          src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
          alt=""><span class="returnTxt">返回流程图</span></el-button>
        <span class="breadcrumb"><router-link
          :to="{name:'confirmStatus',params:{examinationid:classParam.examinationid}}"
          tag="span">各班确认情况</router-link><span
          class="breadcrumb_active">学生确认参考</span></span>
      </el-col>
      <el-col :span="12" class="testOperation_btn">
        <el-button type="primary" @click="saveMsg">保存</el-button>
      </el-col>
    </el-row>
    <el-row class="examManager_row">
      <span>班级：</span>
      <el-select multiple v-model="classIds" placeholder="请选择" class="testNumber">
        <el-option
          v-for="item in classList"
          :key="item.value"
          :label="item.classname"
          :value="item.classid">
        </el-option>
      </el-select>
      <el-button type="primary" icon="el-icon-search" class="selectData" @click="goSearch('class')">查询</el-button>
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
            placeholder="请输入关键字"
            suffix-icon="el-icon-search"
            v-model="selectParam.screen"
            @change="goSearch">
          </el-input>
        </div>
      </el-col>
    </el-row>
    <el-row class="referenceList"
            v-loading="loading"
            element-loading-text="拼命加载中">
      <el-row type="flex" align="middle" class="referenceList_head">
        <el-col :span="4" class="referenceList_filter">
          <span class="referenceList_icon"
                :class="{'referenceList_icon_up':sortList.className.num==1,'referenceList_icon_down':sortList.className.num==2}"
                @click="sort('className')">班级</span>
        </el-col>
        <el-col :span="4" class="referenceList_filter">
          <span class="referenceList_icon"
                :class="{'referenceList_icon_up':sortList.serialNumber.num==1,'referenceList_icon_down':sortList.serialNumber.num==2}"
                @click="sort('serialNumber')">班级序号</span>
        </el-col>
        <el-col :span="4" class="referenceList_filter">
          <span class="referenceList_icon"
                :class="{'referenceList_icon_up':sortList.sex.num==1,'referenceList_icon_down':sortList.sex.num==2}"
                @click="sort('sex')">性别</span>
        </el-col>
        <el-col :span="4" class="referenceList_filter">
          <span class="referenceList_icon"
                :class="{'referenceList_icon_up':sortList.name.num==1,'referenceList_icon_down':sortList.name.num==2}"
                @click="sort('name')">姓名</span>
        </el-col>
        <el-col :span="4" class="checkBtn referenceList_filter">
          <el-checkbox v-model="checkedAll.reported" @change="chooseAll('reported')">是否上报数据</el-checkbox>
        </el-col>
        <el-col :span="4" class="checkBtn referenceList_filter">
          <el-checkbox v-model="checkedAll.participate" @change="chooseAll('participate')">是否参加考试</el-checkbox>
        </el-col>
      </el-row>
      <el-row class="referenceList_body">
        <div v-for="(data,index) in tableData" class="clear_fix">
          <el-col :span="4" class="referenceList_body_col">{{data.className}}</el-col>
          <el-col :span="4" class="referenceList_body_col">{{data.serialNumber||'--'}}</el-col>
          <el-col :span="4" class="referenceList_body_col">{{data.sex}}</el-col>
          <el-col :span="4" class="referenceList_body_col">{{data.name}}</el-col>
          <el-col :span="4" class="referenceList_body_col">
            <el-checkbox v-model="data.reported" @change="chooseAlert('reported',index)"></el-checkbox>
          </el-col>
          <el-col :span="4" class="referenceList_body_col">
            <el-checkbox v-model="data.participate" @change="chooseAlert('participate',index)"></el-checkbox>
          </el-col>
        </div>
      </el-row>
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
  export default{
    data(){
      return {
        classList: [],
        tableData: [],
        checkedAll: {
          reported: false,
          participate: false
        },
        multipleSelection: {
          data: []
        },
        sortList: {   //排序按钮
          className: {
            num: 0
          },
          serialNumber: {
            num: 0
          },
          sex: {
            num: 0
          },
          name: {
            num: 0
          }
        },
        classParam: {
          examinationid: ''
        },
        classIds: [],
        selectParam: {
          page: 1,
          limit: 50,
          field: '',
          examinationid: '',
          screen: '',
          classid: '',
          order: ''
        },
        totalNum: 0,
        loading: false
      }
    },
    created: function () {
      var self = this;
      self.selectParam.examinationid = self.$route.params.examinationid;
      self.classParam.examinationid = self.$route.params.examinationid;
      //班级查询
      req.ajaxSend('/school/Examination/exmanagement/type/confirm/typename/exclass', 'post', self.classParam, function (res) {
        self.classList = res;
      });
    },
    methods: {
      returnFlowchart(){
        this.$router.push('/examManagerHome');
      },
      goSearch(type) {  //查询
        this.selectParam.field = '';
        this.selectParam.order = '';
        this.selectParam.page = 1;
        if (this.classIds.length == 0) {
          this.vmMsgWarning('请先选择班级！');
          return false;
        }
        if (type == 'class') {
          this.selectParam.classid = this.classIds.join(',');
          this.selectParam.screen = '';
        }
        this.loadData(this.selectParam);
      },
      chooseAll(val){
        if (this.checkedAll[val]) {
          for (let obj of this.tableData) {
            obj[val] = true;
          }
        } else {
          for (let obj of this.tableData) {
            obj[val] = false;
          }
        }
      },
      chooseAlert(val, e) {
        let ary = [];
        for (let obj of this.tableData) {
          if (obj[val]) {
            ary.push(obj);
          }
        }
        this.checkedAll[val] = (ary.length == this.tableData.length);
      },
      sort(val){
        if (this.classIds.length == 0) {
          this.vmMsgWarning('请先选择班级！');
          return false;
        }
        this.sortList[val].num++;
        if (this.sortList[val].num == 1) {
          this.selectParam.field = val;
          this.selectParam.order = 'ascending';
        } else if (this.sortList[val].num == 2) {
          this.selectParam.field = val;
          this.selectParam.order = 'descending';
        } else {
          this.sortList[val].num = 0;
          this.selectParam.field = '';
          this.selectParam.order = '';
        }
        this.loadData(this.selectParam);
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData(this.selectParam);
      },
      operationData(type){
        let sAy = [], hdData = {
          className: '班级',
          serialNumber: '班级序号',
          sex: '性别',
          name: '姓名'
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
          req.copyTableData('.referenceConfirm', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      saveMsg(){
        var self = this;
        if (self.tableData.length == 0) {
          return false;
        }
        for (let obj of self.tableData) {
          let data = {
            id: obj.id,
            participate: obj.participate ? '是' : '否',
            reported: obj.reported ? '是' : '否'
          };
          self.multipleSelection.data.push(data);
        }
        req.ajaxSend('/school/Examination/exmanagement/type/confirm/typename/attendupdate', 'post', self.multipleSelection, function (res) {
          if (res.return) {
            self.vmMsgSuccess('设置成功！');
            self.loadData(self.selectParam);
          } else {
            self.vmMsgError('设置失败！');
          }
        })
      },
      loadData(param){
        var self = this;
        self.loading = true;
        req.ajaxSend('/school/Examination/exmanagement/type/confirm/typename/attend', 'post', param, function (res) {
          self.loading = false;
          if (res.data) {
            let mData = {
              participate: [],
              reported: []
            };
            for (let obj of res.data) {
              obj.participate = (obj.participate == '是' ? true : false);
              obj.reported = (obj.reported == '是' ? true : false);
              if (obj.reported) {
                mData.reported.push(obj);
              }
              if (obj.participate) {
                mData.participate.push(obj);
              }
            }
            self.tableData = res.data;
            self.totalNum = Number.parseInt(res.page.count);
            for (let idj in self.checkedAll) {
              self.checkedAll[idj] = mData[idj].length == res.data.length;
            }
          } else {
            self.vmMsgWarning('未查到相关信息，请重新输入！');
          }
        })
      }
    }
  }
</script>
<style>
  .referenceConfirm .selectData {
    border-radius: 20px;
    margin-left: 2rem;
    padding: 10px 20px;
  }

  .referenceList {
    min-height: 35rem;
  }

  .referenceList .referenceList_head {
    height: 3.5rem;
    background-color: #89bcf5;
    color: #fff;
    font-weight: bold;
    border: 1px solid #d2d2d2;
  }

  .referenceList_filter, .referenceList_body_col {
    text-align: center;
  }

  .referenceList_filter > .referenceList_icon {
    position: relative;
    display: inline-block;
    cursor: pointer;
  }

  .referenceList_filter > .referenceList_icon:before {
    content: '';
    width: 15px;
    height: 15px;
    display: block;
    position: absolute;
    right: -2rem;
    top: 50%;
    -webkit-transform: translateY(-50%);
    -moz-transform: translateY(-50%);
    -ms-transform: translateY(-50%);
    -o-transform: translateY(-50%);
    transform: translateY(-50%);
    background: url(../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_num.png) no-repeat;
  }

  .referenceList_filter > .referenceList_icon_up:before {
    background: url(../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/filter_up.png) no-repeat;
  }

  .referenceList_filter > .referenceList_icon_down:before {
    background: url(../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/filter_down.png) no-repeat;
  }

  .referenceList .referenceList_body {
    font-size: .875rem;
    border-left: 1px solid #d2d2d2;
    border-right: 1px solid #d2d2d2;
  }

  .referenceList_body > div {
    height: 3rem;
    line-height: 3rem;
    border-bottom: 1px solid #d2d2d2;
  }

  .checkBtn .el-checkbox__label {
    font-size: 1rem;
    color: #fff;
    padding-left: 10px;
  }
</style>
