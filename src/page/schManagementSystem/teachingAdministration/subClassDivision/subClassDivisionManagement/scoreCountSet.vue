<template>
  <div class="scoreCountSet">
    <el-row type="flex" align="middle" class="subClassDivision_title">
      <el-col :span="14">
        <el-button type="primary" class="return_btn" @click="returnFlowchart"><img
          src="../../../../../assets/img/schManagementSystem/teachingAdministration/schoolExam/icon_return.png"
          alt=""><span class="returnTxt">返回流程图</span></el-button>
        <h3>成绩统计设置</h3>
      </el-col>
      <el-col :span="10" class="operationBtn">
        <el-button type="primary" @click="save">保存</el-button>
      </el-col>
    </el-row>
    <el-row :gutter="20" class="subClassDivision_row">
      <el-col :span="7">
        <el-row class="treeList_class"
                v-loading="loading"
                element-loading-text="拼命加载中">
          <el-tree
            :data="branchList"
            node-key="id"
            :highlight-current="true"
            ref="tree"
            @node-click="chooseBranch"
            :props="defaultPropsBranch">
          </el-tree>
        </el-row>
        <el-row class="treeList_test"
                v-loading="loading1"
                element-loading-text="拼命加载中">
          <ul id="treeDemo" class="ztree"></ul>
        </el-row>
      </el-col>
      <el-col :span="17">
        <el-row type="flex" align="middle">
          <span>{{activeBranch.branch}} - {{activeBranch.major}}</span>
          <el-checkbox-group v-model="synchronization" class="synchronization">
            <el-checkbox label="同步到本科类全部专业"
                         :disabled="synchronization.length!=0&&synchronization[0]!='同步到本科类全部专业'"></el-checkbox>
            <el-checkbox label="同步到全部科类及其他专业"
                         :disabled="synchronization.length!=0&&synchronization[0]!='同步到全部科类及其他专业'"></el-checkbox>
          </el-checkbox-group>
        </el-row>
        <el-row class="subClassDivision_row dataLists">
          <el-table
            :data="tableData"
            max-height="700"
            style="width: 100%"
            v-loading="loading2"
            element-loading-text="拼命加载中"
          >
            <el-table-column
              width="300"
              label="考试">
              <template slot-scope="scope">
                <p>{{scope.row.name}}</p>
                <el-row>
                  <input type="number" class="percentInput" v-model.number="scope.row.examRatio">
                </el-row>
              </template>
            </el-table-column>
            <el-table-column
              label="统计科目">
              <template slot-scope="scope">
                <div v-for="data in scope.row.subs" :key="" class="subject">
                  <span>{{data.name}}</span>
                  <input type="number" class="percentInput" v-model.number="data.ratio">
                  <span>%</span>
                </div>
              </template>
            </el-table-column>
          </el-table>
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
        zTreeObj: {},
        tableData: [],
        defaultPropsBranch: {
          children: 'majors',
          label: 'name'
        },
        branchList: [],
        examList: [],
        synchronization: [],
        activeBranch: {
          branch: '',
          major: '',
          wishId: '',
          index: 0
        },
        pId: '',
        loading:false,
        loading1:false,
        loading2:false,
      }
    },
    created: function () {
      var self = this, data1, data2, setting;
      self.pId = self.$route.params.planId;
      setting = {
        view: {
          selectedMulti: false
        },
        check: {
          enable: true
        },
        async: {
          enable: true,
          type: 'post',
          url: req.getRootName() + '/school/DivideBranch/common',
          dataType: 'json',
          otherParam: {
            func: 'getSubject',
            param: {
              planId: self.pId
            }
          },
          dataFilter: function (treeId, parentNode, responseData) {
            var data = responseData.data;
            return data;
          }
        },
        data: {
          simpleData: {},
          key: {
            children: 'subs'
          }
        },
        callback: {
          onCheck: function (event, treeId, treeNode) {
            self.aFun(treeId, treeNode);
          },
        }
      };
      data1 = {
        func: 'getWish',
        param: {
          planId: self.pId
        }
      };
      data2 = {
        func: 'getSubject',
        param: {
          planId: self.pId,
          wishId: ''
        }
      };
      //请求科类专业
      self.loading=true;
      req.ajaxSend('/school/DivideBranch/common', 'post', data1, function (res) {
        self.branchList = res.data;
        self.loading=false;
      });
      //请求考试及科目
      self.loading1=true;
      req.ajaxSend('/school/DivideBranch/common', 'post', data2, function (res) {
        self.loading1=false;
        self.examList = res.data;
        self.zTreeObj = $.fn.zTree.init($("#treeDemo"), setting, res.data);
      })
    },
    methods: {
      returnFlowchart() {
        this.$router.push('/subClassDivisionHome');
      },
      aFun(treeId, treeNode) {
        var self = this;
        if (treeNode.subs) {  //考试处理
          var Status = 0;
          var index = '';
          //判断是否存在
          for (let [key, obj] of self.tableData.entries()) {
            if (obj.examId == treeNode.examId) {
              Status = 1;
              index = key;
            }
          }
          if (Status === 0 && treeNode.checked) {
            var data = {
              examId: treeNode.examId,
              name: treeNode.name,
              examRatio: 0,
              subs: []
            };
            for (let obj of treeNode.subs) {
              let a = {};
              $.extend(a, obj);
              a.ratio = 0;
              data.subs.push(a);
            }
            self.tableData.push(data);
          }
          if (Status == 1 && !treeNode.checked) {
            self.tableData.splice(index, 1);
          }
        }
        /*科目的处理*/
        if (!treeNode.subs) {
          var ParentStatus = 0;
          var ChildStatus = 0;
          var Childindex = [];
          var ParentExamId = treeNode.parentId;
          //判断是否存在父级,没有则添加
          for (let [key, obj] of self.tableData.entries()) {
            if (obj.examId == ParentExamId) {
              ParentStatus = 1;
              index = key;
            }
          }
          if (ParentStatus === 0 && treeNode.checked) {
            var data2 = {
              examId: ParentExamId,
              name: treeNode.getParentNode().name,
              examRatio: 0,
              subs: []
            };
            self.tableData.push(data2);
          }
          //判断是否存在
          for (let [key, obj] of self.tableData.entries()) {
            if (obj.examId == ParentExamId) {
              Childindex.push(key);
              for (let [ix, mbj] of obj.subs.entries()) {
                if (mbj.subId == treeNode.subId) {
                  ChildStatus = 1;
                  Childindex.push(ix);
                }
              }
            }
          }
          if (ChildStatus === 0 && treeNode.checked) {
            var item = {
              parentId: ParentExamId,
              subId: treeNode.subId,
              name: treeNode.name,
              ratio: 0
            };
            self.tableData[Childindex[0]].subs.push(item);
          }
          if (ChildStatus == 1 && !treeNode.checked) {
            self.tableData[Childindex[0]].subs.splice(Childindex[1], 1);
            if (self.tableData[Childindex[0]].subs.length == 0) {
              self.tableData.splice(Childindex[0], 1);
            }
          }
        }
      },
      chooseBranch(node, state, child) {
        var self = this, data;
        if (!node.majors) {
          self.activeBranch.major = node.name;
          self.activeBranch.wishId = node.wishId;
          data = {
            planId: self.pId,
            wishId: node.wishId
          };
          self.zTreeObj.setting.async.otherParam.param.wishId = node.wishId;
          self.zTreeObj.reAsyncChildNodes(null, "refresh");
          self.loading2=true;
          req.ajaxSend('/school/DivideBranch/scoreSetting', 'post', data, function (res) {
            self.tableData = res.data;
            self.loading2=false;
          });
          for (let [ix, obj] of self.branchList.entries()) {
            if (node.parentId == obj.branchId) {
              self.activeBranch.branch = obj.name;
              self.activeBranch.index = ix;
              break;
            }
          }
        }
      },
      save() {
        var self = this, data = {
          planId: self.pId,
          type: 'save',
          wishIds: [],
          ratio: {}
        };
        if (!self.activeBranch.branch) {
          self.vmMsgWarning('请选择科目和专业！');
          return false;
        }
        for (let obj of self.tableData) {
          let score = 0;
          for (let mbj of obj.subs) {
            score += Number.parseInt(mbj.ratio) || 0;
          }
          if (score != 100) {
            self.vmMsgWarning('每场考试科目占比和必须等于100%！');
            return false;
          }
        }
        if (self.synchronization[0] == '同步到本科类全部专业') {
          for (let obj of self.branchList[self.activeBranch.index].majors) {
            data.wishIds.push(obj.wishId);
          }
        } else if (self.synchronization[0] == '同步到全部科类及其他专业') {
          for (let obj of self.branchList) {
            for (let mbj of obj.majors) {
              data.wishIds.push(mbj.wishId);
            }
          }
        } else {
          data.wishIds.push(self.activeBranch.wishId);
        }
        for (let obj of self.tableData) {
          for (let mbj of obj.subs) {
            data.ratio[mbj.subId] = {
              ratio: mbj.ratio,
              examRatio: obj.examRatio
            };
          }
        }
        req.ajaxSend('/school/DivideBranch/scoreSetting', 'post', data, function (res) {
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
  .scoreCountSet .treeList_class, .scoreCountSet .treeList_test {
    padding: .875rem;
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    -webkit-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    -moz-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    box-shadow: 0 0 1px 1px #d2d2d2 inset;
  }

  .scoreCountSet .treeList_class {
    height: 18.75rem;
  }

  .scoreCountSet .treeList_class .el-tree {
    height: 17rem;
    overflow: auto;
  }

  .scoreCountSet .treeList_test {
    height: 32.5rem;
    margin-top: 1.25rem;
  }

  .scoreCountSet .treeList_test .el-tree {
    height: 30.75rem;
    overflow: auto;
  }

  .scoreCountSet .treeList_class .el-tree, .scoreCountSet .treeList_test .el-tree {
    border: none;
  }

  .scoreCountSet .el-table th {
    background-color: #89bcf5;
    height: 3.5rem;
  }

  .scoreCountSet .el-table td {
    font-size: .875rem;
  }

  .scoreCountSet .el-table__footer-wrapper thead div, .scoreCountSet .el-table__header-wrapper thead div {
    background-color: #89bcf5;
    color: #fff;
  }

  .scoreCountSet .synchronization {
    margin-left: 2rem;
  }

  .scoreCountSet .dataLists {
    min-height: 35rem;
  }

  .scoreCountSet input.percentInput {
    width: 6.25rem;
    height: 1.5rem;
    text-align: center;
  }

  .scoreCountSet .subject {
    float: left;
    background-color: #f08bc5;
    color: #fff;
    padding: 8px;
    border-radius: 5px;
    margin: .5rem .5rem 0 0;
  }

  .scoreCountSet .subject input.percentInput {
    width: 3.2rem;
    height: 1.5rem;
    text-align: center;
  }

  .scoreCountSet .el-table .cell, .scoreCountSet .el-table th > div {
    padding: .5rem .5rem 1rem .5rem;
  }
</style>
