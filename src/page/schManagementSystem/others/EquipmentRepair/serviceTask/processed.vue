<template>
  <div class="g-statisticalAnalysis g-container repairprocessed">
    <header class="g-textHeader g-flexStartRow">
      <el-form :model="repairForm" label-width="85px" label-position="left">
        <el-form-item label="报修类别:" required>
          <el-select v-model="repairForm.id">
            <el-option v-for="(content,index) in repairTypeData" :key="index" :value="content.id" :label="content.repairType"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="时间段:">
          <el-col :span="10">
            <el-date-picker type="date" :picker-options="pickerOptionStart" placeholder="选择日期" v-model="repairForm.startTime" style="width:100%"></el-date-picker>
          </el-col>
          <el-col :span="2" class="line" style="text-align:center;">--</el-col>
          <el-col :span="10">
            <el-date-picker type="date" :picker-options="pickerOptionEnd" placeholder="选择日期" v-model="repairForm.endTime" style="width:100%"></el-date-picker>
          </el-col>
        </el-form-item>
      </el-form>
      <el-button class="radiusButton selfCenter" @click="searchTableClick" type="primary" icon="el-icon-search">查询</el-button>
    </header>
    <section class="centerTable alertsList">
      <div class="g-liOneRow g-sa_header_search">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button data-msg="export" @click="exportClick" class="filt buttonChild" title="导出">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png" />
            </el-button>
          </el-button-group>
          <el-button-group class="elGroupButton_two">
            <el-button data-msg="copy" class="filt buttonChild" title="复制" @click="operationData('copy')"> 
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_copy_highlight.png" />
            </el-button>
            <el-button  data-msg="print" class="filt buttonChild" title="打印预览" @click="operationData('print')">
              <img class="filt_unactive"  src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png" />
              <img class="filt_active" src="../../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png" />
            </el-button>
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="请输入" @change="searchTableClick"></el-input>
        </div>
      </div>
      <el-table class="g-NotHover" 
                v-loading="loadingProcessed" 
                element-loading-text="拼命加载中" 
                element-loading-spinner="el-icon-loading" 
                :data="classesTimeSetTable" 
                :max-height="550"
                @sort-change="sortChange" 
                @selection-change="handleSelectionChange">
        <el-table-column label="序号" type="index" width="100"></el-table-column>
        <el-table-column label="报修单号" min-width="150" sortable="custom">
          <template slot-scope="props">
            <el-button @click="detailShowClick(props.$index)" type="text" v-text="props.row.repairNumber"></el-button>
          </template>
        </el-table-column>
        <el-table-column label="报修物品" min-width="150" prop="repairName" sortable="custom"></el-table-column>
        <el-table-column label="资产编号" min-width="150" prop="assetsNumber" sortable="custom"></el-table-column>
        <el-table-column label="报修内容" min-width="150" prop="repairContent" sortable="custom"></el-table-column>
        <el-table-column label="申请人" min-width="150" prop="userName" sortable="custom"></el-table-column>
        <el-table-column label="联系方式" min-width="150" prop="phone" sortable="custom"></el-table-column>
        <el-table-column label="报修类别" min-width="150" prop="repairType" sortable="custom"></el-table-column>
        <el-table-column label="申请时间" min-width="150" prop="applyTime" sortable="custom"></el-table-column>
        <el-table-column label="接单时间" min-width="150" prop="reciveTime" sortable="custom"></el-table-column>
        <el-table-column label="到场时间" min-width="150" prop="arriveTime" sortable="custom"></el-table-column>
        <el-table-column label="完成时间" min-width="150" prop="completionTime" sortable="custom"></el-table-column>
        <el-table-column label="维修结果" min-width="150" prop="state" sortable="custom">
          <template slot-scope="prop">
            <span v-if="prop.row.state==1">待处理</span>
            <span v-if="prop.row.state==2">已维修</span>
            <span v-if="prop.row.state==3">维修失败</span>
            <span v-if="prop.row.state==4">已验收</span>
          </template>
        </el-table-column>
        <el-table-column label="维修反馈" prop="feedback" min-width="150" sortable="custom">
        </el-table-column>
      </el-table>
    </section>
    <el-dialog class="overflowDialog headerNotBackground" :modal="false" title="维修详情" :visible.sync="isDetailDialog" :before-close="handlerDetailClose">
      <div class="g-repairDetail">
        <div class="g-liOneWrapRow">
          <div class="g-detailRow">
            <span class="g-detailLable">报修单号:</span>
            <span>{{detailForm.repairNumber}}</span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">维修地点:</span>
            <span>{{detailForm.repairAddress}}</span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">报修物品:</span>
            <span>{{detailForm.repairName}}</span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">联系方式:</span>
            <span>{{detailForm.phone}}</span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">资产编号:</span>
            <span>{{detailForm.assetsNumber}}</span>
          </div>
          <div class="g-detailRow">
            <span class="g-detailLable">报修类别:</span>
            <span>{{detailForm.repairType}}</span>
          </div>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">报修内容:</span>
          <span>{{detailForm.repairContent}}</span>
        </div>
        <div class="g-detailRow g-repairPerson g-liOneRow">
          <span class="g-detailLable">图片:</span>
          <ul class="g-flexStartWrapRow">
            <li v-for="url in detailForm.logo">
              <img :src="url" alt="" />
            </li>
          </ul>
        </div>
      </div>
      <div class="g-repairPerson g-liOneWrapRow">
        <div class="g-detailRow">
          <span class="g-detailLable">接单人:</span>
          <span v-text="detailForm.repairUserName"></span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">到场时间:</span>
          <span v-text="detailForm.arriveTime"></span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">维修状态:</span>
          <span v-if="detailForm.repairState==0">未接单</span>
          <span v-if="detailForm.repairState==1">维修中</span>
          <span v-if="detailForm.repairState==2">已维修</span>
          <span v-if="detailForm.repairState==3">已验收</span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">接单时间:</span>
          <span v-text="detailForm.reciveTime"></span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">故障原因:</span>
          <span v-text="detailForm.reason"></span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">申请时间:</span>
          <span v-text="detailForm.applyTime"></span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">维修方法:</span>
          <span v-text="detailForm.method"></span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">完成时间:</span>
          <span v-text="detailForm.completionTime"></span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">更换设备:</span>
          <span v-text="detailForm.replaceType"></span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">维修结果:</span>
          <span v-if="detailForm.state==1">待处理</span>
          <span v-if="detailForm.state==2">已维修</span>
          <span v-if="detailForm.state==3">维修失败</span>
          <span v-if="detailForm.state==4">已验收</span>
        </div>
        <div class="g-detailRow">
          <span class="g-detailLable">维修反馈:</span>
          <span v-text="detailForm.feedback"></span>
        </div>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    serviceTaskLoad,//加载
    serviceTaskType,//报修类别
  } from '@/api/http'
  import {fileTypeCheck} from '@/assets/js/common'
  import req from '@/assets/js/common'
  import moment from 'moment'
  export default{
    data(){
      let _self=this;
      return{
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*form表单*/
        repairForm:{
          id:'',
          startTime:'',
          endTime:''
        },
        /*table*/
        classesTimeSetTable:[],
        pickerOptionStart:{
          disabledDate(time){
            if(_self.repairForm.endTime){
              return time.getTime()>Date.parse(_self.repairForm.endTime);
            }
          }
        },
        pickerOptionEnd:{
          disabledDate(time){
            if(_self.repairForm.startTime){
              return time.getTime()<Date.parse(_self.repairForm.startTime);
            }
          }
        },
        /*弹框——维修详情*/
        isDetailDialog:false,
        detailForm:{},
        /*send ajax param*/
        orderParam:{
          sortData:'',
          sort:'',//排序字段
        },
        multipleData:[],
        repairTypeData:[],
        loadingProcessed: false
      }
    },
    methods:{
      operationData(type){
        if(!this.classesTimeSetTable || this.classesTimeSetTable.length <= 0) { 
          this.vmMsgWarning( '暂无数据！' ); return;
        }
        let sAy = [], hdData = {
          repairNumber:'报修单号',
          repairName: '报修物品',
          assetsNumber: '资产编号',
          repairContent: '报修内容',
          userName: '申请人',
          phone:'联系方式',
          repairType: '报修类别',
          applyTime: '申请时间',
          reciveTime: '接单时间',
          arriveTime: '到场时间',
          completionTime: '完成时间',
          state: '维修结果',
          feedback: '维修反馈'
        };
        sAy.push(hdData);
        for (let obj of this.classesTimeSetTable) {
          let d = {};
          for (let name in hdData) {
              d[name] = obj[name] || '';
          }
          sAy.push(d)
        }
        if (type == 'copy') {
          req.copyTableData('.repairprocessed', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*弹框——维修详情*/
      handlerDetailClose(done){
        done();
      },
      /*显示详情点击*/
      detailShowClick(index){
        this.isDetailDialog=true;
        this.detailForm=this.classesTimeSetTable[index];
      },
      /*查询*/
      searchTableClick(){
        if(this.repairForm.id){
          this.getLoadAjax();
        }
        else{
          this.vmMsgWarning( '请选择报修类别！' );
        }
      },
      /*table*/
      handleSelectionChange(choose){
        this.multipleData=choose;
      },
      sortChange(column){
        this.orderParam.sort=column.prop;
        this.orderParam.sortData=column.order;
      },
      /*send ajax*/
      /*得到报修类别*/
      getRepairTypeAjax(){
        serviceTaskType().then(data=>{
          if(data.statu){
            this.repairTypeData=data.data;
            /*默认第一条*/
            if(this.repairTypeData.length>0){
              this.repairForm.id = this.repairTypeData[0].id;
              this.getLoadAjax();
            }
          }
          else{
            this.vmMsgError( '报修类别数据加载失败，请重试！' );
            this.repairTypeData=[];
          }
        });
      },
      getLoadAjax(){
        this.loadingProcessed = true;
        if(this.repairForm.startTime){
          this.repairForm.startTime=moment(this.repairForm.startTime).format('YYYY-MM-DD');
        }
        if(this.repairForm.endTime){
          this.repairForm.endTime=moment(this.repairForm.endTime).format('YYYY-MM-DD');
        }
        serviceTaskLoad({repairType:'list',...this.repairForm,state:2,...this.orderParam,valueData:this.fuzzyInput}).then(data=>{
          this.loadingProcessed = false;
          if(data.statu){
            this.classesTimeSetTable=data.data;
          }
          else{
            this.classesTimeSetTable=[];
            this.vmMsgError( '数据加载失败，请重试！' );
          }
        });
      },
      exportClick(){
        if(this.repairForm.id){
          let urlPrompt='&repairType=export&state=2&id='+this.repairForm.id;
          req.downloadFile('.g-container','/school/Eqrepair/repairTask?type=getRepairTaskList'+urlPrompt,'post');
        }
        else{
          this.vmMsgWarning( '请选择报修类别！' );
        }
      },
    },
    created(){
      this.getRepairTypeAjax();
    }
  }
</script>
<style lang="less" scoped>
  /*@import '../../../../../style/test';*/
  @import '../../../../../style/style';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  .g-classSchedule .g-container{padding:0;}
  .g-statisticalAnalysis header.g-textHeader.g-flexStartRow{.marginTop(20);.marginBottom(20);border-bottom:1px solid @borderColor;}
  .g-textHeader .el-form{.width(825,1582);}
  .g-textHeader .el-form-item{float:left;margin-bottom:0;}
  .g-textHeader .el-form-item:nth-of-type(1){.width(222,825);}
  .g-textHeader .el-form-item:nth-of-type(2){.width(558,825);.marginLeft(40,825);}
  .g-liOneRow.g-sa_header_search{margin-top:0;}
  /*维修详情弹框*/
  .g-repairDetail{border-bottom:2/16rem solid @borderColor;.marginBottom(20);}
  .g-liOneWrapRow .g-detailRow{.widthRem(370);}
  .g-detailRow{width:100%;.marginBottom(36);
    .g-detailLable{display:inline-block;.widthRem(80);text-align:right;margin-right:10/16rem;}
    span{.fontSize(14);color:@normalColor;}
  }
  .g-repairPerson{
    ul{width:100%;}
    li{width:7.5rem;height:7.5rem;margin-right:10/16rem;.marginBottom(10);
      img{width:100%;}
    }
  }
</style>


