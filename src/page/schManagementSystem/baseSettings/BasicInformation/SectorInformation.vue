<template>
  <div class="SectorInformation">
    <el-col :span="24">
      <el-col :span="22">
        <h3>部门信息</h3>
      </el-col>
      <el-col :span="2">
        <!--<el-button type="primary" class="CreateProcess-top-btn">保存</el-button>-->
      </el-col>
    </el-col>
    <el-row class="SectorInformation-top">
      <el-col :span="24">
        <el-button @click="createDepart('root')">
          <img class="icon-unactive" src="./../../../../assets/img/schManagementSystem/baseSettings/BasicInformaton/icon_build.png" alt="">
          <img class="icon-active" src="./../../../../assets/img/schManagementSystem/baseSettings/BasicInformaton/icon_build_highlight.png" alt="">
          <span>创建部门</span>
        </el-button>
        <el-button @click="createDepart('sub')">
          <img class="icon-unactive" src="./../../../../assets/img/schManagementSystem/baseSettings/BasicInformaton/icon_build_son.png" alt="">
          <img class="icon-active" src="./../../../../assets/img/schManagementSystem/baseSettings/BasicInformaton/icon_build_son_highlight.png" alt="">
          <span>创建子部门</span>
        </el-button>
        <el-button @click="editDepart()">
          <img class="icon-unactive" src="./../../../../assets/img/schManagementSystem/baseSettings/BasicInformaton/icon_bianji.png" alt="">
          <img class="icon-active" src="./../../../../assets/img/schManagementSystem/baseSettings/BasicInformaton/icon_bianji_highlight.png" alt="">
          <span>编辑</span>
        </el-button>
        <el-button @click="deleteDepart(cur_depart)">
          <img class="icon-unactive" src="./../../../../assets/img/schManagementSystem/baseSettings/BasicInformaton/icon_del.png" alt="">
          <img class="icon-active" src="./../../../../assets/img/schManagementSystem/baseSettings/BasicInformaton/icon_del_highlight.png" alt="">
          <span>删除</span>
        </el-button>
      </el-col>
    </el-row>
    <el-row class="permissionsBars_list">
      <div class="rootNode">
        <el-button>{{schoolName}}</el-button>
      </div>
      <depart-list :data.sync="departList" :isChild="false" @select="selectDepart" @change="changeName"/>
    </el-row>
    <el-dialog :title="cur_depart.departmentId?'创建子部门':'创建部门'" v-if="show_create_depart" :modal="false" :visible.sync="show_create_depart">
      <el-row>
        <el-col style="min-height:13rem;">
          <el-col :span="24">
            <el-col :span="5" :offset="2" style="padding-top: .4rem;"><span style="color: red">*</span> 部门名称：</el-col>
            <el-col :span="15">
              <el-input placeholder="请输入" v-model="departmentName"></el-input>
            </el-col>
          </el-col>
          <el-col :span="2" :offset="11">
            <el-button type="primary" class="Field-save" @click="saveAddDepart(cur_depart)">保存</el-button>
          </el-col>
        </el-col>
      </el-row>
    </el-dialog>
    <el-dialog title="编辑部门" v-if="show_edit_depart" :modal="false" :visible.sync="show_edit_depart">
      <el-row>
        <el-col style="min-height:13rem;">
          <el-col :span="24">
            <el-col :span="5" :offset="2" style="padding-top: .4rem;">部门名称：</el-col>
            <el-col :span="15">
              <el-input placeholder="请输入" v-model="departmentName"></el-input>
            </el-col>
          </el-col>
          <el-col :span="2" :offset="11">
            <el-button type="primary" class="Field-save" @click="saveEditDepart(cur_depart)">保存</el-button>
          </el-col>
        </el-col>
      </el-row>
    </el-dialog>
  </div>
