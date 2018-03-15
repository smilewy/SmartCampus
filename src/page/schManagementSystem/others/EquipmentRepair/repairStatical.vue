<template>
  <div class="g-container">
    <header class="g-textHeader">
      <h2>报修统计</h2>
    </header>
    <div class="g-statisticalAnalysis">
      <header class="g-textHeader g-flexStartRow">
        <el-form :model="repairForm" label-width="85px" label-position="left">
          <el-form-item label="报修类别:" required>
            <el-select v-model="repairForm.typeList">
              <el-option value="1" label="按报修类别统计"></el-option>
              <el-option value="2" label="按接单人统计"></el-option>
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
        <el-button class="radiusButton selfCenter" @click="searchClick" type="primary" icon="el-icon-search">查询</el-button>
      </header>
      <section class="centerTable alertsList">
        <div id="staticEchart" v-show="classesTimeSetTable.length!=0"></div>
        <el-table class="g-NotHover" 
                  v-loading="loading" 
                  element-loading-text="拼命加载中" 
                  element-loading-spinner="el-icon-loading" 
                  :data="classesTimeSetTable" 
                  @sort-change="sortChange" 
                  @selection-change="handleSelectionChange">
          <el-table-column :label="staticType" sortable="custom">
            <template slot-scope="props">
              <el-button type="text" v-text="props.row.type"></el-button>
            </template>
          </el-table-column>
          <el-table-column label="待维修" prop="wait"></el-table-column>
          <el-table-column label="维修中" prop="middle"></el-table-column>
          <el-table-column label="已维修" prop="have"></el-table-column>
          <el-table-column label="已验收" prop="ys"></el-table-column>
        </el-table>
      </section>
    </div>
  </div>
</template>
<script>
  import {
    repairStaticalLoad,//加载信息
  } from '@/api/http'
  import moment from 'moment'
  import echarts from 'echarts'
  export default{
    data(){
      let _self=this;
      return{
        /*模糊查询*/
        fuzzyInput:'',
        evaluationName:'',
        /*form表单*/
        repairForm:{
          typeList:'',
          startTime:'',
          endTime:''
        },
        /*table*/
        classesTimeSetTable:[],
        staticType:'报修类别',
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
        /*echarts*/
        echartsObj:'',
        echartsX:[],
        waitData:[],//待审批
        alreadyData:[],//已审批
        orderData:[],//维修中
        assetsData:[],//已验收
        loading: false
      }
    },
    methods:{
      /*点击返回流程图按钮*/
      goBackChart(){
        this.$router.push({name:'evaluationManagement'});
      },
      /*table*/
      handleSelectionChange(choose){
      },
      sortChange(value){
      },
      /*模糊查询*/
      fuzzyClick(){
        /*模糊查询执行回调*/
      },
      /*查询*/
      searchClick(){
        if(this.repairForm.typeList){
          this.getLoadAjax();
        }
        else{
          this.vmMsgWarning( '请选择报修类别！' );
        }
      },
      /*画图*/
      drawChart(){
        this.echartsObj=echarts.init(document.getElementById('staticEchart'));
        let colors=['#89bcf5','#f08bc5','#4da1ff','#fdd465'],_self=this;
        this.echartsObj.setOption(
          {
            tooltip : {
              trigger: 'axis',
              axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
              }
            },
            legend: {
              data:['待维修','维修中','已维修','已验收']
            },
            color:colors,
            grid: {
              left: '3%',
              right: '4%',
              bottom: '3%',
              containLabel: true
            },
            xAxis : [
              {
                type : 'category',
                data : _self.echartsX
              }
            ],
            yAxis : [
              {
                type : 'value'
              }
            ],
            series : [
              {
                name:'待维修',
                type:'bar',
                stack:'维修',
                data:_self.waitData
              },
              {
                name:'维修中',
                type:'bar',
                stack:'维修',
                data:_self.orderData
              },
              {
                name:'已维修',
                type:'bar',
                stack:'维修',
                data:_self.alreadyData
              },
              {
                name:'已验收',
                type:'bar',
                data:_self.assetsData
              }
            ]
          }
        )
      },
      /*send ajax*/
      getLoadAjax(){
        this.loading = true;
        if(this.repairForm.startTime){
          this.repairForm.startTime=moment(this.repairForm.startTime).format('YYYY-MM-DD');
        }
        if(this.repairForm.endTime){
          this.repairForm.endTime=moment(this.repairForm.endTime).format('YYYY-MM-DD');
        }
        repairStaticalLoad(this.repairForm).then(data=>{
          this.loading = false;
          if(data.statu){
            this.classesTimeSetTable=data.data.table;
            this.echartsX=data.data.tb.bt;
            this.waitData=data.data.tb.wait;
            this.alreadyData=data.data.tb.have;
            this.orderData=data.data.tb.middle;
            this.assetsData=data.data.tb.ys;
            if(this.echartsObj){
              this.echartsObj.dispose();
            }
            this.$nextTick(()=>{
              this.drawChart('staticEchart');
            });
          }
          else{
            this.vmMsgError( '数据更新失败！' );
            this.classesTimeSetTable=[];
            this.waitData=[];
            this.alreadyData=[];
            this.orderData=[];
            this.assetsData=[];
          }
        });
      },
    },
    created(){
    }
  }
</script>
<style lang="less" scoped>
  /*@import '../../../../style/test';*/
  @import '../../../../style/style';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.css';
  @import '../../../../style/researchManagement/teacherEvaluation/teacherEvaluation.less';
  .g-sa_header_search{.marginTop(32);.marginBottom(20);}
  .g-classSchedule .g-container{padding:0;}
  .g-statisticalAnalysis header.g-textHeader.g-flexStartRow{.marginTop(20);.marginBottom(20);}
  .g-textHeader .el-form{.width(825,1582);}
  .g-textHeader .el-form-item{float:left;margin-bottom:0;}
  .g-textHeader .el-form-item:nth-of-type(1){.width(222,825);}
  .g-textHeader .el-form-item:nth-of-type(2){.width(558,825);.marginLeft(40,825);}
  .g-liOneRow.g-sa_header_search{margin-top:0;}
  #staticEchart{width:100%;.NotLineheight(430);.marginBottom(30);}
</style>


