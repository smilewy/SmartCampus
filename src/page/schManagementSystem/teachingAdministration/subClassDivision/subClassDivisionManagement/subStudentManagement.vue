<template>
  <div class="subStudentManagement">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-col :span="14">
        <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
          src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
          alt=""><span class="returnTxt">返回流程图</span></el-button>
        <h3>分班学生管理</h3>
      </el-col>
      <el-col :span="10" class="operationBtn">
        <el-button @click="save('clear')">清空</el-button>
        <el-button type="primary" @click="save">保存</el-button>
      </el-col>
    </el-row>
    <el-row :gutter="20" class="subClassDivision_row">
      <el-col :span="7">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>选择学生：</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入关键字进行过滤"
                v-model="filterText">
                <template slot="prepend">
                  <i class="el-icon-search" @click="filterTree"></i>
                </template>
              </el-input>
            </el-row>
          </el-row>
          <el-row class="d_line"></el-row>
          <el-row class="treeList_body">
            <ul id="treeDemo" class="ztree"></ul>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="17">
        <el-row class="alertsList">
          <el-table
            :data="tableData"
            style="width: 100%"
            max-height="700"
            v-loading="loading"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              type="index"
              width="100"
              label="序号">
            </el-table-column>
            <el-table-column
              prop="grade"
              label="年级">
            </el-table-column>
            <el-table-column
              prop="className"
              label="班级">
            </el-table-column>
            <el-table-column
              prop="name"
              label="姓名">
            </el-table-column>
            <el-table-column
              prop="sex"
              label="性别">
            </el-table-column>
            <el-table-column
              label="操作">
              <template slot-scope="scope">
                <span class="edit" @click="deleteData(scope.$index)">删除</span>
              </template>
            </el-table-column>
          </el-table>
        </el-row>
        <el-row class="pageAlerts" v-if="tableAllData.length!=0">
          <el-pagination
            @current-change="handleCurrentChange"
            :current-page.sync="selectParam.page"
            :page-size="selectParam.count"
            layout="prev, pager, next, jumper"
            :total="totalNum">
          </el-pagination>
        </el-row>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  //树组件
  import '@/../static/zTree/css/zTreeStyle/zTreeStyle.css'
  import '@/../static/zTree/js/jquery.ztree.core.min.js'
  import '@/../static/zTree/js/jquery.ztree.excheck.min.js'
  import '@/../static/zTree/js/jquery.ztree.exedit.min.js'

  export default {
    data() {
      return {
        tableData: [],
        tableAllData: [],
        hiddenNodes: [],
        zTreeObj: {},
        defaultProps: {
          children: 'class',
          label: 'name'
        },
        filterText: '',
        selectParam: {
          planId: '',
          page: 1,
          count: 50
        },
        totalNum: 0,
        loading:false
      }
    },
    watch: {
      filterText(val) {
        this.filterTree('treeDemo', val);
      }
    },
    created: function () {
      var self = this, data = {
        func: 'getAllStudent',
        param: {
          planId: self.$route.params.planId
        }
      }, setting = {
        view: {
          selectedMulti: false,
          fontCss: self.getFontCss
        },
        check: {
          enable: true
        },
        data: {
          simpleData: {},
          key: {
            children: 'class'
          }
        },
        callback: {
          onCheck: function (treeId, treeNode) {
            const zTree = $.fn.zTree.getZTreeObj("treeDemo");
            const checkedPersonList = zTree.getCheckedNodes(true);
            self.tableAllData = self.aFun(checkedPersonList);
            self.loadData();
          },
        }
      };
      self.selectParam.planId = self.$route.params.planId;
      //查询待选学生名单
      req.ajaxSend('/school/DivideBranch/common', 'post', data, function (res) {
        self.zTreeObj = $.fn.zTree.init($("#treeDemo"), setting, res);
        const checkedPersonList = self.zTreeObj.getCheckedNodes(true);
        self.tableAllData = self.aFun(checkedPersonList);
        self.loadData();
      });
    },
    methods: {
      returnFlowchart() {
        this.$router.push('/subClassDivisionHome');
      },
      handleCurrentChange(val) {
        this.selectParam.page = val;
        this.loadData();
      },
      deleteData(idx) {
        let deNum = (this.selectParam.page - 1) * this.selectParam.count + idx;
        this.zTreeObj.checkNode(this.tableAllData[deNum], false, true);
        this.tableAllData.splice(deNum, 1);
        this.loadData();
      },
      save(type) {
        var self = this, url = '/school/DivideBranch/studentManage', data;
        if (type == 'clear') {  //清空
          self.tableAllData = [];
          self.zTreeObj.checkAllNodes(false);
          self.selectParam.page = 1;
          self.loadData();
        } else {   //保存
          let tep = self.tableAllData, tep1, tep2;
          if(tep.length!=0){
            tep1 = JSON.stringify(tep);
            tep2 = encodeURI(tep1);
          }else{
              tep2=tep;
          }
          data = {
            planId: self.selectParam.planId,
            type: 'save',
            students: tep2
          };
          req.ajaxSend(url, 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('保存成功！');
            } else {
              self.vmMsgError(res.msg);
            }
          })
        }
      },
      loadData() {
        this.totalNum = this.tableAllData.length;
        this.tableData = this.tableAllData.slice((this.selectParam.page - 1) * this.selectParam.count, (this.selectParam.page) * this.selectParam.count);
      },
      aFun(nodes) {
        const filterNodes = nodes.filter(function (val) {
          if (val.userId) {
            return val;
          }
        });
        return filterNodes;
      },
      getFontCss(treeId, treeNode) {
        return (!!treeNode.highlight) ? {color: "#A60000", "font-weight": "bold"} : {
          color: "#333",
          "font-weight": "normal"
        };
      },
      filterTree(treeId, val) {   //树组件模糊查询
        searchByFlag_ztree(treeId, val);

        /**
         * 搜索树，高亮显示并展示【模糊匹配搜索条件的节点s】
         * @param treeId
         * @param val     搜索框值
         */
        function searchByFlag_ztree(treeId, val) {
          //<1>.搜索条件
          var searchCondition = val;
          //<2>.得到模糊匹配搜索条件的节点数组集合
          var highlightNodes = new Array();
          if (searchCondition != "") {
            var treeObj = $.fn.zTree.getZTreeObj(treeId);
            highlightNodes = treeObj.getNodesByParamFuzzy("name", searchCondition, null);
          }
          //<3>.高亮显示并展示【指定节点s】
          highlightAndExpand_ztree(treeId, highlightNodes);
        }

        /**
         * 高亮显示并展示【指定节点s】
         * @param treeId
         * @param highlightNodes 需要高亮显示的节点数组
         */
        function highlightAndExpand_ztree(treeId, highlightNodes) {
          var treeObj = $.fn.zTree.getZTreeObj(treeId);
          //<1>. 先把全部节点更新为普通样式
          var treeNodes = treeObj.transformToArray(treeObj.getNodes());
          for (var i = 0; i < treeNodes.length; i++) {
            treeNodes[i].highlight = false;
            treeObj.updateNode(treeNodes[i]);
          }
          //<2>.收起树, 只展开根节点下的一级节点
          close_ztree(treeId);
          //<3>.把指定节点的样式更新为高亮显示，并展开
          if (highlightNodes != null) {
            for (var i = 0; i < highlightNodes.length; i++) {
              //高亮显示节点的父节点的父节点....直到根节点，并展示
              highlightNodes[i].highlight = true;
              treeObj.updateNode(highlightNodes[i]);
              var parentNode = highlightNodes[i].getParentNode();
              var parentNodes = getParentNodes_ztree(treeId, parentNode);
              treeObj.expandNode(parentNodes, true, false, true);
              treeObj.expandNode(parentNode, true, false, true);
            }
          }
        }

        /**
         * 递归得到指定节点的父节点的父节点....直到根节点
         */
        function getParentNodes_ztree(treeId, node) {
          if (node != null) {
            var treeObj = $.fn.zTree.getZTreeObj(treeId);
            var parentNode = node.getParentNode();
            return getParentNodes_ztree(treeId, parentNode);
          } else {
            return node;
          }
        }

        /**
         * 收起树：只展开根节点下的一级节点
         * @param treeId
         */
        function close_ztree(treeId) {
          var treeObj = $.fn.zTree.getZTreeObj(treeId);
          var nodes = treeObj.transformToArray(treeObj.getNodes());
          var nodeLength = nodes.length;
          for (var i = 0; i < nodeLength; i++) {
            if (nodes[i].level == 0) {
              //根节点：展开
              treeObj.expandNode(nodes[i], true, true, false);
            } else {
              //非根节点：收起
              treeObj.expandNode(nodes[i], false, true, false);
            }
          }
        }
      }
    }
  }
</script>
<style>
  .subStudentManagement .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
    -webkit-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    -moz-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    box-shadow: 0 0 1px 1px #d2d2d2 inset;
  }

  .subStudentManagement .treeList_body {
    padding: .875rem;
    max-height: 85%;
    overflow: auto;
  }

  .subStudentManagement .treeList_title {
    padding: .875rem .875rem 1.5rem;
  }

  .subStudentManagement .treeList_title h5 {
    font-size: 1rem;
  }

  .subStudentManagement .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .subStudentManagement .treeList .el-tree {
    border: none;
  }

  .subStudentManagement .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .subStudentManagement .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }
</style>
