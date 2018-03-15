<template>
  <div class="newNotice">
    <el-row class="newNotice_content">
      <h3>新建通知</h3>
      <el-row :gutter="24" class="newNotice_center">
        <el-col :span="18">
          <el-row type="flex" align="middle">
            <el-col :span="2">标题：</el-col>
            <el-col :span="12">
              <el-input placeholder="请输入内容" class="newNotice_title_input" v-model="form.title"></el-input>
            </el-col>
            <el-col :span="2" :offset="2">类型：</el-col>
            <el-col :span="6">
              <el-select v-model="form.kind" placeholder="请选择" class="newNotice_title_select">
                <el-option v-for="item in options" :key="item.value" :label="item.label"
                           :value="item.value"></el-option>
              </el-select>
            </el-col>
          </el-row>
          <el-row type="flex" align="middle" class="newNotice_two">
            <el-col :span="2">通知对象：</el-col>
            <el-col :span="22">
              <div class="noticeObj">
                <span v-if="activePersonList1.length==0&&activePersonList2.length==0" class="noStaff">在右边联系人栏中选择</span>
                <el-tag v-if="activePersonList1.length!=0" v-for="(tag,ix) in activePersonList1" :key="ix"
                        :closable="true"
                        @close="handleClosePerson('activePersonList1',ix)">{{tag.name}}
                </el-tag>
                <el-tag v-if="activePersonList2.length!=0" v-for="(tag,ix) in activePersonList2" :key="ix"
                        :closable="true"
                        @close="handleClosePerson('activePersonList2',ix)">{{tag.name}}
                </el-tag>
              </div>
            </el-col>
          </el-row>
          <el-row class="newNotice_two">
            <el-col class="newNotice_edit">编辑内容：</el-col>
          </el-row>
          <el-row class="noticeUeditor">
            <quill-editor style="height: 80%" v-model="form.content"></quill-editor>
          </el-row>
        </el-col>
        <el-col :span="6">
          <el-row class="contact">
            <el-col :span="12" class="contact_all" :class="{'contact_active':contactType==1}">
              <span @click="getContact(1)">全部联系人</span>
            </el-col>
            <el-col :span="12" class="contact_used" :class="{'contact_active':contactType==2}">
              <span @click="getContact(2)">常用联系人</span>
            </el-col>
          </el-row>
          <el-row class="contact_list">
            <el-row class="allContacts" v-show="contactType==1">
              <div id="treeList">
                <ul id="treeDemo1" class="ztree"></ul>
              </div>
            </el-row>
            <div class="commonContacts" :class="{'active':manageGroupBtn_state==2}" v-show="contactType==2">
              <el-row class="treeList">
                <ul id="treeDemo2" class="ztree"></ul>
              </el-row>
              <el-row class="manageGroupBtn">
                <el-row type="flex" align="middle" justify="center" v-show="manageGroupBtn_state==1">
                  <el-button type="primary" @click="operationGroup('open')">分组管理</el-button>
                </el-row>
                <el-row v-show="manageGroupBtn_state==2" class="secBtn">
                  <el-row type="flex" align="middle" justify="center">
                    <el-button type="primary" @click="operationGroup('add')">添加分组</el-button>
                  </el-row>
                  <el-row type="flex" align="middle" justify="center" class="secBtn">
                    <el-button type="danger" @click="operationGroup('exit')">退出分组管理</el-button>
                  </el-row>
                </el-row>
              </el-row>
            </div>
            <el-row class="allContacts active" v-show="contactType==3">
              <div id="chooseAddList">
                <ul id="treeDemo3" class="ztree"></ul>
              </div>
              <el-row type="flex" align="middle" justify="center" class="operationBtn">
                <el-button type="primary" @click="operationGroup('cancel')">取消</el-button>
                <el-button type="primary" @click="operationGroup('confirm')">确定</el-button>
              </el-row>
            </el-row>
          </el-row>
        </el-col>
      </el-row>
    </el-row>
    <el-row class="newNotice_footer">
      <el-col :span="19">
        <el-row>
          <el-col :span="5">
            <span>附件：</span>
            <div class="uploadFile">
              <el-button>选择附件</el-button>
              <input type="file" accept=".xlsx,.xlsm,.xls.doc,.docx,.ppt" class="file_input" @change="sendFile">
            </div>
          </el-col>
          <el-col :span="19" class="fileLists">
            <div v-for="(file,index) in fileList">
              <img v-if="file.fileType==1"
                   src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_ppt.png"
                   alt="">
              <img v-if="file.fileType==2"
                   src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_word.png"
                   alt="">
              <img v-if="file.fileType==3"
                   src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_excel.png"
                   alt="">
              <span :title="file.name">{{file.name}}</span>
              <i class="el-icon-close" @click="deleteFile(index)"></i>
            </div>
          </el-col>
        </el-row>
      </el-col>
      <el-col :span="5">
        <el-button type="primary" :disabled="loading" @click="published(0)">发布</el-button>
        <el-button @click="preview">预览</el-button>
        <!--<el-button @click="published(1)">保存草稿</el-button>-->
      </el-col>
    </el-row>
    <el-dialog
      title="查看通知"
      :visible.sync="dialogVisible"
      :before-close="handleClose"
      :modal="false" class="previewDetail">
      <h3 class="previewDetail_title">{{form.title}}</h3>
      <el-row class="previewDetail_subTitle" :gutter="10">
        <el-col :span="5">发布者：{{userInfo.name}}</el-col>
        <el-col :span="7" class="previewDetail_subTitle_part">发布部门：{{userInfo.department}}</el-col>
        <el-col :span="12" class="previewDetail_subTitle_time">发布时间：{{nowDate|formatDate}}</el-col>
      </el-row>
      <el-row class="previewDetail_center">
        <div v-html="form.content"></div>
      </el-row>
      <el-row>
        <el-button class="previewDetail_annex" type="primary">附件下载</el-button>
      </el-row>
      <el-row class="previewDetail_list clear_fix">
        <div v-for="file in fileList">
          <img v-if="file.fileType==1"
               src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_ppt.png"
               alt="">
          <img v-if="file.fileType==2"
               src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_word.png"
               alt="">
          <img v-if="file.fileType==3"
               src="../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_excel.png"
               alt="">
          <span :title="file.name">{{file.name}}</span>
        </div>
      </el-row>
    </el-dialog>
    <el-dialog
      :title="groupTitle"
      :visible.sync="addGroupVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row type="flex" justify="center">
        <el-col :span="20">
          <el-form @submit.native.prevent :model="groupForm" :rules="groupFormRule" ref="groupForm" label-width="100px">
            <el-form-item label="分组名称：" prop="name">
              <el-input v-model="groupForm.name"></el-input>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" v-show="groupTitle=='添加分组'" @click="sendGroup('add')">添加</el-button>
    <el-button type="primary" v-show="groupTitle!='添加分组'" @click="sendGroup('edit')">保存</el-button>
    <el-button @click="addGroupVisible = false">取消</el-button>
  </span>
    </el-dialog>
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
        editor:'',
        options: [{
          value: 1,
          label: '通知'
        }, {
          value: 2,
          label: '公告'
        }, {
          value: 3,
          label: '通报'
        }, {
          value: 4,
          label: '决议'
        }],
        form: {
          title: '',
          kind: 1,
          uids: [],
          type: 'create',
          content: '',
          draft: 0
        },
        fileList: [],
        dialogVisible: false,
        nowDate: '',
        contactType: 2,
        manageGroupBtn_state: 1,
        zTreeObj1: {},
        zTreeObj2: {},
        zTreeObj3: {},
        activePersonList1: [],
        activePersonList2: [],
        groupTitle: '添加分组',
        addGroupVisible: false,
        groupForm: {
          name: '',
          id: ''
        },
        groupFormRule: {
          name: [
            {required: true, message: '请输入分组名称', trigger: 'blur'},
            {min: 1, max: 6, message: '长度在 1 到 6 个字符', trigger: 'blur'}
          ]
        },
        loading:false
      }
    },
    computed: {
      userInfo() {   //获取登录后的用户信息
        return this.$store.state.userInfo;
      }
    },
    mounted: function () {
      var self = this, data1 = {
        which: 1
      }, setting1 = {
        view: {
          selectedMulti: false
        },
        check: {
          enable: true
        },
        data: {
          simpleData: {
            enable: true
          }
        },
        callback: {
          onCheck: function (treeId, treeNode) {
            const zTree = $.fn.zTree.getZTreeObj("treeDemo1");
            const checkedPersonList = zTree.getCheckedNodes(true);
            self.activePersonList1 = self.aFun(checkedPersonList);
          },
        }
      }, setting2 = {
        view: {
          selectedMulti: false,
          addDiyDom: self.addDiyDom
        },
        check: {
          enable: true
        },
        async: {
          enable: true,
          type: 'post',
          url: req.getRootName()+'/school/Notification/group',
          dataType: 'json',
          dataFilter: function (treeId, parentNode, responseData) {
            var data = responseData.data;
            return data;
          }
        },
        data: {
          simpleData: {
            enable: true
          }
        },
        callback: {
          onCheck: function (treeId, treeNode) {
            const zTree = $.fn.zTree.getZTreeObj("treeDemo2");
            const checkedPersonList = zTree.getCheckedNodes(true);
            self.activePersonList2 = self.aFun(checkedPersonList);
          },
        }
      };
      //全部联系人
      req.ajaxSend('/school/Notification/getUser', 'post', data1, function (res) {
        self.zTreeObj1 = $.fn.zTree.init($("#treeDemo1"), setting1, res.data);
        self.zTreeObj3 = $.fn.zTree.init($("#treeDemo3"), setting1, res.data);
      });
      //常用联系人
      req.ajaxSend('/school/Notification/group', 'post', '', function (res) {
        self.zTreeObj2 = $.fn.zTree.init($("#treeDemo2"), setting2, res.data);
      });
    },
    methods: {
      addDiyDom(treeId, treeNode) {
        var self = this, data, sObj = $("#" + treeNode.tId + "_span");
        if (treeNode.editNameFlag || $("#addBtn_" + treeNode.tId).length > 0) return;
        var addStr = '', classN = self.manageGroupBtn_state == 1 ? 'treeBtns' : 'treeBtns active';
        ;
        if (!treeNode.userId) {
          addStr = "<span class='" + classN + "'>" +
            "<span class='button edit' id='editBtn_" + treeNode.tId +
            "' title='重命名'></span><span class='button add' id='addBtn_" + treeNode.tId +
            "' title='添加人员'></span><span class='button copy' id='copyBtn_" + treeNode.tId +
            "' title='复制分组'></span><span class='button delete' id='deleteBtn_" + treeNode.tId +
            "' title='删除分组'></span></span>"
        } else {
          addStr = "<span class='" + classN + "'><span class='button delete' id='deleteBtn_" + treeNode.tId +
            "' title='删除人员'></span></span>"
        }
        sObj.after(addStr);
        var addBtn = $("#addBtn_" + treeNode.tId);
        if (addBtn) addBtn.bind("click", function () {  //添加人员
          self.groupForm.id = treeNode.id;
          self.contactType = 3;
          self.manageGroupBtn_state = 2;
        });
        var editBtn = $("#editBtn_" + treeNode.tId);
        if (editBtn) editBtn.bind("click", function () {   //重命名
          self.groupForm.name = treeNode.name;
          self.groupForm.id = treeNode.id;
          self.groupTitle = treeNode.name + '-重命名';
          self.addGroupVisible = true;
        });
        var deleteBtn = $("#deleteBtn_" + treeNode.tId);
        if (deleteBtn) deleteBtn.bind("click", function () {   //删除
          self.groupForm.id = treeNode.id;
          if (treeNode.userId) {
            self.$confirm('确定删除人员?', '提示', {
              confirmButtonText: '确定',
              cancelButtonText: '取消',
              type: 'warning'
            }).then(() => {
              data = {
                type: 'delUser',
                groupId: treeNode.pId,
                userId: treeNode.userId
              };
              req.ajaxSend('/school/Notification/group', 'post', data, function (res) {
                if (res.status == 1) {
                  self.vmMsgSuccess('删除成功！');
                  self.activePersonList2 = [];
                  self.zTreeObj2.reAsyncChildNodes(null, "refresh");
                } else {
                  self.vmMsgSuccess(res.msg);
                }
              });
            }).catch(() => {
            });
          } else {
            self.$confirm('确定删除分组?', '提示', {
              confirmButtonText: '确定',
              cancelButtonText: '取消',
              type: 'warning'
            }).then(() => {
              data = {
                type: 'delGroup',
                id: treeNode.id
              };
              req.ajaxSend('/school/Notification/group', 'post', data, function (res) {
                if (res.status == 1) {
                  self.vmMsgSuccess('删除成功！');
                  self.activePersonList2 = [];
                  self.zTreeObj2.reAsyncChildNodes(null, "refresh");
                } else {
                  self.vmMsgError(res.msg);
                }
              });
            }).catch(() => {
            });
          }
        });
      },
      handleClosePerson(name, ix) {
        if (name == 'activePersonList1') {
          this.zTreeObj1.checkNode(this[name][ix], false, true);
        } else {
          this.zTreeObj2.checkNode(this[name][ix], false, true);
        }
        this[name].splice(ix, 1);
      },
      getContact(type) {   //切换联系人
        this.contactType = type;
      },
      operationGroup(type, id, name) {  //操作分组
        var self = this, data;
        if (type == 'open') {
          self.manageGroupBtn_state = 2;
          $('.newNotice #treeDemo2 .treeBtns').addClass('active');
        } else if (type == 'add') {
          self.groupTitle = '添加分组';
          self.addGroupVisible = true;
        } else if (type == 'exit') {
          self.manageGroupBtn_state = 1;
          $('.newNotice #treeDemo2 .treeBtns').removeClass('active');
        } else if (type == 'confirm') {
          var ary = self.zTreeObj3.getCheckedNodes(true);
          data = {
            type: 'user',
            id: self.groupForm.id,
            user: []
          };
          if (ary.length == 0) {
            self.vmMsgWarning('请选择需要添加的人员！');
            return false;
          }
          for (let obj of ary) {
            if (obj.userId) {
              data.user.push({
                id: obj.userId,
                name: obj.name
              })
            }
          }
          req.ajaxSend('/school/Notification/group', 'post', data, function (res) {
            if (res.status == 1) {
              self.vmMsgSuccess('添加成功！');
              self.contactType = 2;
              self.activePersonList2 = [];
              self.zTreeObj2.reAsyncChildNodes(null, "refresh");
            } else {
              self.vmMsgError(res.msg);
            }
          })
        } else {
          self.contactType = 2;
        }
      },
      sendGroup(type) {  //分组请求发起
        var self = this, data;
        if (type == 'add') {
          data = {
            type: 'addGroup',
            group: self.groupForm.name
          };
        } else {
          data = {
            type: 'editGroup',
            id: self.groupForm.id,
            group: self.groupForm.name
          };
        }
        this.$refs['groupForm'].validate((valid) => {
          if (valid) {
            req.ajaxSend('/school/Notification/group', 'post', data, function (res) {
              if (res.status == 1) {
                self.vmMsgSuccess('保存成功！');
                self.addGroupVisible = false;
                self.activePersonList2 = [];
                self.zTreeObj2.reAsyncChildNodes(null, "refresh");
              } else {
                self.vmMsgError(res.msg);
              }
            })
          } else {
            return false;
          }
        });
      },
      sendFile() {   //上传文件
        var fileType, suffix, file = $('.file_input').prop('files')[0];
        if (!file) {
          return false;
        }
        $('.file_input').val('');
        if (this.fileList.length >= 3) {
          this.vmMsgWarning('最多只能上传三个附件！');
          return false;
        }
        var sAry = file.name.split('.'), sL = sAry.length - 1;
        suffix = sAry[sL];
        switch (suffix) {
          case 'ppt':
            fileType = 1;
            break;
          case 'docx':
          case 'doc':
            fileType = 2;
            break;
          case 'xlsx':
          case 'xlsm':
          case 'xls':
            fileType = 3;
            break;
          default:
            this.vmMsgWarning('只能上传word、xlsx、xlsm、xls、ppt格式文件！');
            return false;
        }
        for (let obj of this.fileList) {
          if (file.name == obj.name) {
            this.vmMsgWarning('你已添加过该文件！');
            return false;
          }
        }
        this.fileList.push({'fileType': fileType, 'name': file.name, 'file': file});
      },
      deleteFile(idx) {   //删除文件
        this.fileList.splice(idx, 1);
      },
      handleClose(done) {   //关闭弹框
        done();
      },
      preview() {
        this.dialogVisible = true;
        this.nowDate = new Date().getTime() / 1000;
      },
      published(type) {
        var self = this, sendFormData = new FormData(), nAry = [];
        self.form.draft = type;
        for (let i of self.fileList) {
          sendFormData.append('accessory[]', i.file);
        }
        self.form.uids = self.zTreeObj1.getCheckedNodes(true).concat(self.zTreeObj2.getCheckedNodes(true));
        for (let ob in self.form) {
          if (ob == 'uids') {
            for (let l of self.form[ob]) {
              if (l.userId) {
                nAry.push(l);
                sendFormData.append('uids[]', l.userId);
              }
            }
          } else {
            sendFormData.append(ob, self.form[ob]);
          }
        }
        if (!self.form.title) {
          self.vmMsgWarning('请填写标题！');
          return false;
        }
        if (nAry.length == 0) {
          self.vmMsgWarning('请选择通知对象！');
          return false;
        }
        if (!self.form.content) {
          self.vmMsgWarning('请填公告内容！');
          return false;
        }
        self.loading=true;
        req.ajaxFile('/school/Notification/create', 'post', sendFormData, function (res) {
          self.loading=false;
          if (res.status == 1) {
            self.vmMsgSuccess('新建成功');
          } else {
            self.vmMsgError(res.msg);
          }
        })
      },
      aFun(nodes) {
        var childrenNotHasFalse = [];
        const filterNodes = nodes.filter(function (val) {
          // 记录过程中已经符合条件的id
          // 子孙是否有false
          function jundgeChildrenNotHasFalse(val) {
            if (childrenNotHasFalse.indexOf(val.id) !== -1) {
              return true
            } else {
              val = val.children;

              function hasNotChecked(list) {
                return list.filter(function (valChild) {
                  if (valChild.children) {
                    return hasNotChecked(valChild.children).length > 0;
                  } else {
                    return !valChild.checked;
                  }
                });
              }

              const filteredChildrenLength = hasNotChecked(val).length;
              return filteredChildrenLength === 0 && childrenNotHasFalse.push(val.id);
            }
          }

          // 是否有children
          function hasChildren(val) {
            return !!val.children
          }

          // 是否有父节点
          function hasParent(val) {
            return !!val.pId
          }

          // 查找选中的元素中是否有该节点  有则返回
          function findItem(id) {
            return nodes.filter(function (val) {
              return val.id === id
            })[0];
          }

          // 有父并且父为true并且父的子孙全为选中
          function jundgeParent(val) {
            var hasParentBool = hasParent(val),
              hasParentItem = findItem(val.pId),
              childrenAllTrue = childrenNotHasFalse.indexOf(val.pId) !== -1 || hasParentItem && jundgeChildrenNotHasFalse(hasParentItem);
            return hasParentBool && hasParentItem && childrenAllTrue;
          }

          function jundge(val) {
            var flag = false; // 是否符合条件
            function toTrue() {
              flag = true;
            }

            if (hasChildren(val)) {
              jundgeParent(val) ? '' : jundgeChildrenNotHasFalse(val) ? toTrue() : '';
            } else {
              // 是否有父
              // 1。有父则判断父是否符合规范 符合规范则该元素不加入数组  否则加入
              // 2。无父则直接加入数组
              jundgeParent(val) ? '' : toTrue();
            }
            return flag;
          }

          return jundge(val);
        });
        return filterNodes;
      }
    }
  }
