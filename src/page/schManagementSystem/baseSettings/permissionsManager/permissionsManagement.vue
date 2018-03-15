<template>
  <div class="permissionsManagement">
    <h3>权限管理</h3>
    <el-row type="flex" align="middle" class="role">
      <el-col :span="16">
        <span>选择角色：</span>
        <el-select v-model="roleValue" placeholder="请选择">
          <el-option
            v-for="item in roleLists"
            :key="item.roleId"
            :label="item.roleName"
            :value="item.roleName">
          </el-option>
        </el-select>
      </el-col>
      <el-col :span="8" class="newRole_btn">
        <el-button type="primary" @click="savePermission">保存</el-button>
        <el-button @click="dialogVisible = true">创建角色</el-button>
      </el-col>
    </el-row>
    <el-row class="d_line"></el-row>
    <el-row class="permissionsBars">
      <el-row>
        <span>选择一级导航：</span>
        <el-select v-model="activeBar" placeholder="请选择">
          <el-option
            v-for="item in navBars"
            :key="item.modelId"
            :label="item.modelName"
            :value="item.modelName">
          </el-option>
        </el-select>
        <el-button type="primary" class="selectPermissionBtn" @click="selectPermissions">查询</el-button>
      </el-row>
      <el-row class="permissionsBars_list">
        <div class="rootNode" v-if="activeBarNone">
          <el-button>{{activeBarNone}}</el-button>
        </div>
        <permissions-List :data.sync="permissionsList" :isChild="false" @setPermission="setPermissions"/>
      </el-row>
    </el-row>
    <el-dialog
      title="创建角色"
      :visible.sync="dialogVisible"
      :modal="false"
      :before-close="handleClose">
      <el-row type="flex" align="middle" class="roleName">
        <el-col :span="6">输入角色名：</el-col>
        <el-col :span="18">
          <el-input v-model="roleName" placeholder="请输入内容"></el-input>
        </el-col>
      </el-row>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="saveMsg">提 交</el-button>
  </span>
    </el-dialog>
  </div>
</template>
<script>
  import perList from './permissionsLists'
  import req from '@/assets/js/common'
  export default{
    data(){
      return {
        roleLists: [],
        navBars: [],
        activeBar: '',
        activeBarNone: '',
        permissionsList: [],
        roleValue: '',
        roleName: '',
        dialogVisible: false,
        param: {
          roleId: '',
          modelId: ''
        }
      }
    },
    components: {
      'permissions-List': perList
    },
    created(){
      var self = this;
      //角色列表
      req.ajaxSend('/school/User/getRoleList', 'post', {}, function (res) {
        self.roleLists = res;
      });
      //一级导航列表
      req.ajaxSend('/school/user/getOneNav', 'post', {}, function (res) {
        for (let [idx, obj] of res.entries()) {
          if (obj.modelName == '首页') {
            res.splice(idx, 1);
            break;
          }
        }
        self.navBars = res;
      });
    },
    methods: {
      setPermissions(val){
        val.statu = val.statu == 1 ? 0 : 1;
        if (val.statu == 1 && val.parent) {
          setParentState(val);
        }
        if (val.level < 3 && val.child) {
          if (val.statu == 0) {
            setChildState(val);
          }
        }
        if (val.level >= 3 && val.child) {
          setChildState(val);
        }
        function setChildState(data) {
          for (let obj of data.child) {
            obj.statu = data.statu == 1 ? 1 : 0;
            if (obj.child) {
              setChildState(obj);
            }
          }
        }

        function setParentState(data) {
          if (data.parent) {
            data.parent.statu = 1;
          }
          if (data.parent.parent) {
            setParentState(data.parent);
          }
        }
      },
      selectPermissions(val){
        var self = this;
        for (let obj of self.roleLists) {
          if (obj.roleName == self.roleValue) {
            self.param.roleId = obj.roleId;
            break;
          }
        }
        for (let obj of self.navBars) {
          if (obj.modelName == self.activeBar) {
            self.param.modelId = obj.modelId;
            break;
          }
        }
        if (!self.param.roleId || !self.param.modelId) {
          self.vmMsgWarning('请选择角色或一级导航');
          return false;
        }
        self.activeBarNone = self.activeBar;
        req.ajaxSend('/school/User/getModelListGo', 'get', self.param, function (res) {
          self.permissionsList = res[0].child;
          setParent(self.permissionsList);
          function setParent(data) {
            for (let obj of data) {
              if (obj.child) {
                for (let mbj of obj.child) {
                  mbj.parent = obj;
                  if (mbj.child) {
                    setParent(obj.child);
                  }
                }
              }
            }
          }
        });
      },
      saveMsg(){
        var self = this;
        var param = {
          roleName: self.roleName
        };
        req.ajaxSend('/school/user/createRole', 'post', param, function (res) {
          if (res.statu == 1) {
            self.vmMsgSuccess('创建角色成功');
            req.ajaxSend('/school/User/getRoleList', 'post', {}, function (res) {
              self.roleLists = res;
            });
            self.dialogVisible = false;
          } else {
            self.vmMsgError(res.message);
          }
        });
      },
      savePermission(){
        var self = this,
          toData = {
            roleId: self.param.roleId,
            modelId: []
          }, ld;
        if (self.permissionsList.length == 0) {
          self.vmMsgWarning('没有权限可以设置！');
          return false;
        }
        ld = self.vmLoadingFull('保存中，请稍后。。。');
        getModelId(self.permissionsList);
        req.ajaxSend('/school/User/createRoleModelGo', 'post', toData, function (res) {
          ld.close();
          if (res.statu == 1) {
            self.vmMsgSuccess('权限设置成功');
          } else {
            self.vmMsgError(res.message);
          }
        });
        function getModelId(data) {
          for (let obj of data) {
            if (obj.statu == 1) {
              toData.modelId.push(obj.modelId);
            }
            if (obj.statu == 1 && obj.child) {
              getModelId(obj.child);
            }
          }
        }
      },
      handleClose(done) {
        this.roleName = '';
        done();
      }
    }
  }
</script>
<style>
  .permissionsBars_list {
    margin-top: 2.25rem;
    min-height: 50rem;
    padding: 0 100px;
  }

  .permissionsBars_list .rootNode {
    text-align: center;
    padding-bottom: 2rem;
    border-bottom: 1px solid #d2d2d2;
  }

  .permissionsBars_list .rootNode .el-button {
    position: relative;
  }

  .permissionsBars_list .rootNode .el-button:before {
    position: absolute;
    content: '';
    display: block;
    height: 2rem;
    border-right: 1px solid #d2d2d2;
    bottom: -2rem;
    left: 50%;
  }

  .permissionsManagement {
    padding: 1.25rem 2rem 3rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    font-size: 14px;
  }

  .permissionsManagement h3 {
    font-size: 1.25rem;
    color: #4e4e4e;
  }

  .role {
    margin-top: 2rem;
  }

  .role .newRole_btn {
    text-align: right;
    padding-right: 2.25rem;
  }

  .newRole_btn .el-button {
    width: 7.5rem;
  }

  .permissionsManagement .d_line {
    margin: 1rem 0;
  }

  .permissionsManagement .el-dialog--tiny {
    width: 600px;
  }

  .permissionsManagement .roleName {
    padding: 0 80px;
  }

  .permissionsManagement .selectPermissionBtn {
    background-color: #12b5b0;
    border-color: #12b5b0;
    padding: 10px 30px;
    margin-left: 1.5rem;
  }
</style>
