<template>
  <div class="g-container">
    <header class="g-textHeader">
      <div class="g-liOneRow g-sa_header_search">
        <div class="gs-button alertsBtn">
          <el-button type="primary" @click="totalApproval">批量审批</el-button>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入需要查询的值" @change="fuzzyClick"></el-input>
        </div>
      </div>
    </header>
    <section class="centerTable alertsList">
      <el-table class="g-NotHover" 
                v-loading="loading" 
                element-loading-text="拼命加载中" 
                element-loading-spinner="el-icon-loading" 
                :data="classesTimeSetTable" 
                @selection-change="handleSelectionChange" 
                @sort-change="sortChange">
        <el-table-column type="selection"></el-table-column>
        <el-table-column label="序号" type="index" width="100px"></el-table-column>
        <el-table-column label="业务名称" prop="approveName" sortable="custom"></el-table-column>
        <el-table-column label="资产名称" prop="assetsName" sortable="custom"></el-table-column>
        <el-table-column label="资产编号" prop="assetsNumber" sortable="custom"></el-table-column>
        <el-table-column label="分类代码" prop="assetsTypeId" sortable="custom"></el-table-column>
        <el-table-column label="总价(元)" prop="allPrice" sortable="custom"></el-table-column>
        <el-table-column label="申请日期" prop="createTime" sortable="custom"></el-table-column>
        <el-table-column label="使用地址" prop="useAddress" sortable="custom"></el-table-column>
        <el-table-column label="负责人" prop="userName" sortable="custom"></el-table-column>
        <el-table-column label="说明" prop="explain" sortable="custom"></el-table-column>
        <el-table-column label="操作" width="150" fixed="right">
          <template slot-scope="props">
            <el-button type="text" @click="approvalClick(props.$index)">审批</el-button>
          </template>
        </el-table-column>
      </el-table>
    </section>
    <el-dialog class="g-tree_content spec" title="资产审批" :modal="false" :visible.sync="isDialog" :before-close="handlerClose">
      <div class="g-dialogConContainer">
        <h2 class="g-titleContent" v-text="">123</h2>
        <ul>
          <li class="g-dialogRow g-flexStartRow">
            <div>业务名称</div>
            <div v-text="detailForm.approveName"></div>
          </li>
          <li class="g-dialogRow g-flexStartRow">
            <div>资产名称</div>
            <div v-text="detailForm.assetsName"></div>
          </li>
          <li class="g-dialogRow g-flexStartRow">
            <div>资产编号</div>
            <div v-text="detailForm.assetsNumber"></div>
          </li>
          <li class="g-dialogRow g-flexStartRow">
            <div>分类代码</div>
            <div v-text="detailForm.assetsTypeId"></div>
          </li>
          <li class="g-dialogRow g-flexStartRow">
            <div>总价（元）</div>
            <div v-text="detailForm.allPrice"></div>
          </li>
          <li class="g-dialogRow g-flexStartRow">
            <div>业务日期</div>
            <div v-text="detailForm.businessTime"></div>
          </li>
          <li class="g-dialogRow g-flexStartRow">
            <div>申请日期</div>
            <div v-text="detailForm.createTime"></div>
          </li>
          <li class="g-dialogRow g-flexStartRow">
            <div>使用地址</div>
            <div v-text="detailForm.useAddress"></div>
          </li>
          <li class="g-dialogRow g-flexStartRow">
            <div>负责人</div>
            <div v-text="detailForm.userName"></div>
          </li>
          <li class="g-dialogRow g-flexStartRow">
            <div>说明</div>
            <div v-text="detailForm.explain"></div>
          </li>
        </ul>
        <div class="g-contentOne_header">我的审批</div>
        <div class="g-chooseButton g-flexStartRow">
          <span class="selfCenter">审批结果:</span>
          <div :class="[this.dialogParams.adpot?'activeCss':'normalCss']" data-msg="agree" @click="changeTypeClick">通过</div>
          <div :class="[!this.dialogParams.adpot?'activeCss':'normalCss']" data-msg="disagree" @click="changeTypeClick">不通过</div>
        </div>
        <div class="g-chooseButton g-flexStartRow">
          <span class="selfCenter">审批意见:</span>
          <el-input type="textarea" v-model="dialogParams.approveOpinion" :maxlength="100" style="width:50%;"></el-input>
        </div>
      </div>
      <div class="g-footer">
        <el-button class="radiusButton" @click="approvalAjax" type="primary">提交</el-button>
      </div>
    </el-dialog>
    <el-dialog class="g-tree_content" title="资产批量审批" :modal="false" :visible.sync="isTotalDialog" :before-close="handlerTotalClose">
      <div>
        <div class="g-contentOne_header">我的审批</div>
        <div class="g-chooseButton g-flexStartRow">
          <span class="selfCenter">审批结果:</span>
          <div :class="[this.dialogParams.adpot?'activeCss':'normalCss']" data-msg="agree" @click="changeTypeClick">通过</div>
          <div :class="[!this.dialogParams.adpot?'activeCss':'normalCss']" data-msg="disagree" @click="changeTypeClick">不通过</div>
        </div>
        <div class="g-chooseButton g-flexStartRow">
          <span class="selfCenter">审批意见:</span>
          <el-input type="textarea" v-model="dialogParams.approveOpinion" :maxlength="100" style="width:80%;"></el-input>
        </div>
      </div>
      <div class="g-footer">
        <el-button class="radiusButton" @click="approvalAjax" type="primary">提交</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    PendApprovalGetLoad,//页面加载信息
    PendApprovalGetSort,//排序
    PendApprovalGetSearch,//模糊查询
    PendApprovalHandle,//审批操作
  } from '@/api/http'
  import {handlerAjaxData} from '@/assets/js/common'
  import req from '@/assets/js/common'
  export default{
    data(){
      return{
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*table*/
        classesTimeSetTable:[],
        multipleSelect:[],
        /*弹框——单个审批*/
        isDialog:false,
        detailForm:{},
        /*弹框——批量审批*/
        isTotalDialog:false,
        dialogParams:{
          approveId:[],
          approveOpinion:'',
          adpot:true
        },
        /*tree*/
        treeData:[],
        defaultProps: {
          children: 'data',
          label:'techerName',
        },
        // 判断是否重复点击提交按钮
        isRepeatSubmit: false ,
        loading: false
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*弹框——单个审批*/
      handlerClose(done){
        done();
      },
      /*调整方式*/
      changeTypeClick(event){
        let e=$(event.currentTarget);
        if(e.data('msg')=='agree'){
          this.dialogParams.adpot=true;
        }else{
          this.dialogParams.adpot=false;
        }
      },
      /*弹框——批量审批*/
      handlerTotalClose(done){
        done();
      },
      /*table*/
      fuzzyTableClick(){},
      handleSelectionChange(val){
        this.multipleSelect=val;
      },
      approvalClick(index){
        this.isDialog=true;
        this.handlerParams(index);
      },
      /*发送请求修改参数*/
      handlerParams(value){
        this.dialogParams.approveId=[];
        if(value=='all'){
          this.classesTimeSetTable.forEach((val,index)=>{
            this.dialogParams.approveId.push(val.approveId);
          });
        }else{
          this.dialogParams.approveId.push(this.classesTimeSetTable[value].approveId);
          this.detailForm=this.classesTimeSetTable[value];
        }
      },
      /*批量审批*/
      totalApproval(){
        if(this.multipleSelect.length>0){
          this.isTotalDialog=true;
          this.handlerParams('all');
        }else{
          this.vmMsgWarning( '请选择审批信息！' );
        }
      },
      /*send ajax*/
      getLoadData(){
        this.loading = true;
        PendApprovalGetLoad().then(data=>{
          this.loading = false;
          this.classesTimeSetTable=handlerAjaxData(data);
        });
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
        this.loading = true;
        PendApprovalGetSearch({valueData:this.fuzzyInput}).then(data=>{
          this.loading = false;
          this.classesTimeSetTable=handlerAjaxData(data);
        });
      },
      sortChange(column,prop,order){
        this.loading = true;
        PendApprovalGetSort({sort:column.prop,sortData:column.order}).then(data=>{
          this.loading = false;
          this.classesTimeSetTable=handlerAjaxData(data);
        });
      },
      /*审批*/
      approvalAjax(){
        if(this.isRepeatSubmit) { this.vmMsgWarning( '请勿重复提交！' ); return; }
        this.isRepeatSubmit = true;
        PendApprovalHandle(this.dialogParams).then(data=>{
          this.isDialog=false;
          this.isTotalDialog=false;
          this.dialogParams={
            approveId:[],
            approveOpinion:'',
            adpot:true
          };
          if(data.statu){
            this.vmMsgSuccess( '审批成功！' );
            this.getLoadData();
          }else{
            this.vmMsgError( data.message );
          }
          this.isRepeatSubmit = false;
        });
      },
    },
    created(){
      this.getLoadData();
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../../style/test';
  @import '../../../../../style/style';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  div.g-container{padding:0;width:100%;}
  .g-tree_content.spec{
    margin-top:0;
    .g-sectionR{.width(585,940);margin:0;}
    .g-sectionL{.width(330,940);.NotLineheight(541);}
  }

  /*弹框*/
  .g-tree_content h2{text-align:center;.fontSize(12);color:@HColor;padding-bottom:20/16rem;}
  .g-tree_content .el-dialog--tiny{.NotLineheight(560);}
  /*容器*/
  .g-dialogConContainer{width:100%;.NotLineheight(405);overflow-x:hidden;overflow-y:auto;}
  .g-tree_content ul{
    width:100%;border-top:1px solid @borderColor;border-bottom:1px solid @borderColor;.marginBottom(24);
  }
  /*行*/
  .g-tree_content .g-dialogRow{
    width:100%;
    &:not(:last-of-type){border-bottom:1px solid @borderColor;}
  }
  /*列*/
  .g-tree_content .g-dialogRow>div{
    text-align:center;.fontSize(14);color:@normalColor;padding:15/16rem 0;
    &:nth-of-type(odd){width:35.7%;border-right:1px solid @borderColor;}
    &:nth-of-type(even){width:64.3%;}
  }

  .g-contentOne_header{.widthRem(100);.height(30);margin-bottom:25/16rem;font-size:14/16rem;color:#fff;background:@buttonActive;.box-shadow(0 4/16rem 6/16rem 0 rgba(0,0,0,.2));text-align:center;.border-bottom-right-radius(15/16rem);.border-top-right-radius(15/16rem);}
  /*审批结果处css*/
  .g-chooseButton{/*1582*/
    width:100%;padding:0 0 30/16rem 60/16rem;.fontSize(14);color:@normalColor;
    span{margin-right:20/16rem;}
    div{.widthRem(80);.height(30);text-align:center;.box-sizing();
      &:hover{cursor:pointer;}
      &:first-of-type{.border-bottom-left-radius(15/16rem);.border-top-left-radius(15/16rem);}
      &:last-of-type{.border-top-right-radius(15/16rem);.border-bottom-right-radius(15/16rem);}
    }
    div.activeCss{background:@green;color:#fff;border:none;}
    div.normalCss{color:@normalColor;border:1px solid @borderColor;}
  }
  .g-footer{width:100%;padding:24/16rem 0;text-align:center;}
</style>


