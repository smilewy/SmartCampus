<template>
  <div class="distributionPersonnelList">
    <el-row type="flex" align="middle">
      <el-col :span="16">
        <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
          src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
          alt=""><span class="returnTxt">返回流程图</span></el-button>
        <h3>设置分配人员名单</h3>
      </el-col>
      <el-col :span="8" class="save">
        <el-button type="primary" @click="save">保存</el-button>
      </el-col>
    </el-row>
    <el-row :gutter="20" class="distributionPersonnel_head">
      <el-col :span="17">
        <el-row>共 <span class="listNumber">{{numberData.all}}</span> 人参与宿舍分配（男 <span class="listNumber">{{numberData.male}}</span>
          ，女 <span
            class="listNumber">{{numberData.female}}</span> ）
        </el-row>
        <el-row class="d_line distributionPersonnel_row"></el-row>
        <el-row type="flex" align="middle" class="alertsBtn">
          <el-button-group>
            <el-button class="filt" title="导出" @click="operationData('export')">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"
                   alt="">
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"
                   alt="">
            </el-button>
            <el-button class="delete" title="删除" @click="operationData('delete')">
              <img class="delete_unactive"
                   src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete.png"
                   alt="">
              <img class="delete_active"
                   src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete_highlight.png"
                   alt="">
            </el-button>
          </el-button-group>
          <el-button class="delete secBtn-group" title="打印" @click="operationData('print')">
            <img class="delete_unactive"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"
                 alt="">
            <img class="delete_active"
                 src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"
                 alt="">
          </el-button>
        </el-row>
        <el-row class="alertsList">
          <el-table
            :data="tableData"
            style="width: 100%"
            @selection-change="handleSelectionChange"
            @select="setActData"
            @select-all="setActDataAll"
            max-height="600"
            v-loading="loading"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              type="selection"
              width="55">
            </el-table-column>
            <el-table-column
              type="index"
              width="80"
              label="序号">
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
              prop="phone"
              min-width="150"
              label="手机号">
            </el-table-column>
          </el-table>
        </el-row>
      </el-col>
      <el-col :span="7">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>候选名单</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入查询班级或姓名"
                v-model="filterText">
                <template slot="prepend">
                  <i class="el-icon-search"></i>
                </template>
              </el-input>
            </el-row>
          </el-row>
          <el-row class="d_line"></el-row>
          <el-row class="treeList_body"
                  v-loading="loading1"
                  element-loading-text="拼命加载中">
            <el-tree
              :data="treeData"
              node-key="id"
              ref="tree"
              :filter-node-method="filterNode"
              @node-click="chooseStudent"
              :props="defaultProps">
            </el-tree>
          </el-row>
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
        multipleSelection: [],
        treeData: [],
        defaultProps: {
          children: 'child',
          label: 'name'
        },
        filterText: '',
        planId: '',
        numberData: {
          all: 0,
          male: 0,
          female: 0
        },
        loading:false,
        loading1:false
      }
    },
    watch: {
      filterText(val) {
        this.$refs.tree.filter(val);
      }
    },
    created: function () {
      var self = this, data = {
        func: 'getAssign',
        param: {
          planId: self.$route.params.planId
        }
      }, data1 = {
        planId: self.$route.params.planId
      };
      self.planId = self.$route.params.planId;
      self.loading1=true;
      req.ajaxSend('/school/StudentDorm/common', 'post', data, function (res) {
        self.treeData = res.data;
        self.loading1=false;
      });
      self.loading=true;
      req.ajaxSend('/school/StudentDorm/assignList', 'post', data1, function (res) {
        self.loading=false;
        self.tableData = res.data;
        for (let obj of self.tableData) {
          self.numberData.all++;
          if (obj.sex == '女') {
            self.numberData.female++;
          } else {
            self.numberData.male++;
          }
        }
      })
    },
    methods: {
      returnFlowchart(){
        this.$router.go(-1);
      },
      filterNode(value, data) {
        if (!value) return true;
        if (data.name) {
          data.name = data.name.toString();
          return data.name.indexOf(value) !== -1;
        }
      },
      setActData(sel, row){
        row.checked = !row.checked;
      },
      setActDataAll(sel){
        if (sel.length != 0) {
          for (let obj of this.tableData) {
            obj.checked = true;
          }
        } else {
          for (let obj of this.tableData) {
            obj.checked = false;
          }
        }
      },
      operationData(type){
        let sAy = [], hdData;
        hdData = {
          name: '姓名',
          sex: '性别',
          grade: '年级',
          class: '班级',
          phone: '手机号'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'delete') {
          var ary = [];
          if (this.multipleSelection.length == 0) {
            this.vmMsgWarning('请选择记录！');
            return false;
          }
          this.numberData.all = 0;
          this.numberData.male = 0;
          this.numberData.female = 0;
          if (this.multipleSelection.length == this.tableData.length) {
            this.tableData = [];
          } else {
            for (let obj of this.tableData) {
              if (!obj.checked) {
                ary.push(obj);
                this.numberData.all++;
                if (obj.sex == '女') {
                  this.numberData.female++;
                } else {
                  this.numberData.male++;
                }
              }
            }
            this.tableData = ary;
          }
        } else if(type=='export'){
          req.downloadFile('.distributionPersonnelList', '/school/StudentDorm/assignList?export=ensure&planId=' + this.planId, 'post');
        } else {
          req.lodop(sAy);
        }
      },
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      chooseStudent(node){
        if (!node.child) {
          node.checked = false;
          for (let obj of this.tableData) {
            if (obj.id == node.id) {
              this.vmMsgWarning('已添加过该成员！');
              return false;
            }
          }
          this.tableData.push(node);
          this.numberData.all++;
          if (node.sex == '女') {
            this.numberData.female++;
          } else {
            this.numberData.male++;
          }
        }
      },
      save(){
        var self = this, data = {
          planId: self.planId,
          type: 'operate',
          assignId: []
        };
        if (self.tableData.length == 0) {
          self.vmMsgWarning('请添加分配人员名单！');
          return false;
        }
        for (let obj of self.tableData) {
          data.assignId.push(obj.id);
        }
        req.ajaxSend('/school/StudentDorm/assignList', 'post', data, function (res) {
          if (res.status == 1) {
            self.vmMsgSuccess('保存成功！');
          } else {
            this.vmMsgError(res.msg);
          }
        })
      }
    }
  }
</script>
<style>
  .distributionPersonnelList .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
    -webkit-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    -moz-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    box-shadow: 0 0 1px 1px #d2d2d2 inset;
  }

  .distributionPersonnelList .distributionPersonnel_head {
    margin-top: 2rem;
  }

  .distributionPersonnelList .distributionPersonnel_row {
    margin: 1.25rem 0;
  }

  .distributionPersonnelList .save .el-button {
    padding: 10px 2.5rem;
    border-radius: 20px;
    float: right;
  }

  .distributionPersonnelList .listNumber {
    color: #4da1ff;
    font-size: .875rem;
  }

  .distributionPersonnelList .treeList_body {
    padding: .875rem;
    height: 44rem;
    overflow: auto;
  }

  .distributionPersonnelList .treeList_title {
    padding: .875rem .875rem 1.5rem;
  }

  .distributionPersonnelList .treeList_title h5 {
    font-size: 1rem;
  }

  .distributionPersonnelList .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .distributionPersonnelList .treeList .el-tree {
    border: none;
  }

  .distributionPersonnelList .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .distributionPersonnelList .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }
</style>
