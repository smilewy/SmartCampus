<template>
  <div class="inSchoolProve">
    <h3>在读证明</h3>
    <el-row :gutter="20" class="inSchoolProve_row">
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
              @node-click="chooseStudent"
              :props="defaultProps">
            </el-tree>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="17">
        <el-row type="flex" align="middle" class="prove_header">
          <el-col :span="12">在读证明</el-col>
          <el-col :span="12" class="preview">
            <el-button type="primary" @click="operationData('save')">保存</el-button>
            <el-button type="primary" @click="operationData('prev')">预览</el-button>
          </el-col>
        </el-row>
        <el-row class="d_line"></el-row>
        <el-row type="flex" justify="center" class="prove_body">
          <el-col :span="16">
            <h6>在读证明</h6>
            <el-row class="prove_text">
              <el-row>
                <el-row>
                  <input type="text" placeholder="姓名" class="centry" v-model="znMsg.name"/>，<input type="text"
                                                                                                   placeholder="女"
                                                                                                   class="centry"
                                                                                                   v-model="znMsg.sex"/>，出生于<input
                  type="text"
                  placeholder="yyyy-mm-dd"
                  class="centry" v-model="znMsg.birthday"/>，
                  是我校<input type="text" placeholder="年级" class="centry" v-model="znMsg.gradeName"/> <input
                  type="text" placeholder="班级"
                  class="centry" v-model="znMsg.className"/>的学生。
                </el-row>
                <el-row>
                  特此证明。
                </el-row>
              </el-row>
              <el-row class="signed">
                <el-row>
                  <input type="text" placeholder="学校名称" class="right" v-model="znMsg.schoolName"/>
                </el-row>
                <el-row>
                  <input type="text" placeholder="2017年9月11日" class="right" v-model="znMsg.date"/>
                </el-row>
              </el-row>
            </el-row>
            <h6>Current Study Certificate</h6>
            <el-row class="prove_text">
              <el-row>
                This is to certify that <input type="text" placeholder="name" class="centry"
                                               v-model="enMsg.name"/>，<input type="text"
                                                                             placeholder="Female"
                                                                             class="centry" v-model="enMsg.sex"/>，born
                on <input type="text" placeholder="yyyy-mm-dd" class="centry" v-model="enMsg.birthday"/>，
                is a student in Class<input type="text" placeholder="class" class="centry" v-model="enMsg.className"/>，Grade
                <input type="text"
                       placeholder="grade"
                       class="centry" v-model="enMsg.gradeName"/>
                in our school .
              </el-row>
              <el-row class="signed">
                <el-row>
                  <input type="text" placeholder="School name" class="right" v-model="enMsg.schoolName"/>
                </el-row>
                <el-row>
                  <input type="text" placeholder="9/11/2017" class="right" v-model="enMsg.date"/>
                </el-row>
              </el-row>
            </el-row>
          </el-col>
        </el-row>
      </el-col>
    </el-row>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        treeData: [],
        defaultProps: {
          children: 'data',
          label: 'name'
        },
        filterText: '',
        enMsg: {
          birthday: "", //出身日期
          className: "", //班级
          name: "", //姓名
          date: "", //时间
          gradeName: "", //年级名
          schoolName: "" //学校
        },
        znMsg: {
          birthday: "", //出身日期
          className: "", //班级
          name: "", //姓名
          date: "", //时间
          gradeName: "", //年级名
          schoolName: "" //学校
        },
        userId: '',
        loading: false
      }
    },
    watch: {
      filterText(val) {
        this.$refs.tree.filter(val);
      }
    },
    created: function () {
      var self = this;
      self.loading = true;
      req.ajaxSend('/school/Educational/achievementPro?type=getGradeClassStudent', 'get', '', function (res) {
        self.treeData = res.data;
        self.loading = false;
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
      chooseStudent(node, state, child) {
        var self = this, data = {
          userId: ''
        };
        if (!node.data) {
          data.userId = node.userId;
          self.userId = node.userId;
          req.ajaxSend('/school/Educational/zdPro?type=getUser', 'get', data, function (res) {
            self.enMsg = res.data.en;
            self.znMsg = res.data.zn;
          })
        }
      },
      operationData(type) {
        var self = this, data = {
          userId: self.userId,
          data: {
            en: self.enMsg,
            zn: self.znMsg
          }
        };
        if (!self.userId) {
          self.vmMsgWarning('请先选择学生！');
          return false;
        }
        if (type == 'save') {
          self.$confirm('是否保存?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/Educational/zdPro?type=create', 'post', data, function (res) {
              if (res.statu == 1) {
                self.vmMsgSuccess('保存成功！');
              } else {
                self.vmMsgError(res.message);
              }
            })
          }).catch(() => {
          });
        } else {
          self.$router.push({name: 'inSchoolProvePreview', params: {userId: self.userId}});
        }
      }
    }
  }
</script>
<style>
  .inSchoolProve {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .inSchoolProve h3 {
    font-size: 1.25rem;
  }

  .inSchoolProve .inSchoolProve_row {
    margin: 2rem 0;
  }

  .inSchoolProve .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
    -webkit-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    -moz-box-shadow: 0 0 1px 1px #d2d2d2 inset;
    box-shadow: 0 0 1px 1px #d2d2d2 inset;
  }

  .inSchoolProve .treeList_body {
    padding: .875rem;
    height: 43rem;
    overflow: auto;
  }

  .inSchoolProve .treeList_title {
    padding: .875rem .875rem 1.5rem;
    height: 8rem;
  }

  .inSchoolProve .treeList_title h5 {
    font-size: 1rem;
  }

  .inSchoolProve .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .inSchoolProve .treeList .el-tree {
    border: none;
  }

  .inSchoolProve .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .inSchoolProve .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  .inSchoolProve .prove_header {
    padding: 0 2.5rem;
    margin-bottom: 1rem;
  }

  .inSchoolProve .preview {
    text-align: right;
  }

  .inSchoolProve .preview .el-button {
    padding: 10px 25px;
    border-radius: 20px;
  }

  .inSchoolProve .prove_body h6 {
    font-size: 1.125rem;
    text-align: center;
    margin: 3.5rem 0;
  }

  .inSchoolProve .prove_text {
    line-height: 2.5;
  }

  .inSchoolProve .prove_text input {
    border-left: 0;
    border-right: 0;
    border-top: 0;
    border-bottom: 1px solid #4da1ff;
    font-size: 1rem;
  }

  .inSchoolProve .prove_text input.centry {
    text-align: center;
  }

  .inSchoolProve .prove_text input.right, .inSchoolProve .signed {
    text-align: right;
  }

  .inSchoolProve .signed {
    margin-top: 2rem;
  }
</style>
