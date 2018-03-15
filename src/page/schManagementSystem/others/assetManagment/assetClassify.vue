<template>
  <div class="g-container">
    <header class="g-textHeader">
      <h2>资产分类</h2>
    </header>
    <section class="g-section">
      <treeTable 
          v-loading="loading" 
          element-loading-text="拼命加载中" 
          element-loading-spinner="el-icon-loading" 
          :columns="columns" 
          :dataSource="assetTypeTable" 
          :ParamObj="ParamObj" 
          isChecked 
          :handleButton="handleButton" 
          v-on:handleDialog="handleDialog">
      </treeTable>
    </section>
    <el-dialog class="headerNotBackground" :title="dialogTitle" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
      <el-form :model="gradeDialogForm" label-width="90px" label-position="right">
        <el-form-item label="分类名称:" required>
          <el-input v-model="gradeDialogForm.name" placeholder="请输入分类名称"></el-input>
        </el-form-item>
      </el-form>
      <div class="g-button">
        <el-button type="primary" @click="addClassity">确定</el-button>
        <el-button @click="cancelDialog">取消</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    assetClassifyLoad,//初始加载数据
    assetClassifyDelete,//删除
    assetClassifyCreate,//新增
    assetClassifyChange,//修改
    assetClassifyUsed,//使用该分类
  } from '@/api/http'
  import {handlerAjaxData} from '@/assets/js/common'
  import treeTable from '../../../../components/treeTable/treeTable.vue'
  export default{
    data(){
      return{
        /*treeTable组件----------*/
        /*table组件*/
        /*button*/
        handleButton:{
          /*操作button*/
          parentHandle:[{name:'新增',msg:'add'},{name:'编辑',msg:'handle'},{name:'删除',msg:'delete',cls:'deleteColor'}],
          childHandle:[{name:'新增',msg:'add'},{name:'删除',msg:'delete',cls:'deleteColor'}],
        },
        /*列循环字段*/
        columns:[
          /*props为列绑定数据*/
            {name:'资产分类',props:'assetsTypeName'},{name:'分类代码',props:'assetsTypeId'}
          ],
        /*行循环字段*/
        assetTypeTable:[],
        /*所有请求需要的参数*/
        ParamObj:['assetsTypeName','assetsTypeId'],
        /*-----------------------------*/
        /*弹框*/
        isDialog:false,
        dialogTitle:'新增分类',
        gradeDialogForm:{
          name:''
        },
        /*send ajax*/
        ajaxParams:null,
        ajaxType:'',
        loading: false
      }
    },
    computed: {},
    components:{treeTable},
    methods:{
      /*table*/
      /*handleDialog是子组件所有需要发送请求的事件触发*/
      handleDialog(msg,ajaxParams){
        /*ajaxParams为请求所需参数对象，参数名:值*/
        this.ajaxParams=ajaxParams;
        if(msg=='add'){
          this.isDialog=true;
          this.dialogTitle='新增分类';
          this.ajaxType='add';
          this.gradeDialogForm.name='';
        }
        else if(msg=='handle'){
          this.isDialog=true;
          this.dialogTitle='编辑分类';
          this.gradeDialogForm.name=this.ajaxParams.assetsTypeName;
          this.ajaxType='handle';
        }
        else if(msg=='delete'){
          this.$confirm('是否删除该分类？','提示',{
            confirmButtonText:'确定',
            cancelButtonText:'取消',
            type:'warning'
          }).then(()=>{
            this.deleteAjax();
          }).catch(()=>{});
        }
        else if(msg=='isUsed'){
          this.usesAjax();
        }
      },
      /*弹框*/
      handlerClose(done){
        this.isDialog=false;
        done();
      },
      /*保存*/
      addClassity(){
        if(this.gradeDialogForm.name){
          if(this.gradeDialogForm.name.length > 20){
            this.vmMsgWarning( '分类名称长度不得超过20字符！' ); return;
          }
          if(this.ajaxType=='add'){
            this.addTypeAjax();
          }
          else if(this.ajaxType=='handle'){
            this.handlerTypeAjax();
          }
        }
        else{
          this.vmMsgWarning( '请填写分类名称！' );
        }
      },
      cancelDialog(){
        this.isDialog=false;
      },
      /*send ajax------*/
      sendLoadAjax(){
        this.loading = true;
        assetClassifyLoad().then(data=>{
          this.loading = false; 
          if(data.statu){
            this.assetTypeTable=handlerAjaxData(data);
          } else {
            this.vmMsgError( data.message );
          }
        });
      },


      getClassById( assetsTypeId, arr, operType, newVal ){
         if( !Array.isArray(arr) ){ throw("必须是数组"); }

         let type = arr.find( o => o.assetsTypeId == assetsTypeId );
         if( !type ){
           arr.forEach( o => {
             if( 'childs' in o ){
               this.getClassById( assetsTypeId, o.childs, operType, newVal );
             }
           });
         } else {
           if ( operType == "delete" ){
              arr.splice( arr.indexOf( type ), 1);
           } else if ( operType == "edit" ){
              type.assetsTypeName = newVal;
           } else if ( operType == "add" ){
             if(!type.childs) {
                this.$set( type, "childs", []);
             } 
             type.childs.splice( type.childs.length, 0, newVal);
           }
         }
      },

      /*删除分类*/
      deleteAjax(){
        assetClassifyDelete({assetsTypeId:this.ajaxParams.assetsTypeId}).then(data=>{
         
          if(data.statu==1){
            this.getClassById( this.ajaxParams.assetsTypeId, this.assetTypeTable, 'delete' );
            this.vmMsgSuccess( '删除成功！' );
            //this.sendLoadAjax();
          } else {
            this.vmMsgError( data.message );
          }
        });
      },
      /*新增分类*/
      addTypeAjax(){
        assetClassifyCreate({assetsTypeId:this.ajaxParams.assetsTypeId,assetsTypeName:this.gradeDialogForm.name}).then(data=>{
          this.isDialog=false;
          if(data.statu){
            let obj = {
              approver: null,
              assetsTypeId: data.data.assetsTypeId,
              assetsTypeName: data.data.assetsTypeName,
              assetsTypeParentId: data.data.assetsTypeParentId,
              createTime: data.data.createTime,
              ifApprov: "0",
              ifUser: false,
              leavel: data.data.leavel,
              scId: data.data.scId
            };

            this.getClassById( this.ajaxParams.assetsTypeId, this.assetTypeTable, 'add', obj );
            this.vmMsgSuccess( '增加成功！' );
            //this.sendLoadAjax();
          } else {
            this.vmMsgError( data.message );
          }
        });
      },
      handlerTypeAjax(){
        assetClassifyChange({assetsTypeId:this.ajaxParams.assetsTypeId,assetsTypeName:this.gradeDialogForm.name}).then(data=>{
          this.isDialog=false;
          if(data.statu){
            this.getClassById( this.ajaxParams.assetsTypeId, this.assetTypeTable, 'edit', this.gradeDialogForm.name );
            this.vmMsgSuccess( '修改成功！' );
            //this.sendLoadAjax();
          } else {
            this.vmMsgError( data.message );
          }
        });
      },
      /*使用该分类*/
      usesAjax(){
        assetClassifyUsed({assetsTypeId:this.ajaxParams.assetsTypeId}).then(data=>{
          if(data.statu){
            this.vmMsgSuccess( '设置使用成功！' );
          } else {
            this.vmMsgError( data.message );
          }
        });
      },
    },
    created(){
      this.sendLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
</style>




