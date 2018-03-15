<template>
  <div class="setLifeTeacher">
    <el-row type="flex" align="middle">
      <el-col :span="12">
        <h3>设置生活老师</h3>
      </el-col>
      <el-col :span="12">
        <el-button type="primary" class="saveBtn" @click="save">保存</el-button>
      </el-col>
    </el-row>
    <el-row :gutter="20" class="setLifeTeacher_row">
      <el-col :span="5">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>生活教师：</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入查询分类或姓名"
                v-model="filterText">
                <template slot="prepend">
                  <i class="el-icon-search"></i>
                </template>
              </el-input>
            </el-row>
          </el-row>
          <el-row class="d_line"></el-row>
          <el-row class="treeList_body"
                  v-loading="loading"
                  element-loading-text="拼命加载中">
            <el-tree
              :data="teacherList"
              node-key="id"
              ref="tree"
              :filter-node-method="filterNode"
              @node-click="chooseTeacher"
              :highlight-current="true"
              :props="defaultPropsTeacher">
            </el-tree>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="12">
        <el-row class="alertsBtn">
          <el-button-group>
            <el-button class="filt" title="添加" @click="addRow">
              <img class="filt_unactive"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add.png"
                   alt="">
              <img class="filt_active"
                   src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_add_highlight.png"
                   alt="">
            </el-button>
            <el-button class="delete" title="删除" @click="deleteData">
              <img class="delete_unactive"
                   src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete.png"
                   alt="">
              <img class="delete_active"
                   src="../../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_delete_highlight.png"
                   alt="">
            </el-button>
          </el-button-group>
          <span class="warmTips">操作提示：从左边选择生活老师，从右边选择对应宿舍。</span>
        </el-row>
        <el-row class="alertsList">
          <el-table
            :data="tableData"
            style="width: 100%"
            border
            @selection-change="handleSelectionChange"
            @select="setChecked"
            @select-all="setActAllData"
            v-loading="loading1"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              type="selection"
              width="55">
            </el-table-column>
            <el-table-column
              label="生活老师">
              <template slot-scope="scope">
                <span>
                   <el-tag
                     :closable="true"
                     :close-transition="false"
                     @close="handleClose('teacher',scope.$index)" v-if="scope.row.teaId"
                   >
                  {{scope.row.teaName}}
                </el-tag>
                </span>
                <span class="addPersonBtn" :class="{'active':scope.row.tChecked}"
                      @click="setActive(scope.$index,'tChecked')"><i class="el-icon-plus"></i></span>
              </template>
            </el-table-column>
            <el-table-column
              label="宿舍">
              <template slot-scope="scope">
                <span>
                   <el-tag
                     :key="ix"
                     v-for="(tag,ix) in scope.row.dorm"
                     :closable="true"
                     :close-transition="false"
                     @close="handleClose('dormitory',scope.$index,ix)"
                   >
                  {{tag.name}}
                </el-tag>
                </span>
                <span class="addPersonBtn" :class="{'active':scope.row.dChecked}"
                      @click="setActive(scope.$index,'dChecked')"><i class="el-icon-plus"></i></span>
              </template>
            </el-table-column>
          </el-table>
        </el-row>
        <el-row class="dormitoryDirector">
          <h5>宿舍主任：
            <span>
                   <el-tag
                     :closable="true"
                     :close-transition="false"
                     @close="handleClose('director')" v-if="dynamicTags.teaId"
                   >
                  {{dynamicTags.teaName}}
                </el-tag>
                </span>
            <span class="addPersonBtn" :class="{'active':idxData.director}" @click="setActive('director','tChecked')"><i
              class="el-icon-plus"></i></span>
          </h5>
        </el-row>
      </el-col>
      <el-col :span="7">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>选择宿舍：</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入查询分类或姓名"
                v-model="filterText2">
                <template slot="prepend">
                  <i class="el-icon-search"></i>
                </template>
              </el-input>
            </el-row>
          </el-row>
          <el-row class="d_line"></el-row>
          <el-row class="treeList_body">
            <el-tree
              :data="dormitoryList"
              node-key="id"
              ref="tree2"
              :filter-node-method="filterNode2"
              :highlight-current="true"
              @node-click="chooseDormitory"
              :render-content="renderContent"
              :props="defaultPropsDormitory">
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
        checkAll: false,
        dynamicTags: {
          dormDean: 1,
          dorm: [],
          teaId: '',
          teaName: ''
        },
        teacherList: [],
        dormitoryList: [],
        defaultPropsTeacher: {
          children: 'child',
          label: 'name'
        },
        defaultPropsDormitory: {
          children: 'child',
          label: 'name'
        },
        filterText: '',
        filterText2: '',
        multipleSelection: [],
        idxData: {   //用来记录应该加入哪个宿舍和生活老师
          tIdx: '',
          dIdx: '',
          director: false
        },
        loading: false,
        loading1: false
      }
    },
    watch: {
      filterText(val) {
        this.$refs.tree.filter(val);
      },
      filterText2(val) {
        this.$refs.tree2.filter(val);
      }
    },
    created: function () {
      var self = this, data = {
        func: 'getManager'
      }, data1 = {
        func: 'getDorm'
      };
      //获取生活老师
      self.loading = true;
      req.ajaxSend('/school/StudentDorm/common', 'post', data, function (res) {
        self.teacherList = res.data;
        self.loading = false;
      });
      //获取宿舍列表
      req.ajaxSend('/school/StudentDorm/common', 'post', data1, function (res) {
        self.dormitoryList = res.data;
      });
      //获取记录
      self.loading1 = true;
      req.ajaxSend('/school/StudentDorm/setManager', 'post', '', function (res) {
        self.loading1 = false;
        for (let [ix, obj] of res.data.ls.entries()) {
          obj.tChecked = false;
          obj.dChecked = false;
        }
        self.tableData = res.data.ls;
        self.dynamicTags.teaId = res.data.zr.teaId;
        self.dynamicTags.teaName = res.data.zr.teaName;
      })
    },
    methods: {
      filterNode(value, data) {
        if (!value) return true;
        if (data.name) {
          data.name = data.name.toString();
          return data.name.indexOf(value) !== -1;
        }
      },
      filterNode2(value, data) {
        if (!value) return true;
        if (data.name) {
          data.name = data.name.toString();
          return data.name.indexOf(value) !== -1;
        }
      },
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      setChecked(selection, row){
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
      chooseDormitory(data){  //选择宿舍
        var idx = this.idxData.dIdx, dMsg={};
        if (data.dormId) {
          if (!idx && typeof idx == 'string') {
            this.vmMsgWarning('请先点击宿舍加号键！');
            return false;
          }
          for (let obj of this.tableData[idx].dorm) {
            if (obj.dormId == data.dormId) {
              this.vmMsgWarning('已添加过该宿舍！');
              return false;
            }
          }
          $.extend(dMsg, data);
          dMsg.name = `${dMsg.number}-${dMsg.floor}-${dMsg.dormNumber}`;
          this.tableData[idx].dorm.push(dMsg);
        }
      },
      chooseTeacher(data){  //选择教师
        var idx = this.idxData.tIdx;
        if (!data.child) {
          if (!this.idxData.director) {   //选择教师
            if (!idx && typeof idx == 'string') {
              this.vmMsgWarning('请先点击生活老师加号键！');
              return false;
            }
            if (this.tableData[idx].teaId) {
              this.vmMsgWarning('每行只允许添加一个老师！');
              return false;
            }
            this.tableData[idx].teaName = data.name;
            this.tableData[idx].teaId = data.teaId;
          } else {   //选择宿舍主任
            if (this.dynamicTags.teaId) {
              this.vmMsgWarning('只允许添加一个宿舍主任！');
              return false;
            }
            this.dynamicTags.teaName = data.name;
            this.dynamicTags.teaId = data.teaId;
          }
        }
      },
      addRow(){ //添加行
        let l = this.tableData.length, data = {
          dorm: [],
          teaId: '',
          teaName: '',
          tChecked: false,
          dChecked: false
        };
        this.tableData.push(data);
      },
      deleteData(){
        var self = this, ary = [];
        if (self.multipleSelection.length == 0) {
          self.vmMsgWarning('请选择记录！');
          return false;
        }
        if (this.multipleSelection.length == this.tableData.length) {
          this.tableData = [];
        } else {
          for (let obj of this.tableData) {
            if (!obj.checked) {
              ary.push(obj);
            }
          }
          this.tableData = ary;
        }
      },
      handleClose(type, idx, ix) {
        if (type == 'teacher') {
          this.tableData[idx].teaId = '';
          this.tableData[idx].teaName = '';
        } else if (type == 'dormitory') {
          this.tableData[idx].dorm.splice(ix, 1);
        } else {
          this.dynamicTags.teaId = '';
          this.dynamicTags.teaName = '';
        }
      },
      setActive(idx, type){
        for (let obj of this.tableData) {
          obj[type] = false;
        }
        if (type == 'tChecked') {
          if (idx != 'director') {
            this.idxData.tIdx = idx;
            this.tableData[idx][type] = true;
            this.idxData.director = false;
          } else {
            this.idxData.tIdx = '';
            this.idxData.director = true;
          }
        } else {
          this.idxData.dIdx = idx;
          this.tableData[idx][type] = true;
        }
      },
      renderContent(h, {node, data, store}) {   //树空间自定义渲染
        var nSpan = data.dormId ?
      <
        span
        style = "color: #05adaa;" >（{
          data.dormNumber
        }）</
        span >
      :
        '';
        return ( < span >
          < span >
          < span > {node.label
      }</
        span >
        < / span >
        < span
        style = "font-size:12px" > {nSpan} < / span >
          < / span >
      )
        ;
      },
      save(){
        var self = this, data = {
          type: 'save',
          setting: []
        };
        for (let obj of self.tableData) {
          let param = {
            teaId: obj.teaId,
            teaName: obj.teaName,
            dormDean: 0,
            dormId: []
          };
          for (let mbj of obj.dorm) {
            param.dormId.push(mbj.dormId);
          }
          data.setting.push(param);
        }
        data.setting.push(self.dynamicTags);
        req.ajaxSend('/school/StudentDorm/setManager', 'post', data, function (res) {
          if (res.status == 1) {
            self.vmMsgSuccess('保存成功！');
          } else {
            self.vmMsgError(res.msg);
          }
        })
      }
    }
  }
