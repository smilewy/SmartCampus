<template>
  <div class="g-container">
    <header class="g-textHeader">
      <div class="g-liOneRow g-sa_header_search">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button class="filt buttonChild" @click="exportClick" title="导出">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
            </el-button>
          </el-button-group>
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
        <el-table-column label="业务名称" min-width="100" sortable="custom">
          <template slot-scope="props">
            <el-button type="text" v-text="props.row.approveName" @click="showDetail(props.$index)"></el-button>
          </template>
        </el-table-column>
        <el-table-column label="资产名称" min-width="100" prop="assetsName" sortable="custom"></el-table-column>
        <el-table-column label="资产编号" min-width="100" prop="assetsNumber" sortable="custom"></el-table-column>
        <el-table-column label="分类代码" min-width="100" prop="assetsTypeId" sortable="custom"></el-table-column>
        <el-table-column label="总价(元)" min-width="100" prop="allPrice" sortable="custom"></el-table-column>
        <el-table-column label="审批日期" min-width="120" prop="approveTime" sortable="custom"></el-table-column>
        <el-table-column label="申请日期" min-width="120" prop="createTime" sortable="custom"></el-table-column>
        <el-table-column label="使用地址" min-width="120" prop="useAddress" sortable="custom"></el-table-column>
        <el-table-column label="负责人" min-width="100" prop="userName" sortable="custom"></el-table-column>
        <el-table-column label="审批人" min-width="100" prop="approver" sortable="custom"></el-table-column>
        <el-table-column label="说明" min-width="120" prop="explain" sortable="custom"></el-table-column>
        <el-table-column label="审批意见" min-width="100" prop="approveOpinion" sortable="custom"></el-table-column>
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
        <div class="g-contentOne_header">审批状态</div>
        <div class="g-chooseButton g-flexStartRow">
          <span class="selfCenter">审批人:</span>
          <span class="normalCss">{{detailForm.approver}}</span>
        </div>
        <div class="g-chooseButton g-flexStartRow">
          <span class="selfCenter">审批结果:</span>
          <span class="activeCss" v-if="detailForm.appResult=='1'">通过</span>
          <span class="normalCss" v-if="detailForm.appResult=='2'">不通过</span>
          <span class="normalCss" v-if="detailForm.appResult=='0'">审批中</span>
        </div>
        <div class="g-chooseButton g-flexStartRow">
          <span class="selfCenter">审批意见:</span>
          <span class="normalCss">{{detailForm.approveOpinion}}</span>
        </div>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    alreadyApprovalGetLoad,//页面加载信息
    alreadyApprovalGetSort,//排序
    alreadyApprovalGetSearch,//模糊查询
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
        /*弹框*/
        isDialog:false,
        detailForm:{},
        gradeDialogForm:{
          person:'',
          address:'',
          description:'',
          date:''
        },
        /*tree*/
        treeData:[],
        defaultProps: {
          children: 'data',
          label:'techerName',
        },
        loading: false
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*弹框*/
      handlerClose(done){
        done();
      },
      fuzzyDialogClick(){
        /*input框输入值改变触发的函数*/
        this.$refs['allMsg'].filter(this.fuzzyInput);
      },
      filterNode(value, data) {
        /*筛选节点*/
        if (!value) return true;
        return data.techerName.indexOf(value) !== -1;
      },
      /*tree点击事件*/
      handleNodeClick(data,node){
        if('data' in data){
          return;
        }else{
          this.techerId=data.techerId;
          this.techerName=data.techerName;
          this.headerText=data.techerName+'('+node.parent.data.techerName+')';
          this.getTableData();
        }
      },
      /*table*/
      fuzzyTableClick(){},
      handleSelectionChange(val){
      },
      /*send ajax*/
      getLoadAjax(){
        this.loading = true;
        alreadyApprovalGetLoad().then(data=>{
          this.loading = false;
          this.classesTimeSetTable=handlerAjaxData(data);
        });
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
        this.loading = true;
        alreadyApprovalGetSearch({valueData:this.fuzzyInput}).then(data=>{
          this.loading = false;
          this.classesTimeSetTable=handlerAjaxData(data);
        });
      },
      sortChange(column,prop,order){
        this.loading = true;
        alreadyApprovalGetSort({sort:column.prop,sortData:column.order}).then(data=>{
          this.loading = false;
          this.classesTimeSetTable=handlerAjaxData(data);
        });
      },
      exportClick(){
        req.downloadFile('.g-container','/school/assets/assetsApprove?type=getHaveApproveExport','post');
      },
      showDetail(idx){
        this.isDialog=true;
        this.detailForm=this.classesTimeSetTable[idx];
      }
    },
    created(){
      this.getLoadAjax();
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
    width:100%;padding:0 0 15/16rem 60/16rem;.fontSize(14);color:@normalColor;
    span{margin-right:20/16rem;}
    div{.widthRem(80);.height(30);text-align:center;.box-sizing();
      &:hover{cursor:pointer;}
      &:first-of-type{.border-bottom-left-radius(15/16rem);.border-top-left-radius(15/16rem);}
      &:last-of-type{.border-top-right-radius(15/16rem);.border-bottom-right-radius(15/16rem);}
    }
    span.activeCss{color:@green;font-weight:bold;}
    span.normalCss{color:@HColor;font-weight:bold;}
  }
  .g-footer{width:100%;padding:24/16rem 0;text-align:center;}
</style>


