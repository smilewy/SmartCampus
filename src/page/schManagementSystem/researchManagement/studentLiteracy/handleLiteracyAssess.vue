<template>
  <div class="g-container">
    <header class="g-textHeader g-liOneRow">
      <div class="g-flexStartRow">
        <el-button @click="goBackParent" class="g-gobackChart g-imgContainer RedButton">
          <img src="../../../../assets/img/commonImg/icon_return.png" />
          返回
        </el-button>
        <span class="selfCenter">考核方向</span>
      </div>
      <div>
        <el-button :disabled="buttonState" type="primary" @click="addProject"><i class="el-icon-plus"></i>添加考核项目</el-button>
      </div>
    </header>
    <section>
      <header class="g-textHeader g-flexStartRow">
<!--        <div class="g-contentRow">
          <span class="selfCenter g-NotMarginLeft" style="margin-right:1.25rem;">考核方向名称:</span>
          <el-input v-model="direction" style="width:10rem;"></el-input>
        </div>-->
        <div class="g-contentRow selfCenter">
          <span class="selfCenter notPadding" style="margin-right:1.25rem;">满分分值:</span>
          <span class="g-NotMarginLeft" v-text="scoreAll"></span>
        </div>
      </header>
      <treeTable1 :expand="true" :columns="columns" :dataSource="assetTypeTable" :buttonState="buttonState" :ParamObj="ParamObj" :handleButton="handleButton" v-on:handleDialog="handleDialog"></treeTable1>
    </section>
    <el-dialog class="g-tree_content g-defineDialog headerNotBackground" :title="dialogTitle" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
      <el-form :model="gradeDialogForm" ref="dialogForm" label-width="100px" label-position="right">
        <el-form-item label="考核项目:">
          <el-input v-model="gradeDialogForm.projectNmae" placeholder="请输入考核项目"></el-input>
        </el-form-item>
      </el-form>
      <div class="g-button">
        <el-button @click="confirmAddProject()" type="primary">确定</el-button>
        <el-button @click="isDialog=false;">取消</el-button>
      </div>
    </el-dialog>
    <el-dialog class="g-tree_content g-defineDialog headerNotBackground" title="添加子项目" :modal="false" :visible.sync="isChildDialog" :before-close="handlerChildClose">
      <el-form :model="ChildDialogForm" ref="dialogForm" label-width="100px" label-position="right">
        <el-form-item label="考核子项目:">
          <el-input v-model="ChildDialogForm.projectNmae" placeholder="请输入考核子项目"></el-input>
        </el-form-item>
      </el-form>
    <div class="g-button">
      <el-button @click="confirmAddChildPro" type="primary">确定</el-button>
      <el-button @click="isChildDialog=false">取消</el-button>
    </div>
  </el-dialog>
    <el-dialog class="g-tree_content g-defineDialog headerNotBackground" :title="SpecificDialogTitle" :modal="false" :visible.sync="isSpecificDialog" :before-close="handlerSpecificClose">
      <el-form :model="specificDialogForm" ref="dialogForm" label-width="100px" label-position="right">
        <el-form-item label="考核条例:">
          <el-input v-model="specificDialogForm.projectNmae" placeholder="请输入考核条例"></el-input>
        </el-form-item>
        <el-form-item label="分值:">
          <el-input v-model="specificDialogForm.scoreAll" placeholder="请输入考核条例"></el-input>
        </el-form-item>
      </el-form>
      <div class="g-button">
        <el-button @click="confirmAddSpecialPro" type="primary">确定</el-button>
        <el-button @click="isSpecificDialog=false">取消</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    literacyAssessUpdateLoad,//加载
    literacyAssessAddProject,//添加条例
    literacyAssessUpdateDel,//删除条例
    literacyAssessUpdateProject,//修改条例
  } from '@/api/http'
  import treeTable1 from '../../../../components/treeTable/treeTable1.vue'
  export default{
    data(){
      return{
        /*form表单*/
        dataForm:{},
        /*考核方向名称*/
        direction:'',
        /*弹框——添加考核项目*/
        isDialog:false,
        dialogTitle:'添加考核项目',
        gradeDialogForm:{
          projectNmae:'',
        },
        /*弹框——添加子项目*/
        isChildDialog:false,
        ChildDialogForm:{
          projectNmae:'',
        },
        /*弹框——添加具体条例*/
        isSpecificDialog:false,
        specificDialogForm:{
          projectNmae:'',
          scoreAll:50
        },
        SpecificDialogTitle:'添加具体条例',
        /*table组件*/
        /*button区*/
        handleButton:{
          /*操作button*/
          parentHandle:[
            {name:'添加子项目',msg:'add'},
            {name:'添加具体条例',msg:'addSpecific'},
            {name:'编辑',msg:'handle'},
            {name:'删除',msg:'delete',cls:'deleteColor'}
          ],
          childHandle:[
            {name:'编辑',msg:'handle'},
            {name:'删除',msg:'delete',cls:'deleteColor'}
          ],
        },
        /*表列名*/
        columns:[
          /*props为列绑定数据*/
          {name:'考核项目',props:'projectNmae'},
          {name:'具体条例',props:'projectNmaeRules'},
          {name:'分值',props:'scoreAll'}
        ],
        /*button是否禁用*/
        buttonState:false,
        /*data数据*/
        assetTypeTable:[
          /*tree数据*/
          {type:'是否有',code:'1打断点',
            /*isSpecific判断哪条数据为具体条例，因为button不同*/
            childs:[{type:'电脑',code:'1-1',childs:[{type:'',code:'1-1-1',isSpecific:true}]},
              {type:'dfa',code:'1-1'}
            ],
          },
          {type:'no项',code:'淡淡的'}
        ],
        /*send ajax need params*/
        ParamObj:['projectId','level','isParent','projectNmae','projectNmaeRules','scoreAll'],
        /*满分分值*/
        scoreAll:'',
        /*send ajax param*/
        directionId:'',
        /*发送请求类型add、handler*/
        ajaxType:'',
        _params:null,
      }
    },
    components:{treeTable1},
    methods:{
      goBackParent(){
        this.$router.push('/literacyAssess');
      },
      /*弹框——添加考核项目与编辑考核项目为一个弹框，添加子项目为一个弹框
      * 添加具体条例与编辑具体条例为一个弹框*/
      /*弹框——添加考核项目*/
      addProject(){
        this.isDialog=true;
        this.gradeDialogForm.projectNmae='';
        this.dialogTitle='添加考核项目';
      },
      /*添加考核项目*/
      handlerClose(done){
        done();
      },
      /*弹框——添加子项目*/
      handlerChildClose(done){
        done();
      },
      /*弹框——添加具体条例*/
      handlerSpecificClose(done){
        done();
      },
      /*table——组件*/
      handleDialog(msg,params){
        this._params=params;
        this.ajaxType=msg;
        if(msg=='add'){
          this.isChildDialog=true;
          this.ChildDialogForm.projectNmae='';
          this.DialogChildTitle='添加子项目';
        }
        else if(msg=='addSpecific'){
          this.isSpecificDialog=true;
          this.specificDialogForm.projectNmae='';
          this.specificDialogForm.scoreAll='';
          this.SpecificDialogTitle='添加具体条例';
        }
        else if(msg=='handle'){
          if(Number(this._params.isParent)){
            /*值为1代表为项目，即有子级*/
            this.isDialog=true;
            this.dialogTitle='编辑';
            this.gradeDialogForm.projectNmae=this._params.projectNmae;
          }
          else{
            this.isSpecificDialog=true;
            this.SpecificDialogTitle='编辑具体条例';
            this.specificDialogForm.projectNmae=this._params.projectNmaeRules;
            this.specificDialogForm.scoreAll=this._params.scoreAll;
          }
        }
        else if(msg=='delete'){
          if(Number(this._params.isParent)){
            /*值为1代表为项目，即有子级*/
            this.$confirm('请检查是否有具体条例，确定删除该项目吗？','提示',{
              confirmButtonText:'确定',
              cancelButtonText:'取消',
              type:'warning'
            }).then(()=>{
              this.deleteProjectAjax();
            }).catch(()=>{});
          }
          else{
            this.$confirm('确定删除具体条例吗？','提示',{
              confirmButtonText:'确定',
              cancelButtonText:'取消',
              type:'warning'
            }).then(()=>{
              this.deleteProjectAjax();
            }).catch(()=>{});
          }
        }
      },
      /*send ajax*/
      getLoadAjax(){
        literacyAssessUpdateLoad({directionId:this.directionId}).then(data=>{
          this.scoreAll=data.scoreAll;
          this.buttonState=!data.state;
          this.assetTypeTable=data.data;
          if(this.buttonState){
            /*button禁用*/
            this.handleButton={
              /*操作button*/
              parentHandle:[
                {name:'添加子项目',msg:'add'},
                {name:'添加具体条例',msg:'addSpecific'},
                {name:'编辑',msg:'handle'},
                {name:'删除',msg:'delete'}
              ],
                childHandle:[
                {name:'编辑',msg:'handle'},
                {name:'删除',msg:'delete'}
              ],
            };
          }
          else{
            this.handleButton={
              /*操作button*/
              parentHandle:[
                {name:'添加子项目',msg:'add'},
                {name:'添加具体条例',msg:'addSpecific'},
                {name:'编辑',msg:'handle'},
                {name:'删除',msg:'delete',cls:'deleteColor'}
              ],
              childHandle:[
                {name:'编辑',msg:'handle'},
                {name:'删除',msg:'delete',cls:'deleteColor'}
              ],
            };
          }
        });
      },
      /*添加考核项目*/
      confirmAddProject(){
        if(this.gradeDialogForm.projectNmae){
          if(this.ajaxType=='handle'){
            /*编辑*/
            literacyAssessUpdateProject({projectId:this._params.projectId,...this.gradeDialogForm}).then(data=>{
              this.simplePrompt(data,'编辑成功！','编辑失败！');
            });
          } else{
            literacyAssessAddProject({directionId:this.directionId,isParent:1,...this.gradeDialogForm}).then(data=>{
              this.simplePrompt(data,'添加成功！','添加失败！');
            });
          }
          this.isDialog=false;
        }
        else{
          this.vmMsgWarning( '请输入考核项目！' );
        }
      },
      /*添加子项目*/
      confirmAddChildPro(){
        if(this.ChildDialogForm.projectNmae){
          this.isChildDialog=false;
          literacyAssessAddProject({directionId:this.directionId,isParent:1,pid:this._params.projectId,level:this._params.level,...this.ChildDialogForm}).then(data=>{
            this.simplePrompt(data,'添加成功！','添加失败！');
          });
        }
        else{
          this.vmMsgWarning('请输入考核项目！');
        }
      },
      /*添加具体条例*/
      confirmAddSpecialPro(){
        if(this.specificDialogForm.projectNmae && this.specificDialogForm.scoreAll){
          if(this.specificDialogForm.scoreAll<0 || this.specificDialogForm.scoreAll>100 || isNaN(this.specificDialogForm.scoreAll)){
            this.vmMsgWarning('分值只能是0-100的数值！');
          }
          else{
            this.isSpecificDialog=false;
            if(this.ajaxType=='handle'){
              /*编辑*/
              literacyAssessUpdateProject({projectId:this._params.projectId,...this.specificDialogForm}).then(data=>{
                this.simplePrompt(data,'编辑成功！','编辑失败！');
              });
            }
            else{
              /*添加*/
              literacyAssessAddProject({directionId:this.directionId,isParent:0,pid:this._params.projectId,level:this._params.level,...this.specificDialogForm}).then(data=>{
                this.simplePrompt(data,'添加成功！','添加失败！');
              });
            }
          }
        }
        else{
          this.vmMsgWarning('请输入考核条例和分值！');
        }
      },
      /*删除项目*/
      deleteProjectAjax(){
        literacyAssessUpdateDel({directionId:this.directionId,projectId:this._params.projectId}).then(data=>{
          this.simplePrompt(data,'删除成功！','删除失败！');
        });
      },
      simplePrompt(data,suceessmsg,errMsg){
        if(data.return){
          this.vmMsgSuccess( suceessmsg );
          this.getLoadAjax();
        }
        else{
          this.vmMsgError( errMsg );
        }
      },
    },
    created(){
      this.directionId=this.$route.params.id;
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/test';
  @import '../../../../style/style';
  .g-textHeader{border:none;padding-bottom:70/16rem;
    span{.fontSize(19);color:@HColor;margin-left:40/16rem;}
  }
  .g-tree_content{
    margin-top: 0;
  }
  .g-textHeader span.notPadding{margin-left:0;.fontSize(19);}
  i{.fontSize(14);margin-right:10/16rem;}
  .g-container .g-textHeader .g-NotMarginLeft{margin-left:0;.fontSize(19);}
</style>




