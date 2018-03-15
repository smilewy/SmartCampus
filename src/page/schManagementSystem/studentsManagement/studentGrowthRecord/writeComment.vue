<template>
  <div class="writeComment">
    <h3>写评语</h3>
    <el-row :gutter="20" class="writeComment_row">
      <el-col :span="4">
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
              :highlight-current="true"
              :filter-node-method="filterNode"
              :props="defaultProps"
              @node-click="choosePerson">
            </el-tree>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="10">
        <el-row class="treeList">
          <el-row type="flex" align="middle" class="secHeader">
            <h5 :class="{'isActive':!treeActiveData.userId}">{{treeActiveData.name||'请选择学生'}}</h5>
          </el-row>
          <el-row class="writeCommentForm">
            <el-form ref="form" :model="form" :rules="formRules" label-width="80px">
              <el-form-item label="标题：" prop="title">
                <el-input placeholder="请输入标题,1-20个字符" :maxlength="20" v-model="form.title"></el-input>
              </el-form-item>
              <el-form-item>
                <el-row>
                  <el-row v-show="!tagState.editTagState">
                    <el-checkbox-group v-model="form.label">
                      <el-checkbox v-for="tag in tagList" :label="tag.labelId" :vlaue="tag.labelId"
                                   :key="tag.labelId">{{tag.labelName}}
                      </el-checkbox>
                    </el-checkbox-group>
                  </el-row>
                  <el-row class="editTag" v-show="tagState.editTagState">
                    <el-tag
                      v-for="(tag,ix) in tagList"
                      :key="tag.labelId"
                      :closable="true"
                      @close="removeTag(ix)"
                    >
                      {{tag.labelName}}
                    </el-tag>
                  </el-row>
                </el-row>
                <el-row class="writeCommentForm_btn" v-show="!tagState.editTagState&&!tagState.addTagState">
                  <el-button type="primary" @click="operationTag('add',true)">添加</el-button>
                  <el-button class="writeCommentForm_edit" @click="operationTag('edit',true)">编辑</el-button>
                </el-row>
                <el-row class="writeCommentForm_btn writeCommentForm_btn_edit" v-show="tagState.editTagState">
                  <el-button type="primary" @click="operationTag('edit',false)">退出编辑</el-button>
                </el-row>
                <el-row class="writeCommentForm_btn writeCommentForm_btn_add" v-show="tagState.addTagState">
                  <el-input v-model="addTagValue"></el-input>
                  <el-button type="primary" @click="operationTag('add',false,true)">确定</el-button>
                  <el-button class="writeCommentForm_edit" @click="operationTag('add',false)">取消</el-button>
                </el-row>
              </el-form-item>
              <el-form-item label="评语：" prop="content">
                <el-input type="textarea" resize="none" placeholder="请填写评语内容" v-model="form.content"></el-input>
              </el-form-item>
              <el-form-item label="附件：">
                <div class="uploadImg">
                  <i class="el-icon-plus"></i>
                  <input type="file" accept="image/jpeg,image/jpg,image/png" class="file_input" @change="sendFile">
                </div>
                <div class="fileList" v-if="fileList.length!=0">
                  <span v-for="(file,ix) in fileList" :key="ix">
                    <img :src="file.url" alt="">
                    <span @click="deleteFile(ix)" class="deleteFile"><i
                      class="el-icon-circle-close"></i></span>
                  </span>
                </div>
              </el-form-item>
            </el-form>
          </el-row>
          <el-row class="saveComment">
            <el-button type="primary" @click="saveComment">提交</el-button>
          </el-row>
        </el-row>
      </el-col>
      <el-col :span="10">
        <el-row class="treeList">
          <el-row type="flex" align="middle" class="secHeader">
            <el-col :span="16">
              <h5><span>评论模板</span> <span class="doubleC">（双击引用）</span></h5>
            </el-col>
            <el-col :span="8">
              <el-button @click="openDialog">添加模板</el-button>
            </el-col>
          </el-row>
          <el-row class="templateLabelList">
            <el-row>
              <span class="templateLabel" :class="{'active':label.checked}" v-for="(label,index) in labelList"
                    :key="label.modelTypeId" @click="searchBabel(index)">{{label.modelTypeName}}</span>
            </el-row>
            <el-row class="templateLabel_search">
              <el-input
                placeholder="请输入关键字"
                suffix-icon="el-icon-search"
                v-model="selectParam.valueData"
                @change="handleIconClick">
              </el-input>
            </el-row>
          </el-row>
          <el-row class="alertsList">
            <el-table
              :data="tableData"
              style="width: 100%"
              v-loading="loading1"
              element-loading-text="拼命加载中"
            >
              <el-table-column
                type="index"
                label="序号"
                width="80">
              </el-table-column>
              <el-table-column
                label="内容">
                <template slot-scope="scope">
                  <div @dblclick="operationTemplate('set',scope.$index)">{{scope.row.content}}</div>
                </template>
              </el-table-column>
              <el-table-column
                prop="frequency"
                label="引用数">
              </el-table-column>
              <el-table-column
                label="操作">
                <template slot-scope="scope">
                  <div>
                    <span class="operation" @click="operationTemplate('top',scope.$index)">置顶</span>
                    <span class="operation" @click="operationTemplate('delete',scope.$index)">删除</span>
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
        </el-row>
      </el-col>
    </el-row>
    <el-dialog
      title="添加模板"
      :modal="false"
      :visible.sync="dialogVisible"
      :before-close="handleClose">
      <el-row type="flex" justify="center" class="templateForm">
        <el-col :span="18">
          <el-form ref="templateForm" :rules="templateFormRules" :model="templateForm" label-width="70px">
            <el-form-item prop="modelTypeId" label="类型：">
              <el-select v-model="templateForm.modelTypeId" placeholder="请选择" style="width: 100%">
                <el-option :label="templateType.modelTypeName" :value="templateType.modelTypeId"
                           v-for="templateType in labelList" :key="templateType.modelTypeId"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item prop="content" label="评语：">
              <el-input type="textarea" resize="none" placeholder="请填写评语内容" v-model="templateForm.content"></el-input>
            </el-form-item>
            <el-form-item>
              <span class="tip">评语首尾请勿添加标点符号</span>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="saveTemplate">提交</el-button>
  </span>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'

  export default {
    data() {
      return {
        treeData: [],
        treeActiveData: {},
        defaultProps: {
          children: 'data',
          label: 'name'
        },
        filterText: '',
        form: {
          title: '',
          label: [],
          content: '',
          userId: '',
          url: []
        },
        tagState: {
          editTagState: false,
          addTagState: false
        },
        addTagValue: '',
        tagList: [],
        fileList: [],
        labelList: [],
        tableData: [],
        selectParam: {
          modelTypeId: '',
          valueData: ''
        },
        dialogVisible: false,
        templateForm: {
          modelTypeId: '',
          content: ''
        },
        templateFormRules: {
          modelTypeId: [
            {required: true, type: 'number', message: '请选择类型', trigger: 'change'}
          ],
          content: [
            {required: true, message: '请填写评语', trigger: 'blur'}
          ]
        },
        formRules: {
          title: [
            {required: true, message: '请输入标题', trigger: 'blur'}
          ],
          content: [
            {required: true, message: '请填写评语', trigger: 'blur'}
          ]
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
      var self = this;
      //得到待选学生
      self.loading = true;
      req.ajaxSend('/school/Studentsgrowthrecord/basicsSet?type=getGradeClassStudent', 'get', '', function (res) {
        self.treeData = res.data;
        self.loading = false;
      });
      //得到评语标签
      req.ajaxSend('/school/Studentsgrowthrecord/basicsSet?type=getLabel', 'get', '', function (res) {
        self.tagList = res.data;
      });
      //得到模板标签类型
      req.ajaxSend('/school/Studentsgrowthrecord/basicsSet?type=getModelType', 'get', '', function (res) {
        for (let obj of res.data) {
          obj.checked = false;
        }
        self.labelList = res.data;
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
      choosePerson(data) {
        if (data.userId) {
          this.treeActiveData = data;
          this.form.userId = data.userId;
        }
      },
      operationTag(name, state, isave) {
        var self = this, data;
        if (name == 'edit') {  //编辑
          self.tagState.editTagState = state;
        } else {   //添加
          if (isave) {  //保存添加
            data = {
              labelName: self.addTagValue
            };
            req.ajaxSend('/school/Studentsgrowthrecord/basicsSet?type=createLabel', 'post', data, function (res) {
              if (res.statu == 1) {
                self.vmMsgSuccess('添加标签成功！');
                req.ajaxSend('/school/Studentsgrowthrecord/basicsSet?type=getLabel', 'get', '', function (res) {
                  self.tagList = res.data;
                })
              } else {
                self.vmMsgError(res.message);
              }
            })
          } else {   //取消添加
            self.tagState.addTagState = state;
          }
        }
      },
      removeTag(ix) {
        var self = this, data = {
          labelId: self.tagList[ix].labelId
        };
        self.$confirm('确定删除该标签?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Studentsgrowthrecord/basicsSet?type=deleteLabel', 'post', data, function (res) {
            if (res.statu == 1) {
              self.vmMsgSuccess('删除标签成功！');
              self.tagList.splice(ix, 1);
            } else {
              self.vmMsgError(res.message);
            }
          });
        }).catch(() => {
        });
      },
      deleteFile(ix) {
        this.fileList.splice(ix, 1);
      },
      sendFile() {   //上传文件
        // 实例化一个表单数据对象
        var self = this, file = $('.file_input').prop('files')[0], suffix, sAry, len, data = new FormData();
        if (!file) {
          return false;
        }
        $('.file_input').val('');
        if (self.fileList.length >= 3) {
          self.vmMsgWarning('最多只能上传3张图片！');
          return false;
        }
        sAry = file.name.split('.');
        len = sAry.length - 1;
        suffix = sAry[len];
        if (suffix != 'jpeg' && suffix != 'jpg' && suffix != 'png') {
          self.vmMsgWarning('只能上传jpeg、jpg、png格式图片！');
          return false;
        }
        data.append('userFile', file);
        req.ajaxFile('/school/Studentsgrowthrecord/basicsSet?type=uploda', 'post', data, function (res) {
          if (res.statu == 1) {
            let d = {
              url: res.url
            };
            self.vmMsgSuccess('上传成功！');
            self.fileList.push(d);
          } else {
            self.vmMsgError('上传失败！');
          }
        })
      },
      openDialog() {
        this.dialogVisible = true;
        for (let name in this.templateForm) {
          this.templateForm[name] = '';
        }
      },
      handleClose(done) {
        done();
      },
      handleIconClick() {  //输入框查询模板
        this.loadTableData(this.selectParam);
      },
      searchBabel(idx) {   //点击标签查询
        this.selectParam.valueData = '';
        this.selectParam.modelTypeId = this.labelList[idx].modelTypeId;
        for (let obj of this.labelList) {
          obj.checked = false;
        }
        this.labelList[idx].checked = true;
        this.loadTableData(this.selectParam);
      },
      operationTemplate(type, idx) {
        var self = this, data = {
          templateId: self.tableData[idx].templateId
        };
        if (type == 'top') {
          req.ajaxSend('/school/Studentsgrowthrecord/basicsSet?type=modelToTop', 'get', data, function (res) {
            if (res.statu == 1) {
              self.vmMsgSuccess('置顶成功！');
              self.loadTableData(self.selectParam);
            } else {
              self.vmMsgError(res.message);
            }
          })
        } else if (type == 'set') {
          req.ajaxSend('/school/Studentsgrowthrecord/basicsSet?type=addMoBan', 'post', data, function (res) {
            if (res.statu == 1) {
              if (self.form.content) {
                self.form.content = self.form.content + '，' + self.tableData[idx].content;
              } else {
                self.form.content = self.tableData[idx].content;
              }
              self.tableData[idx].frequency = Number.parseInt(self.tableData[idx].frequency) + 1;
            } else {
              self.vmMsgError(res.message);
            }
          })
        } else {
          self.$confirm('确定删除该模板?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/Studentsgrowthrecord/basicsSet?type=delModel', 'post', data, function (res) {
              if (res.statu == 1) {
                self.vmMsgSuccess('删除成功！');
                self.loadTableData(self.selectParam);
              } else {
                self.vmMsgError(res.message);
              }
            })
          }).catch(() => {
          });
        }
      },
      saveTemplate() {  //保存模板
        var self = this;
        this.$refs['templateForm'].validate((valid) => {
          if (valid) {
            this.dialogVisible = false;
            req.ajaxSend('/school/Studentsgrowthrecord/basicsSet?type=createModel', 'post', self.templateForm, function (res) {
              if (res.statu == 1) {
                self.vmMsgSuccess('添加成功！');
                self.loadTableData(self.selectParam);
              } else {
                self.vmMsgError(res.message);
              }
            })
          } else {
            return false;
          }
        });
      },
      saveComment() {
        var self = this;
        if (!self.form.userId) {
          self.vmMsgWarning('请选择学生！');
          return false;
        }
        self.form.url = [];
        for (let obj of self.fileList) {
          self.form.url.push(obj.url);
        }
        self.$refs['form'].validate((valid) => {
          if (valid) {
            req.ajaxSend('/school/Studentsgrowthrecord/basicsSet?type=createStudentRecord', 'post', self.form, function (res) {
              if (res.statu == 1) {
                self.vmMsgSuccess('保存成功！');
              } else {
                self.vmMsgError(res.message);
              }
            })
          } else {
            return false;
          }
        })
      },
      loadTableData(data) {
        var self = this;
        self.loading1 = true;
        req.ajaxSend('/school/Studentsgrowthrecord/basicsSet?type=modelList', 'get', data, function (res) {
          self.tableData = res.data;
          self.loading1 = false;
        })
      }
    }
  }