</script>
<style>
  .ztree li span.button.add {
    background-image: url(../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/btn_addpersonnel_n.png);
    background-position: center;
    vertical-align: middle;
    margin-left: 3px;
  }

  .ztree li span.button.edit {
    background-image: url(../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/btn_rechristen_n.png);
    background-position: center;
    vertical-align: middle;
    margin-left: 3px;
  }

  .ztree li span.button.delete {
    background-image: url(../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/btn_delete_n.png);
    background-position: center;
    vertical-align: middle;
    margin-left: 3px;
  }

  .ztree li span.button.copy {
    background-image: url(../../../../assets/img/schManagementSystem/campusOffice/notificationNotice/btn_copy_n.png);
    background-position: center;
    vertical-align: middle;
    margin-left: 3px;
  }

  .newNotice .el-dialog--small {
    width: 554px;
  }

  .newNotice .allContacts #treeList {
    width: 100%;
    height: 45rem;
    margin: auto;
    overflow: auto;
  }

  .newNotice .allContacts.active #chooseAddList {
    height: 40rem;
  }

  .newNotice .allContacts .operationBtn {
    height: 5rem;
  }

  .newNotice .allContacts .operationBtn .el-button.el-button--primary {
    background-color: #17bb9e;
    border-color: #17bb9e;
    padding: 10px 3rem;
  }

  .newNotice .commonContacts .treeList {
    height: 40rem;
    overflow: auto;
  }

  .newNotice .commonContacts.active .treeList {
    height: 38rem;
  }

  .newNotice .commonContacts .manageGroupBtn {
    height: 5rem;
  }

  .newNotice .commonContacts.active .manageGroupBtn {
    height: 6rem;
  }

  .newNotice .manageGroupBtn .secBtn {
    margin-top: .8rem;
  }

  .newNotice .manageGroupBtn > div {
    height: 100%;
  }

  .newNotice .manageGroupBtn .el-button {
    width: 86%;
    padding: .5rem 0;
  }

  .newNotice .manageGroupBtn .el-button.el-button--primary {
    background-color: #17bb9e;
    border-color: #17bb9e;
  }

  .newNotice #treeDemo2 .treeBtns {
    display: none;
  }

  .newNotice #treeDemo2 .treeBtns.active {
    display: inline;
  }