</template>
<script>
  import req from '@/assets/js/common'
  import departList from './SectorInformationList.vue'
  export default{
    data(){
      return {
        schoolName:'',
        departList: [],
        init_departList: [],
        cur_depart:{},
        show_create_depart:false,
        show_edit_depart:false,
        departmentName:'',
        openMap:{}
      }
    },
    created(){
        this.getList(true);
        this.GetSchoolName();
    },
    methods: {
      deleteDepart(depart){
        if(!depart.departmentId){
          this.vmMsgWarning('请选择部门');
          return;
        }
        if(depart.child){
          this.vmMsgWarning('该部门包含子部门，不能删除');
          return;
        }
        this.$confirm('是否确定删除该部门?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/Bcinformation/department/type/departmentdel', 'post',{
            departmentId:depart.departmentId
          }, (res)=> {
            if(res.return===true){
              this.vmMsgSuccess('删除成功');
            }
            if(res.return===false){
              this.vmMsgError('删除失败');
            }
            this.getList();
          })
        }).catch(() => {

        });
      },
      editDepart(){
        if(!this.cur_depart.departmentId){
          this.vmMsgWarning('请选择部门');
          return;
        }
        let cur_depart = this.cur_depart;
        function initData(data) {
          data.forEach(val=>{
            val.isEdit=val.departmentId===cur_depart.departmentId;
            if(val.child){
              val.child = initData(val.child)
            }
          });
          return data;
        }
        this.departList = initData(this.departList);
      },
      changeName(depart){
        if(!depart.departmentName){
          this.vmMsgWarning('请填写部门名称');
           this.getList();
           return;
        }
        req.ajaxSend('/school/Bcinformation/department/type/departmentupdate', 'post',{
          departmentName:depart.departmentName,
          departmentId:depart.departmentId
        }, (res)=> {
          if(res.return===true){
            this.vmMsgSuccess('修改成功');
            this.show_edit_depart=false;
          }
          if(res.return===false){
            this.vmMsgError('修改失败');
          }
          if(res.return==='error'){
            this.vmMsgWarning('部门名称不能重复');
          }
          this.getList();
        });
      },
      isRepeatName(tree,name){
        var repeat = false;
        tree.forEach(val=>{
          if(val.departmentName===name){
            repeat = true;
          }
          if(val.child){
            this.isRepeatName(val.child,name)
          }
        });
        return repeat;
      },
      saveEditDepart(depart){
        req.ajaxSend('/school/Bcinformation/department/type/departmentupdate', 'post',{
          departmentName:this.departmentName,
          departmentId:depart.departmentId
        }, (res)=> {
          if(res.return===true){
            this.vmMsgSuccess('修改成功');
            this.show_edit_depart=false;
          }
          if(res.return===false){
            this.vmMsgError('修改失败');
          }
          if(res.return==='error'){
            this.vmMsgError('部门名称不能重复');
          }
          this.getList();
        });
      },
      createDepart(type){
        if(type==='root'){
          this.cur_depart = {};
        }else{
          if(!this.cur_depart.departmentId){
            this.vmMsgWarning('请选择部门');
            return;
          }
        }
        this.departmentName = '';
        this.show_create_depart=true;
      },
      saveAddDepart(depart){
        if(!this.departmentName){
          this.vmMsgWarning('请填写部门名称');
          return;
        }
        let param = {
          departmentName:this.departmentName
        };
        if(depart.departmentId){
          param.level = depart.level;
          param.parentId = depart.departmentId;
        }
        req.ajaxSend('/school/Bcinformation/department/type/departmentupdate', 'post',param, (res)=> {
          if(res.return===true){
            this.vmMsgSuccess('添加成功');
            this.show_create_depart=false;
            if(depart.departmentId){
              openEditNode(this.departList);
              function openEditNode(tree) {
                tree.forEach(val=>{
                  if(val.departmentId===depart.departmentId){
                    val.ifHave = true;
                    return;
                  }
                  if(val.child){
                    openEditNode(val.child)
                  }
                });
              }
            }
            this.getList();
          }
          if(res.return===false){
            this.vmMsgError('添加失败');
          }
          if(res.return==='error'){
            this.vmMsgError('部门名称不能重复');
          }
        });
      },
      getList(isClose){
        let vm = this;
        function initData(data) {
          data.forEach(val=>{
            val.ifHave=isClose?false:
              vm.openMap[val.departmentId]?vm.openMap[val.departmentId]==='r-true':false;
            val.isChecked=false;
            val.isEdit=false;
            if(val.child){
              val.child = initData(val.child)
            }
          });
          return data;
        }
        function getOpenMap(data) {
          data.forEach(val=>{
            vm.openMap[val.departmentId] = val.ifHave?'r-true':'r-false';
            if(val.child){
              getOpenMap(val.child)
            }
          });
        }
        getOpenMap(this.departList);
        req.ajaxSend('/school/Bcinformation/department/type/departmentselect', 'post',{}, (res)=> {
          this.init_departList = JSON.parse(JSON.stringify(res));
          this.departList = initData(res);
          this.cur_depart = {};
        });
      },
      GetSchoolName(){
        req.ajaxSend('/school/Bcinformation/schoolinformation/type/schoolfind','post',{},(res)=>{
          this.schoolName=res.scName;
        });
      },
      selectDepart(depart){
        this.cur_depart = JSON.parse(JSON.stringify(depart));
        function initData(data) {
          data.forEach(val=>{
            val.isChecked=val.departmentId===depart.departmentId;
            if(val.child){
              val.child = initData(val.child)
            }
          });
          return data;
        }
        this.departList = initData(this.departList);
      }
    },
    components:{
      departList
    }
  }
</script>
<style lang="less" scoped>
  .SectorInformation{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
    min-height:50rem;
    .Field-save{
      margin-top: 4.6rem;
      padding: .5rem 2.1rem;
      border-radius: 1.1rem;
    }
  }
  .SectorInformation  .CreateProcess-top-btn{
    padding:.5rem 2.8rem ;
    border-radius: 1.1rem;
  }
  .SectorInformation-top{
    margin-top:2.5rem;
  }
  .SectorInformation-top .el-button{
    display: inline-block;
    margin-left:.8rem;
    font-size: 0.875rem;
    border: none;
  }
  .SectorInformation-top img{
    width: 10px;
    height: 10px;
  }
  .SectorInformation-top .el-button:hover .icon-active{
    display:inline-block;
  }
  .SectorInformation-top .el-button:hover .icon-unactive{
    display:none;
  }
  .SectorInformation-top .el-button .icon-active{
    display: none;
  }
  .SectorInformation-top .el-button:hover >span{
    color: #89BCF5;
  }

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

  .permissionsBars_list .rootNode .el-button{
    cursor: default;
    position: relative;
    color: #1f2d3d !important;
    border-color: #c4c4c4 !important;
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
</style>
