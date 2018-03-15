<template>
  <div class="studentGrowthRecord">
    <h3>学生成长记录</h3>
    <el-row :gutter="20" class="studentGrowthRecord_row">
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
      <el-col :span="20">
        <el-row class="treeList">
          <el-row type="flex" align="middle" class="secHeader">
            <h5 class="isActive" v-if="!treeActiveData.userId">请选择学生</h5>
            <h5 v-if="treeActiveData.userId">{{treeActiveData.name}}的成长记录</h5>
          </el-row>
          <el-row class="studentGrowthRecord_content">
            <el-row class="studentGrowthRecord_mainContent">
              <el-row class="arrowContent">
                <img
                  src="../../../../assets/img/schManagementSystem/studentsManagement/studentGrowthRecord/icon_arrow.png"
                  alt="" class="arrowTop">
              </el-row>
              <el-row>
                <el-row class="lineDevice" v-for="(a,index) in tableData" :key="a.recordId">
                  <el-col :span="5">
                    <el-row class="studentGrowthRecord_content_row studentGrowthRecord_content_l">
                      <p>{{a.createTime}}</p>
                      <p>{{a.teacherName}}老师 {{a.post}} {{a.subject}}</p>
                      <span class="circle"></span>
                    </el-row>
                  </el-col>
                  <el-col :span="19">
                    <el-row type="flex" align="middle">
                      <el-col :span="22" class="gap">
                        <el-row class="studentGrowthRecord_content_row studentGrowthRecord_content_center">
                          <el-row type="flex" align="middle">
                            <el-col :span="10">
                              <h5>{{a.title}}</h5>
                            </el-col>
                            <el-col :span="14">
                              <el-tag
                                v-for="tag in a.la"
                                :key="tag.labelId"
                              >
                                {{tag.labelName}}
                              </el-tag>
                            </el-col>
                          </el-row>
                          <el-row class="detail">
                            <p>{{a.content}}</p>
                            <div class="showImg">
                              <div v-for="(data,ix) in a.url" :key="ix" @click="showImg('open',index)">
                                <img :src="data" alt="">
                              </div>
                            </div>
                          </el-row>
                        </el-row>
                      </el-col>
                      <el-col :span="2">
                        <el-row type="flex" justify="center" class="operation">
                          <span class="edit" @click="operationRecord('edit',index)"><i class="el-icon-edit"></i></span>
                        </el-row>
                        <el-row type="flex" justify="center" class="operation">
                          <span class="delete" @click="operationRecord('delete',index)"><i
                            class="el-icon-close"></i></span>
                        </el-row>
                      </el-col>
                    </el-row>
                  </el-col>
                </el-row>
              </el-row>
            </el-row>
          </el-row>
        </el-row>
      </el-col>
    </el-row>
    <el-dialog
      title="编辑成长记录"
      :modal="false"
      :visible.sync="dialogVisible"
      :before-close="handleClose">
      <el-row class="writeCommentForm">
        <el-form ref="form" :rules="formRules" :model="form" label-width="80px">
          <el-form-item label="标题：" prop="title">
            <el-row>
              <el-input placeholder="请输入标题" v-model="form.title"></el-input>
            </el-row>
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
                  :key="ix"
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
                    <img :src="file" alt="">
                    <span @click="deleteFile(ix)" class="deleteFile"><i
                      class="el-icon-circle-close"></i></span>
                  </span>
            </div>
          </el-form-item>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="saveComment">提交</el-button>
  </span>
    </el-dialog>
    <div class="fileImgCarousel" v-show="fileListState">
      <div class="fileImgCarousel_body">
        <span class="closeFileImgCarousel" @click="showImg('close')"><i class="el-icon-close"></i></span>
        <div class="fileImgCarousel_img">
          <el-carousel :autoplay="false" indicator-position="none">
            <el-carousel-item v-for="item in fileList" :key="item">
              <div>
                <img :src="item" alt="">
              </div>
            </el-carousel-item>
          </el-carousel>
        </div>
      </div>
    </div>
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
        tableData: [],
        dialogVisible: false,
        form: {
          title: '',
          label: [],
          content: '',
          recordId: '',
          url: []
        },
        userId: '',
        tagState: {
          editTagState: false,
          addTagState: false
        },
        addTagValue: '',
        tagList: [],
        fileList: [],
        fileListState: false,
        formRules: {
          title: [
            {required: true, message: '请输入标题', trigger: 'blur'}
          ],
          content: [
            {required: true, message: '请填写评语', trigger: 'blur'}
          ]
        },
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
    },
    methods: {
      filterNode(value, data) {
        if (!value) return true;
        if (data.name) {
          data.name = data.name.toString();
          return data.name.indexOf(value) !== -1;
        }
      },
      choosePerson(node) {
        if (node.userId) {
          var self = this, data = {
            userId: node.userId
          };
          self.treeActiveData = node;
          req.ajaxSend('/school/Studentsgrowthrecord/studentsRecord?type=getUserList', 'get', data, function (res) {
            self.tableData = res.data;
          })
        }
      },
      operationRecord(type, idx) {
        var self = this, data;
        if (type == 'edit') {  //编辑
          self.fileList = [];
          self.dialogVisible = true;
          self.form.title = self.tableData[idx].title;
          self.form.recordId = self.tableData[idx].recordId;
          self.form.label = self.tableData[idx].label;
          self.form.content = self.tableData[idx].content;
          self.userId = self.tableData[idx].userId;
          for (let obj of self.tableData[idx].url) {
            self.fileList.push(obj);
          }
        } else {
          self.$confirm('确定删除该记录?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            data = {
              recordId: self.tableData[idx].recordId
            };
            req.ajaxSend('/school/Studentsgrowthrecord/studentsRecord?type=delete', 'post', data, function (res) {
              if (res.statu == 1) {
                self.vmMsgSuccess('删除记录成功！');
                self.tableData.splice(idx, 1);
              } else {
                self.vmMsgError(res.message);
              }
            });
          }).catch(() => {
          });
        }
      },
      handleClose(done) {   //关闭弹框
        done();
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
      removeTag(ix) {   //关闭弹框中的标签
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
        if (self.fileList.length >= 3) {
          self.vmMsgWarning('最多只能上传3张图片！');
          return false;
        }
        data.append('userFile', file);
        req.ajaxFile('/school/Studentsgrowthrecord/studentsRecord?type=uploda', 'post', data, function (res) {
          if (res.statu == 1) {
            self.vmMsgSuccess('上传成功！');
            self.fileList.push(res.url);
          } else {
            self.vmMsgError('上传失败！');
          }
        })
      },
      showImg(type, ix) {
        if (type == 'open') {
          this.fileListState = true;
          this.fileList = this.tableData[ix].url;
        } else {
          this.fileListState = false;
        }
      },
      saveComment() {
        var self = this;
        self.form.url = [];
        for (let obj of self.fileList) {
          self.form.url.push(obj);
        }
        self.$refs['form'].validate((valid) => {
          if (valid) {
            req.ajaxSend('/school/Studentsgrowthrecord/studentsRecord?type=updataRecord', 'post', self.form, function (res) {
              if (res.statu == 1) {
                let data = {
                  userId: self.userId
                };
                self.vmMsgSuccess('修改成功！');
                self.dialogVisible = false;
                req.ajaxSend('/school/Studentsgrowthrecord/studentsRecord?type=getUserList', 'get', data, function (res) {
                  self.tableData = res.data;
                })
              } else {
                self.vmMsgError(res.message);
              }
            })
          } else {
            return false;
          }
        });
      }
    }
  }