</style>
<style lang="less" scoped>
  .uploadFile {
    display: inline-block;
    position: relative;

    .file_input {
      width: 100%;
      height: 36px;
      border-radius: 18/16rem;
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

  }

  .newNotice {
    padding-top: 20/16rem;
    -webkit-box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    -moz-box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 20/16rem 0;
    background-color: #fff;

    .newNotice_content {
      padding: 0 2rem;
    }

    h3 {
      font-size: 20/16rem;
      color: #4e4e4e;
    }

    .el-select {
      width: 100%;
    }

  }

  .newNotice_center {
    margin-top: 2rem;
  }

  .newNotice_two {
    margin-top: 24/16rem;

    .newNotice_edit {
      padding-bottom: .8rem;
    }

    .noticeObj {
      font-size:14px;
      width: 100%;
      height: 36px;
      border-radius: 6px;
      border: 1px solid #dcdfe6;
      overflow: auto;
      padding: 0 15px;
      -webkit-box-sizing: border-box;
      -moz-box-sizing: border-box;
      box-sizing: border-box;

      span.noStaff {
        color: #c0c4cc;
        line-height: 36px;
      }

      .el-tag {
        background-color: #f08bc4;
        padding: 0 .6rem;
        margin: 5px 1rem 5px 0;
        color: #fff;
      }

    }
  }

  .noticeUeditor {
    height: 39rem;
  }

  .contact {
    padding: .8rem 0;
    text-align: center;
    border-radius: 6px 6px 0 0;
    color: #8c8c8c;
    border: 1px solid #c0c0c0;
    cursor: pointer;

    .contact_all {
      border-right: 1px solid #c0c0c0;
    }

    .contact_active {
      color: #4da1ff;

      &
      :after {
        position: absolute;
        content: '';
        display: block;
        bottom: -.8rem;
        left: 50%;
        -webkit-transform: translateX(-50%);
        -moz-transform: translateX(-50%);
        -ms-transform: translateX(-50%);
        -o-transform: translateX(-50%);
        transform: translateX(-50%);
        width: 50/16rem;
        height: 2px;
        background-color: #4da1ff;
      }

    }
    .contact_all, .contact_used {
      position: relative;
    }

  }

  .contact_list {
    height: 730/16rem;
    border-left: 1px solid #c0c0c0;
    border-right: 1px solid #c0c0c0;
    border-bottom: 1px solid #c0c0c0;
    border-radius: 0 0 6px 6px;
    padding-top: .5rem;

    .el-tree {
      border: none;
    }

  }

  .newNotice_footer {
    margin-top: 24/16rem;
    border-top: 1px solid #c0c0c0;
    padding: 1rem 2rem;

    .fileLists {
      padding: .6rem 0;
      border-left: 1px solid #c0c0c0;
      height: 2.8rem;
    }

    .fileLists > div {
      float: left;
      position: relative;
      font-size: 14/16rem;
      margin-left: 2rem;

      span {
        display: inline-block;
        max-width: 10rem;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
      }

      img {
        width: 22/16rem;
        margin-right: 14/16rem;
      }

      i {
        position: absolute;
        right: -10px;
        top: -5px;
        cursor: pointer;
        font-size: 12px;
      }

    }
    .el-button {
      width: 110/16rem;
      border-radius: 18/16rem;
      padding: 10px 0;
    }

  }

  .previewDetail .el-dialog__body {
    padding: 1rem 20px;
  }

  h3.previewDetail_title {
    text-align: center;
    margin-bottom: 1rem;
    font-size: 1rem;
  }

  .previewDetail_subTitle {
    font-size: 12px;
    padding: .8rem 0;
    border-top: 1px solid #dcdcdc;
    border-bottom: 1px solid #dcdcdc;
  }

  .previewDetail_subTitle_part {
    text-align: center;
  }

  .previewDetail_subTitle_time {
    text-align: right;
  }

  .previewDetail_center {
    min-height: 10rem;
    padding: 1.875rem 0;
  }

  .previewDetail_annex {
    border-radius: 0 18px 18px 0;
    -webkit-box-shadow: 0 5px 5px 1px #d2d2d2;
    -moz-box-shadow: 0 5px 5px 1px #d2d2d2;
    box-shadow: 0 5px 5px 1px #d2d2d2;
    cursor: auto;
  }

  .previewDetail_list {
    margin-top: 1.875rem;
  }

  .previewDetail_list > div {
    float: left;
    border: 1px solid #fff;
    margin-right: 1.25rem;
    padding: 5px 10px;
  }

  .previewDetail_list > div:hover {
    border: 1px solid #d2d2d2;
  }

  .previewDetail_list > div > span {
    display: inline-block;
    max-width: 120px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-left: .875rem;
  }

  .previewDetail .el-dialog__footer {
    -webkit-box-shadow: 0 -10px 20px -5px #d2d2d2;
    -moz-box-shadow: 0 -10px 20px -5px #d2d2d2;
    box-shadow: 0 -10px 20px -5px #d2d2d2;
    margin-top: 1rem;
    padding: 1rem 0;
  }

  @media (max-width: 1600px) {
    .noticeUeditor {
      height: 38.5rem;
    }
  }

  @media (max-width: 1420px) {
    .noticeUeditor {
      height: 37.7rem;
    }
  }
</style>
