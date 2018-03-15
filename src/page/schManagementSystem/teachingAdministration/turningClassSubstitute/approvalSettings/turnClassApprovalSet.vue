<template>
  <div class="turnClassApprovalSet">
    <h3>审批设置</h3>
    <el-row :gutter="20" class="turnClassApprovalSet_row">
      <el-col :span="17">
        <el-row class="treeList approvalPerson">
          <el-row>
            <h4>审批人</h4>
          </el-row>
          <el-row class="tagList">
            <el-tag
              :key="tag.id"
              v-for="(tag,ix) in teacherLists"
              :closable="true"
              :close-transition="false"
              @close="handleClose(ix)"
            >
              {{tag.name}}
            </el-tag>
          </el-row>
          <el-row class="operationBtn">
            <el-button type="primary" class="clearBtn" @click="save('clear')">清空</el-button>
            <el-button type="primary" @click="save">保存</el-button>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="7">
        <el-row class="treeList">
          <el-row class="treeList_title">
            <el-row>
              <h5>待选教师</h5>
            </el-row>
            <el-row class="treeInput">
              <el-input
                placeholder="输入查询姓名"
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
              :data="treeData"
              node-key="id"
              ref="tree"
              :filter-node-method="filterNode"
              @node-click="chooseTeacher"
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
        teacherLists: [],
        treeData: [],
        defaultProps: {
          children: 'data',
          label: 'name'
        },
        filterText: '',
        loading:false
      }
    },
    watch: {
      filterText(val) {
        this.$refs.tree.filter(val);
      }
    },
    created: function () {
      var self = this;
      //得到待选教师列表
      self.loading=true;
      req.ajaxSend('/school/Classreplacement/approverSet?type=getApproverUser', 'get', '', function (res) {
        self.treeData = res.data || [];
        self.loading=false;
      });
      //得到审批人
      req.ajaxSend('/school/Classreplacement/approverSet?type=getApproverUserList', 'get', '', function (res) {
        self.teacherLists = res.data || [];
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
      handleClose(ix) {
        this.teacherLists.splice(ix, 1);
      },
      chooseTeacher(data, node, event){
        if (!data.data) {
          for (let obj of this.teacherLists) {
            if (obj.id == data.id) {
              this.vmMsgWarning('该教师已在审批列表中!');
              return false;
            }
          }
          this.teacherLists.push(data);
        }
      },
      save(type){
        var self = this, data;
        if (type == 'clear') {  //清空
          self.$confirm('确定清空审批人列表?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            self.teacherLists = [];
          }).catch(() => {
          });
        } else {   //保存
          if (self.teacherLists.length == 0) {
            self.vmMsgWarning('请先选择审批人!');
            return false;
          }
          data = {
            user: self.teacherLists
          };
          req.ajaxSend('/school/Classreplacement/approverSet?type=createApproverUser', 'post', data, function (res) {
            if (res.statu == 1) {
              self.vmMsgSuccess('保存成功!');
            } else {
              self.vmMsgError(res.message);
            }
          })
        }
      }
    }
  }
</script>
<style>
  .turnClassApprovalSet {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .turnClassApprovalSet h3 {
    font-size: 1.25rem;
  }

  .turnClassApprovalSet .turnClassApprovalSet_row {
    margin-top: 2rem;
  }

  .turnClassApprovalSet h4 {
    font-size: 1.25rem;
    font-weight: normal;
    padding: .875rem 1rem;
    border-bottom: 1px solid #d2d2d2;
  }

  .turnClassApprovalSet .tagList {
    padding: .75rem 1.25rem;
    height: 43rem;
  }

  .turnClassApprovalSet .tagList .el-tag {
    background-color: #f08bc5;
    margin-right: 1.25rem;
    margin-top: .5rem;
    color: #fff;
  }

  .turnClassApprovalSet .operationBtn {
    text-align: right;
    padding-right: 2rem;
  }

  .turnClassApprovalSet .operationBtn .el-button {
    padding: 10px 2.625rem;
    border-radius: 20px;
  }

  .turnClassApprovalSet .operationBtn .el-button.clearBtn {
    background: #ff8686;
    border-color: #ff8686;
  }

  .turnClassApprovalSet .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
  }

  .turnClassApprovalSet .treeList_body {
    padding: .875rem;
    height: 45rem;
    overflow: auto;
  }

  .turnClassApprovalSet .treeList_title {
    padding: .875rem;
  }

  .turnClassApprovalSet .treeList_title h5 {
    font-size: 1rem;
  }

  .turnClassApprovalSet .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .turnClassApprovalSet .treeList .el-tree {
    border: none;
  }

  .turnClassApprovalSet .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .turnClassApprovalSet .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }
</style>