</script>
<style>
  .writeComment {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    font-size: 14px;
  }

  .writeComment h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .writeComment .writeComment_row {
    margin-top: 3rem;
  }

  .writeComment .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
  }

  .writeComment .treeList .isActive {
    color: #ff5b5b;
  }

  .writeComment .treeList_body {
    padding: .875rem;
    height: 44rem;
    overflow: auto;
  }

  .writeComment .treeList_title {
    padding: .875rem .875rem 1.5rem;
  }

  .writeComment .treeList_title h5, .writeComment .secHeader h5 {
    font-size: 1rem;
  }

  .writeComment .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .writeComment .treeList .el-tree {
    border: none;
  }

  .writeComment .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .writeComment .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  .writeComment .secHeader {
    height: 3.125rem;
    padding: 0 1rem;
    border-bottom: 1px solid #d2d2d2;
  }

  .writeComment .secHeader .el-button {
    padding: .5rem 1rem;
    font-size: .875rem;
    float: right;
  }

  .writeComment .secHeader .doubleC, .writeComment .tip {
    color: #999999;
  }

  .writeComment .writeCommentForm {
    padding: 2rem 1rem;
    height: 44rem;
    overflow: auto;
  }

  .writeComment .writeCommentForm .el-textarea__inner, .writeComment .templateForm .el-textarea__inner {
    height: 14.375rem;
    font-family: inherit;
  }

  .writeComment .writeCommentForm .el-button {
    padding: 8px 15px;
  }

  .writeComment .writeCommentForm .el-tag {
    background-color: #f08bc5;
    margin-right: .6rem;
    color: #fff;
  }

  .writeComment .writeCommentForm .el-checkbox + .el-checkbox {
    margin-left: 0;
  }

  .writeComment .writeCommentForm .el-checkbox {
    margin-right: 1rem;
  }

  .writeComment .writeCommentForm_btn .el-button {
    padding: 5px 15px;
  }

  .writeComment .writeCommentForm_btn_edit {
    text-align: right;
  }

  .writeComment .writeCommentForm_btn_add .el-input {
    width: 50%;
  }

  .writeComment .writeCommentForm .writeCommentForm_edit {
    background-color: #13b5b1;
    color: #fff;
    border-color: #13b5b1;
  }

  .writeComment .saveComment {
    text-align: center;
    padding: 1rem;
  }

  .writeComment .saveComment .el-button {
    width: 100%;
  }

  .writeComment .uploadImg {
    display: inline-block;
    position: relative;
    width: 4rem;
    height: 4rem;
    line-height: 4rem;
    text-align: center;
    border: 1px dashed #d2d2d2;
    color: #d2d2d2;
    font-size: 1.2rem;
  }

  .writeComment .uploadImg .file_input {
    width: 100%;
    height: 100%;
    position: absolute;
    right: 0;
    top: 0;
    z-index: 1;
    -moz-opacity: 0;
    -ms-opacity: 0;
    -webkit-opacity: 0;
    opacity: 0; /*css属性——opcity不透明度，取值0-1*/
    filter: alpha(opacity=0); /*兼容IE8及以下--filter属性是IE特有的，它还有很多其它滤镜效果，而filter: alpha(opacity=0); 兼容IE8及以下的IE浏览器(如果你的电脑IE是8以下的版本，使用某些效果是可能会有一个允许ActiveX的提示,注意点一下就ok啦)*/
    cursor: pointer;
  }

  .writeComment .templateLabelList {
    padding: 0 1rem;
  }

  .writeComment .templateLabel {
    display: inline-block;
    padding: .2rem 1rem;
    border: 1px solid #d2d2d2;
    border-radius: 4px;
    font-size: .875rem;
    margin-right: .625rem;
    margin-top: 1rem;
    cursor: pointer;
  }

  .writeComment .templateLabel.active {
    background-color: #4da1ff;
    color: #fff;
  }

  .writeComment .templateLabel_search {
    margin: 1rem 0;
  }

  .writeComment .templateLabel_search .el-input__inner {
    border-radius: 20px;
  }

  .writeComment .alertsList .operation {
    color: #4da1ff;
    cursor: pointer;
  }

  .writeComment .alertsList .operation.active {
    color: #ff5b5b;
    cursor: pointer;
  }

  .writeComment .el-table th, .writeComment .el-table td {
    text-align: center;
  }

  .writeComment .el-dialog--small {
    width: 550px;
  }

  .writeComment .deleteFile {
    cursor: pointer;
    position: absolute;
    top: 0;
    right: 0;
  }

  .writeComment .fileList > span {
    margin-right: 1rem;
    display: inline-block;
    width: 4rem;
    padding: 1rem;
    position: relative;
  }

  .writeComment .fileList > span img {
    width: 100%;
    height: auto;
  }
</style>
