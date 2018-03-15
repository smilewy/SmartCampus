<template>
  <div class="g-container">
    <header class="g-textHeader g-liOneRow">
      <div class="g-headerButtonGroup">
        <h2>素养考核方向</h2>
      </div>
      <el-button type="primary" @click="saveClick">保存</el-button>
    </header>
    <section>
      <header class="g-textHeader">
        <div class="g-liOneRow g-sa_header_search">
          <div class="gs-button alertsBtn">
            <el-button type="primary" @click="totalApproval"><i class="el-icon-plus"></i>添加考核方向</el-button>
          </div>
          <div class="gs-refresh g-fuzzyInput">
            <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入" @change="getLoadAjax"></el-input>
          </div>
        </div>
      </header>
      <section class="centerTable alertsList">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          class="g-NotHover g-fixedColumn" :data="classesTimeSetTable" @selection-change="handleSelectionChange" @sort-change="sortChange">
          <el-table-column label="素养考核方向">
            <template slot-scope="props">
              <input type="text" v-model="props.row.directionName" class="tableInput" />
            </template>
          </el-table-column>
          <el-table-column label="满分分数" prop="scoreAll"></el-table-column>
          <el-table-column label="操作" width="150" fixed="right">
            <template slot-scope="props">
              <el-button type="text" @click="handleClick(props.row.directionId)">编辑</el-button>
              <el-button @click="deleteClick(props.row.directionId)" :disabled="!props.row.state" :class="[{'deleteColor':props.row.state}]" type="text">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
      </section>
      <el-dialog class="g-tree_content g-defineDialog headerNotBackground" :title="dialogTitle" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
        <el-form :model="gradeDialogForm" ref="dialogForm" label-width="100px" label-position="right">
          <el-form-item label="素养考核方向:">
            <el-input v-model="gradeDialogForm.directionName" placeholder="请输入考核方向"></el-input>
          </el-form-item>
        </el-form>
        <div class="g-button">
          <el-button @click="confirmClick" type="primary">确定</el-button>
          <el-button @click="isDialog=false;">取消</el-button>
        </div>
      </el-dialog>
    </section>
  </div>
</template>
<script>
  import {
    literacyAssessLoad,//加载
    literacyAssessAdd,//添加
    literacyAssessDel,//删除
    literacyAssessUpdateName,//修改素养考核方向
  } from '@/api/http'
  export default{
    data(){
      return{
        isLoading:false,
        returnD:'',
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*table*/
        classesTimeSetTable:[],
        /*弹框*/
        isDialog:false,
        dialogTitle:'添加考核方向',
        gradeDialogForm:{
          directionName:''
        },
        /*tree*/
        treeData:[],
        defaultProps: {
          children: 'data',
          label:'techerName',
        },
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*弹框*/
      handlerClose(done){
        done();
      },
      confirmClick(){
        if(this.gradeDialogForm.directionName){
          this.addAjax();
        }
        else{
          this.vmMsgWarning( '请输入素养考核方向' );
        }
      },
      /*table*/
      fuzzyTableClick(){},
      handleSelectionChange(val){
      },
      sortChange(column,prop,order){},
      handleClick(directionId){
        this.$router.push({name:'handleLiteracyAssess',params:{id:directionId}});
      },
      /*批量审批*/
      totalApproval(){
        if(!this.returnD){
          this.vmMsgWarning( '不能添加考核方向' ); return;
        }
        this.isDialog=true;
        this.dialogTitle='创建考核方向';
      },
      /*send ajax*/
      getLoadAjax(){
          this.isLoading=true;
        literacyAssessLoad({find:this.fuzzyInput}).then(data=>{
          this.returnD=data.return;
          this.classesTimeSetTable=data.data;
          this.isLoading=false;
        })
      },
      deleteClick(directionId){
        this.$confirm('确定删除该考核方向吗？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
          literacyAssessDel({directionId:directionId}).then(data=>{
            this.simplePrompt(data,'删除成功！','删除失败，请重试！');
          });
        }).catch(()=>{});
      },
      /*添加考核方向*/
      addAjax(){
        this.isDialog=false;
        literacyAssessAdd(this.gradeDialogForm).then((data)=>{
          this.simplePrompt(data,'添加成功！','添加失败！');
        })
      },
      /*保存*/
      saveClick(){
        literacyAssessUpdateName({data:this.classesTimeSetTable}).then(data=>{
          this.simplePrompt(data,'保存成功！','保存失败！');
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
      this.getLoadAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/test';
  @import '../../../../style/style';
  .g-container header.g-textHeader{padding-bottom:0;}
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  .headerNotBackground.g-tree_content{
    .g-sectionR{.width(585,940);margin:0;}
    .g-sectionL{.width(330,940);.NotLineheight(541);}
  }

  /*弹框*/
  .g-tree_content h2{text-align:center;.fontSize(12);color:@HColor;padding-bottom:20/16rem;}
  .g-tree_content .el-dialog--tiny{.NotLineheight(560);}
  /*容器*/
  .gs-button button{
    i{.fontSize(14);margin-right:10/16rem;}
  }
  .g-tree_content{
    margin-top: 0;
  }
  .g-tree_content .g-flexStartRow>div:not(:first-of-type){margin-left:2rem;}
  .g-dialogOther{padding-left:15/16rem;padding-bottom:20/16rem;}
</style>