</script>
<style>
  .studentGrowthRecord {
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }

  .studentGrowthRecord h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .studentGrowthRecord .studentGrowthRecord_row {
    margin-top: 3rem;
  }

  .studentGrowthRecord .treeList {
    border: 1px solid #d2d2d2;
    border-radius: 5px;
    height: 52.25rem;
  }

  .studentGrowthRecord .treeList .isActive {
    color: #ff5b5b;
  }

  .studentGrowthRecord .treeList_body {
    padding: .875rem;
    height: 44rem;
    overflow: auto;
  }

  .studentGrowthRecord .treeList_title {
    padding: .875rem .875rem 1.5rem;
  }

  .studentGrowthRecord h5 {
    font-size: 1rem;
  }

  .studentGrowthRecord .treeList .treeInput {
    margin: .875rem 0 0;
  }

  .studentGrowthRecord .treeList .el-tree {
    border: none;
  }

  .studentGrowthRecord .el-input-group--prepend .el-input__inner {
    border-radius: 20px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  .studentGrowthRecord .el-input-group__prepend {
    border-radius: 20px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
  }

  .studentGrowthRecord .secHeader {
    height: 3.125rem;
    padding: 0 1rem;
    border-bottom: 1px solid #d2d2d2;
  }

  .studentGrowthRecord .studentGrowthRecord_content {
    height: 46.125rem;
    overflow: auto;
    margin-top: 3rem;
  }

  .studentGrowthRecord .lineDevice {
    margin: 3rem 0;
  }

  .studentGrowthRecord .studentGrowthRecord_mainContent {
    position: relative;
    min-height: 46.125rem;
  }

  .studentGrowthRecord .studentGrowthRecord_mainContent .gap {
    margin-left: 3rem;
  }

  .studentGrowthRecord .studentGrowthRecord_mainContent .arrowContent {
    position: absolute;
    left: 20.8333%;
    height: 97%;
    width: .2rem;
    background-color: #dcdcdc;
    bottom: 0;
  }

  .studentGrowthRecord .studentGrowthRecord_mainContent .arrowTop {
    width: 1.75rem;
    position: absolute;
    top: -1.2rem;
    left: -.775rem;
  }

  .studentGrowthRecord .studentGrowthRecord_content_l {
    padding: 1rem;
  }

  .studentGrowthRecord .studentGrowthRecord_content_l .circle {
    position: absolute;
    width: 1.2rem;
    height: 1.2rem;
    border-radius: 100%;
    background: rgba(77, 161, 255, .5);
    top: 1rem;
    right: -.75rem;
  }

  .studentGrowthRecord .studentGrowthRecord_content_l .circle:before {
    display: block;
    content: '';
    width: .7rem;
    height: .7rem;
    background-color: #4da1ff;
    border-radius: 100%;
    position: absolute;
    top: 50%;
    left: 50%;
    -webkit-transform: translate(-50%, -50%);
    -moz-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    -o-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
  }

  .studentGrowthRecord .studentGrowthRecord_content_row {
    position: relative;
  }

  .studentGrowthRecord .studentGrowthRecord_content_center {
    border: 1px solid #d2d2d2;
    padding: .5rem 1rem;
    border-radius: 5px;
    background-color: #fff;
  }

  .studentGrowthRecord .studentGrowthRecord_content_center .el-tag {
    margin: .2rem .625rem .2rem 0;
  }

  .studentGrowthRecord .studentGrowthRecord_content_center:before {
    display: block;
    content: '';
    position: absolute;
    width: .8rem;
    height: .8rem;
    background-color: #fff;
    border-left: 1px solid #d2d2d2;
    border-top: 1px solid #d2d2d2;
    -webkit-transform: rotate(-45deg);
    -moz-transform: rotate(-45deg);
    -ms-transform: rotate(-45deg);
    -o-transform: rotate(-45deg);
    transform: rotate(-45deg);
    left: -.5rem;
    top: 1rem;
  }

  .studentGrowthRecord .studentGrowthRecord_content_center .detail {
    margin: 1.5rem 0;
    line-height: 2;
  }

  .studentGrowthRecord .edit, .studentGrowthRecord .delete {
    display: block;
    width: 2rem;
    height: 2rem;
    border-radius: 100%;
    border: 2px solid;
    text-align: center;
    line-height: 2rem;
    cursor: pointer;
  }

  .studentGrowthRecord .edit {
    border-color: #53a0f6;
    color: #53a0f6;
  }

  .studentGrowthRecord .delete {
    border-color: #ff6767;
    color: #ff6767;
  }

  .studentGrowthRecord .operation + .operation {
    margin-top: 2rem;
  }

  .studentGrowthRecord .writeCommentForm .el-textarea__inner, .studentGrowthRecord .templateForm .el-textarea__inner {
    height: 14.375rem;
    font-family: inherit;
  }

  .studentGrowthRecord .writeCommentForm .el-button {
    padding: 8px 15px;
  }

  .studentGrowthRecord .el-tag {
    background-color: #f08bc5;
    margin-right: .6rem;
    color: #fff;
  }

  .studentGrowthRecord .writeCommentForm .el-checkbox + .el-checkbox {
    margin-left: 0;
  }

  .studentGrowthRecord .writeCommentForm .el-checkbox {
    margin-right: 1rem;
  }

  .studentGrowthRecord .writeCommentForm_btn .el-button {
    padding: 5px 15px;
  }

  .studentGrowthRecord .writeCommentForm_btn_edit {
    text-align: right;
  }

  .studentGrowthRecord .writeCommentForm_btn_add .el-input {
    width: 50%;
  }

  .studentGrowthRecord .writeCommentForm .writeCommentForm_edit {
    background-color: #13b5b1;
    color: #fff;
    border-color: #13b5b1;
  }

  .studentGrowthRecord .el-dialog--small {
    width: 550px;
  }

  .studentGrowthRecord .uploadImg {
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

  .studentGrowthRecord .uploadImg .file_input {
    width: 100%;
    height: 30px;
    border-radius: 1.125rem;
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

  .studentGrowthRecord .showImg > div {
    width: 10%;
    float: right;
    margin-left: 1rem;
  }

  .studentGrowthRecord .showImg img {
    width: 100%;
    height: auto;
  }

  .studentGrowthRecord .fileList > span {
    margin-right: 1rem;
    display: inline-block;
    width: 4rem;
    padding: 1rem;
    position: relative;
  }

  .studentGrowthRecord .fileList > span img {
    width: 100%;
    height: auto;
  }

  .studentGrowthRecord .deleteFile {
    cursor: pointer;
    position: absolute;
    top: 0;
    right: 0;
  }

  .studentGrowthRecord .fileList > span {
    margin-right: 1rem;
    display: inline-block;
    width: 4rem;
    padding: 1rem;
    position: relative;
  }

  .studentGrowthRecord .fileList > span img {
    width: 100%;
    height: auto;
  }

  .studentGrowthRecord .fileImgCarousel {
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: 66;
    position: fixed;
    background: rgba(0, 0, 0, .5);
  }

  .studentGrowthRecord .fileImgCarousel_body {
    width: 360px;
    position: relative;
    left: 50%;
    top: 50%;
    -webkit-transform: translate(-50%, -50%);
    -moz-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    -o-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
  }

  .studentGrowthRecord .fileImgCarousel_body img {
    width: 100%;
    height: auto;
  }

  .studentGrowthRecord .closeFileImgCarousel {
    position: absolute;
    top: 0;
    right: -60px;
    color: #fff;
    font-size: 25px;
    cursor: pointer;
  }
</style>
