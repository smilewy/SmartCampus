<template>
  <div class="distributionDormitoryMsg">
    <el-row type="flex" align="middle">
      <el-col :span="16">
        <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
          src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
          alt=""><span class="returnTxt">返回流程图</span></el-button>
        <h3>设置分配宿舍信息</h3>
      </el-col>
      <el-col :span="8" class="save">
        <el-button type="primary" @click="save">保存</el-button>
      </el-col>
    </el-row>
    <el-row :gutter="20" class="distributionPersonnel_head">
      <el-col :span="17">
        <el-row>
          <span>共 <span class="listNumber">{{numberData.allDorm}}</span> 个宿舍（<span class="listNumber">{{numberData.maleDorm}}</span>个男生宿舍 ，<span
            class="listNumber">{{numberData.femaleDorm}}</span> 个女生宿舍，<span
            class="listNumber">{{numberData.mixDorm}}</span> 个混合宿舍，<span
            class="listNumber">{{numberData.otherDorm}}</span> 个其他）。</span>
          <span class="d_gap">共可容纳 <span class="listNumber">{{numberData.allNumber}}</span> 人（男 <span
            class="listNumber">{{numberData.male}}</span> ，女 <span
            class="listNumber">{{numberData.female}}</span>，混合 <span
            class="listNumber">{{numberData.mix}}</span>，</span>其他 <span
          class="listNumber">{{numberData.other}}</span>）。
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
            @select-all="setActAllData"
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
              prop="buildName"
              min-width="150"
              label="宿舍楼名称">
            </el-table-column>
            <el-table-column
              prop="number"
              min-width="120"
              label="栋号">
            </el-table-column>
            <el-table-column
              prop="floor"
              min-width="120"
              label="楼层">
            </el-table-column>
            <el-table-column
              prop="dormNumber"
              min-width="130"
              label="宿舍号">
            </el-table-column>
            <el-table-column
              prop="name"
              min-width="150"
              label="宿舍名称">
            </el-table-column>
            <el-table-column
              min-width="150"
              label="宿舍类型">
              <template slot-scope="scope">
                <span v-if="scope.row.dormType=='1'">女生宿舍</span>
                <span v-if="scope.row.dormType=='2'">男生宿舍</span>
                <span v-if="scope.row.dormType=='3'">混合宿舍</span>
                <span v-if="scope.row.dormType=='4'">其他</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="capacity"
              min-width="150"
              label="容纳人数">
            </el-table-column>
          </el-table>
        </el-row>
      </el-col>
      <el-col :span="7">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>待选宿舍</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入宿舍名称"
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
          allDorm: 0,
          maleDorm: 0,
          femaleDorm: 0,
          mixDorm: 0,
          otherDorm: 0,
          allNumber: 0,
          male: 0,
          female: 0,
          mix: 0,
          other: 0
        },
        loading: false,
        loading1: false
      }
    },
    watch: {
      filterText(val) {
        this.$refs.tree.filter(val);
      }
    },
    created: function () {
      var self = this, data = {
        func: 'getDorm'
      }, data1 = {
        planId: self.$route.params.planId
      };
      self.planId = self.$route.params.planId;
      self.loading1 = true;
      req.ajaxSend('/school/StudentDorm/common', 'post', data, function (res) {
        self.treeData = res.data;
        self.loading1 = false;
      });
      self.loading = true;
      req.ajaxSend('/school/StudentDorm/assignDorm', 'post', data1, function (res) {
        self.loading = false;
        self.tableData = res.data;
        for (let obj of self.tableData) {
          self.numberData.allDorm++;
          self.numberData.allNumber += Number.parseInt(obj.capacity);
          if (obj.dormType == '1') {
            self.numberData.femaleDorm++;
            self.numberData.female += Number.parseInt(obj.capacity);
          } else if (obj.dormType == '2') {
            self.numberData.maleDorm++;
            self.numberData.male += Number.parseInt(obj.capacity);
          } else if (obj.dormType == '3') {
            self.numberData.mixDorm++;
            self.numberData.mix += Number.parseInt(obj.capacity);
          } else {
            self.numberData.otherDorm++;
            self.numberData.other += Number.parseInt(obj.capacity);
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
      operationData(type){
        let sAy = [], hdData;
        hdData = {
          buildName: '宿舍楼名称',
          number: '栋号',
          floor: '楼层',
          dormNumber: '宿舍号',
          name: '宿舍名称',
          dormType: '宿舍类型',
          capacity: '容纳人数'
        };
        sAy.push(hdData);
        for (let obj of this.tableData) {
          let d = {};
          for (let name in hdData) {
            if (name == 'dormType') {
              switch (obj[name]) {
                case '1':
                  d[name] = '女生宿舍';
                  break;
                case '2':
                  d[name] = '男生宿舍';
                  break;
                case '3':
                  d[name] = '混合宿舍';
                  break;
                case '4':
                  d[name] = '其他';
                  break;
              }
            } else {
              d[name] = obj[name] || '';
            }
          }
          sAy.push(d)
        }
        if (type == 'delete') {
          var ary = [];
          if (this.multipleSelection.length == 0) {
            this.vmMsgWarning('请选择记录！');
            return false;
          }
          for (let name in this.numberData) {
            this.numberData[name] = 0;
          }
          if (this.multipleSelection.length == this.tableData.length) {
            this.tableData = [];
          } else {
            for (let obj of this.tableData) {
              if (!obj.checked) {
                ary.push(obj);
                this.numberData.allDorm++;
                this.numberData.allNumber += Number.parseInt(obj.capacity);
                if (obj.dormType == '1') {
                  this.numberData.femaleDorm++;
                  this.numberData.female += Number.parseInt(obj.capacity);
                } else if (obj.dormType == '2') {
                  this.numberData.maleDorm++;
                  this.numberData.male += Number.parseInt(obj.capacity);
                } else if (obj.dormType == '3') {
                  this.numberData.mixDorm++;
                  this.numberData.mix += Number.parseInt(obj.capacity);
                } else {
                  this.numberData.otherDorm++;
                  this.numberData.other += Number.parseInt(obj.capacity);
                }
              }
            }
            this.tableData = ary;
          }
        } else if (type == 'export') {
          req.downloadFile('.distributionDormitoryMsg', '/school/StudentDorm/assignDorm?export=ensure&planId=' + this.planId, 'post');
        } else {
          req.lodop(sAy);
        }
      },
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      setActData(sel, row){
        row.checked = !row.checked;
      },
      setActAllData(sel){
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
      chooseStudent(node){
        if (!node.child) {
          node.checked = false;
          for (let obj of this.tableData) {
            if (obj.dormId == node.dormId) {
              this.vmMsgWarning('已添加过该宿舍！');
              return false;
            }
          }
          this.tableData.push(node);
          this.numberData.allDorm++;
          this.numberData.allNumber += Number.parseInt(node.capacity);
          if (node.dormType == '1') {
            this.numberData.femaleDorm++;
            this.numberData.female += Number.parseInt(node.capacity);
          } else if (node.dormType == '2') {
            this.numberData.maleDorm++;
            this.numberData.male += Number.parseInt(node.capacity);
          } else if (node.dormType == '3') {
            this.numberData.mixDorm++;
            this.numberData.mix += Number.parseInt(node.capacity);
          } else {
            this.numberData.otherDorm++;
            this.numberData.other += Number.parseInt(node.capacity);
          }
        }
      },
      save(){
        var self = this, data = {
          planId: self.planId,
          type: 'operate',
          dormId: []
        };
        if (self.tableData.length == 0) {
          self.vmMsgWarning('请添加分配宿舍！');
          return false;
        }
        for (let obj of self.tableData) {
          data.dormId.push(obj.dormId);
        }
        req.ajaxSend('/school/StudentDorm/assignDorm', 'post', data, function (res) {
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
  .distributionDormitoryMsg .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
    -webkit-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    -moz-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    box-shadow: 0 0 1px 1px #d2d2d2 inset;
  }

  .distributionDormitoryMsg .distributionPersonnel_head {
    margin-top: 2rem;
  }

  .distributionDormitoryMsg .d_gap {
    margin-left: 2rem;
  }

  .distributionDormitoryMsg .distributionPersonnel_row {
    margin: 1.25rem 0;
  }

  .distributionDormitoryMsg .save .el-button {
    padding: 10px 2.5rem;
    border-radius: 20px;
    float: right;
  }

  .distributionDormitoryMsg .listNumber {
    color: #4da1ff;
    font-size: .875rem;
  }

  .distributionDormitoryMsg .treeList_body {
    padding: .875rem;
    height: 44rem;
    overflow: auto;
  }

  .distributionDormitoryMsg .treeList_title {
    padding: .875rem .875rem 1.5rem;
  }

  .distributionDormitoryMsg .treeList_title h5 {
    font-size: 1rem;
  }

  .distributionDormitoryMsg .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .distributionDormitoryMsg .treeList .el-tree {
    border: none;
  }

  .distributionDormitoryMsg .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .distributionDormitoryMsg .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }
</style>
