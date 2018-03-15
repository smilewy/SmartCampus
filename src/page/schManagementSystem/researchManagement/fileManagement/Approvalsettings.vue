<template>
  <div class="Approvalsettings">
    <el-col :span="24">
      <el-col :span="22">
        <h3>审批设置</h3>
      </el-col>
    </el-col>
    <el-col :span="24">
      <el-col :span="17" class="Appset-left">
        <div class="Appset-left-title">审批人</div>
        <el-tag
          :key="tag.id"
          v-for="tag in dynamicTags"
          :closable="true"
          :close-transition="false"
          @close="handleClose(tag)">
          {{tag.label}}
        </el-tag>
        <el-col :span="24" style="margin-top:32rem;">
          <el-col :span="4" :offset="16">
            <el-button type="danger" class="CreateProcess-top-btn" @click="resetTags()">清空</el-button>
          </el-col>
          <el-col :span="4">
            <el-button type="primary" class="CreateProcess-top-btn"  @click="saveTags()">保存</el-button>
          </el-col>
        </el-col>
      </el-col>
      <el-col :span="6" :offset="1" class="Appset-right">
        <div class="Appset-right-title">待选审批人</div>
        <el-col :span="22" :offset="1" class="Appset-right-input1">
          <el-input
            placeholder="请输入查询关键字"
            v-model="filterText"
            @change="handleIconClick">
          </el-input>
        </el-col>
        <div class="Appset-right-border"></div>
        <el-col :span="24">
          <el-tree
            v-loading.body="isLoading"
            element-loading-text="拼命加载中..."
            class="filter-tree"
            :data="approveData"
            :props="defaultProps"
            default-expand-all
            @node-click="handleNodeClick"
            :filter-node-method="filterNode"
            ref="tree2">
          </el-tree>
        </el-col>
      </el-col>
    </el-col>
  </div>
</template>
<script>
  import req from './../../../../assets/js/common'
  export default{
    data(){
      return{
        isLoading:false,
        filterText: '',
        approveData:[],
        defaultProps: {
          children: 'children',
          label: 'label'
        },
        dynamicTags: [],
      }
    },
    created(){
      this.getApproveList();
        req.ajaxSend('/school/FileManage/approveSetting','post',{},(res)=>{
          this.dynamicTags = [];
          if(res.data){
            res.data.approver.forEach((val,idx)=>{
              if(!val)return;
              let tag = {
                id:res.data.approveId[idx],
                label:val
              };
              this.dynamicTags.push(tag);
            })
          }
        })
    },
    methods:{
      getApproveList(){
        this.isLoading=true;
        function suit2tree(data){
          var tree = [],
            final = [];
          if(isArray(data)){
            tree = data;
          }else{
            tree = obj2arr(data);
          }
          tree.forEach(function(val){
            var object = {};
            val.label = val.name || val.custom_id;
            if(val.id || val.custom_id)
              object.id = val.id || val.custom_id;
            if(val.custom_id)
              delete val.custom_id;
            if(val.name)
              delete val.name;
            val.children = deepCopy(val);
            if(val.label&&val.id){
              delete val.children;
            }else{
              val.children = suit2tree(val.children);
              object.children = val.children;
            }
            object.label = val.label;
            if(object.label&&object.label!=='children')
              final.push(object);
          });
          return final;
        }
        function obj2arr(data,key_name){
          if(!isArray(data)){
            var arr = [];
            for(var key in data){
              var object = data[key];
              if(typeof object === 'object'){
                object[key_name||'custom_id'] = key;
                arr.push(object);
              }
            }
            return arr;
          }else{
            return data;
          }
        }
        function isArray(data){
          return data&&!!data.join;
        }
        function deepCopy(data){
          return JSON.parse(JSON.stringify(data));
        }
        req.ajaxSend('/school/FileManage/common','post',{func:'getApprover'},(res)=>{
          this.approveData=suit2tree(res);
          this.isLoading=false;
        });
      },
      resetTags(){
        let param={
          type:'save',
          approver:[]
        };
        this.$confirm('是否确定清空审批人?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/FileManage/approveSetting','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess( res.msg );
              this.dynamicTags=[];
            }else {
              this.vmMsgError( res.msg );
            }
          });
        }).catch(() => {

        });
      },
      saveTags(){
        let param={
          type:'save',
          approver:this.dynamicTags.map(val=>({
            id:val.id,
            name:val.label
          }))
        };
        this.$confirm('是否确定保存该设置?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            req.ajaxSend('/school/FileManage/approveSetting','post',param,(res)=>{
              if(res.status===1){
                this.vmMsgSuccess( res.msg );
              }else {
                this.vmMsgError( res.msg );
              }
            });
          }).catch(() => {

          });
      },
      handleNodeClick(data) {
        if(data.children)return;
        if(this.dynamicTags.some(val=>val.id===data.id)){
          this.vmMsgWarning( '该审批人已存在！' ); return;
        }
        this.dynamicTags.push(data);
      },
      filterNode(value, data) {
        if (!value) return true;
        return data.label.indexOf(value) !== -1;
      },
      handleIconClick() {},
      handleClose(tag) {
        this.dynamicTags.splice(this.dynamicTags.indexOf(tag), 1);
      },
  },
  watch: {
      filterText(val) {
        this.$refs.tree2.filter(val);
      }
    },
  }
</script>
<style lang="less" scoped>
  .Approvalsettings{
    padding: 1.25rem 2rem;
    box-shadow: 0 0.1875rem 0.375rem 0.125rem rgba(0, 0, 0, 0.2);
    border-radius: .5rem;
    margin: 1.25rem 0;
    background-color: #fff;
  }
  .Approvalsettings  .CreateProcess-top-btn{
    padding:.5rem 2.8rem ;
    border-radius: 1.1rem;
    cursor: pointer;
  }
  .Appset-left,.Appset-right{
    border: 1px solid #d2d2d2;
    height:43.5rem;
    margin-top: 2rem;
    border-radius: .4rem;
    overflow-y: auto;
    margin-bottom: 1rem;
    box-shadow: 0 0.1rem 0.1rem 0.12rem rgba(0, 0, 0, 0.09) inset;
  }
  .Appset-left-title{
    font-size: 1.1rem;
    padding: 1rem 0 1rem 1rem;
    margin-bottom:1.8rem;
    border-bottom: 1px solid #d2d2d2;
  }
  .Appset-right-title{
    padding: .8rem 0 .8rem .8rem;
    font-weight: bold;
    font-size: 0.95rem;
  }
  .Approvalsettings .Appset-right-input1 .el-input__inner{
    height:28/16rem;
    border-radius:.8rem ;
  }
  .Appset-right-border{
    border: 1px solid #d2d2d2;
    margin-top:2.9rem;
  }
  .Approvalsettings .el-tree{
    border: none;
    margin-bottom:3rem;
  }
  .Approvalsettings .Appset-right-input .el-input__inner{
    height:36/16rem;
    border-radius:1.1rem ;
    border-color: #4da1ff;
  }
</style>
<style>
  .Approvalsettings .el-tag{
    background-color:#F08BC5 ;
    margin-left: 1rem;
    margin-top: 1rem;
    color: #ffffff;
  }
  .Approvalsettings .el-tag .el-icon-close{
    color: #ffffff;
  }
</style>