</script>
<style>
  .setLifeTeacher {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .setLifeTeacher h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
    display: inline-block;
  }

  .setLifeTeacher .saveBtn {
    border-radius: 20px;
    padding: 10px 40px;
    float: right;
  }

  .setLifeTeacher .setLifeTeacher_row {
    margin-top: 2rem;
  }

  .setLifeTeacher .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
    -webkit-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    -moz-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    box-shadow: 0 0 1px 1px #d2d2d2 inset;
  }

  .setLifeTeacher .treeList_body {
    padding: .875rem;
    height: 45rem;
    overflow: auto;
  }

  .setLifeTeacher .treeList_title {
    padding: .875rem;
  }

  .setLifeTeacher h5 {
    font-size: 1rem;
  }

  .setLifeTeacher .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .setLifeTeacher .treeList .el-tree {
    border: none;
  }

  .setLifeTeacher .alertsBtn {
    margin: 0 0 1.25rem 0;
  }

  .setLifeTeacher .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .setLifeTeacher .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  .setLifeTeacher .treeList .el-tree {
    border: none;
  }

  .setLifeTeacher .warmTips {
    font-size: .875rem;
    color: #999999;
    margin-left: 1rem;
  }

  .setLifeTeacher .el-table th {
    text-align: center;
  }

  .setLifeTeacher .el-table tr > td:first-child {
    text-align: center;
  }

  .setLifeTeacher .alertsList {
    height: 44rem;
    border: 1px solid #d2d2d2;
    border-radius: 0 0 5px 5px;
    overflow: auto;
  }

  .setLifeTeacher .dormitoryDirector {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    padding: .9rem;
    margin-top: 1rem;
  }

  .setLifeTeacher .addPersonBtn {
    display: inline-block;
    border-radius: 6px;
    border: 1px solid #999999;
    font-size: .875rem;
    padding: .2rem;
    margin-left: 10px;
    cursor: pointer;
    color: #999999;
    line-height: normal;
  }

  .setLifeTeacher .addPersonBtn.active {
    border-color: #f08bc5;
    color: #f08bc5;
  }

  .setLifeTeacher .el-tag {
    background-color: #f08bc5;
    margin: 5px 0 5px 10px;
    color: #fff;
  }
</style>
